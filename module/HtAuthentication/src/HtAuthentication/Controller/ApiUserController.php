<?php
/**
 *  Manage internal login using email and password
 */
namespace HtAuthentication\Controller;

use HtApplication\Controller\AbstractActionController;
use HtAuthentication\Service\Model\OrganisationFactory;
use HtApplication\Model\Log;
use HtUser\Service\Model\UserFactory;
use HtUser\Service\Model\InviteFactory;
use HtAuthentication\Service\Model\Adapter\InternalFactory;
use HtUser\Model\User;
use HtAuthentication\Model\Token;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\View\Model\JsonModel;
use Zend\Db\Sql\Select;

class ApiUserController extends AbstractActionController
{
	
	/**
	 * Input filter for authenticatedAction
	 * @return InputFilter
	 */
	protected function getFilterForAuthenticated() 
	{
		$inputFilter = new InputFilter();
		$factory = new InputFactory();

		$inputFilter->add($factory->createInput(array(
			'name' => 'token',
			'required' => true,
			'validators' => array(
				array(
					'name' => 'StringLength',
					'options' => array(
						'min' => Token::TOKEN_LENGTH,
						'max' => Token::TOKEN_LENGTH,
					),
				),
			),
		)));
			
		$inputFilter->add($factory->createInput(array(
			'name' => 'requestor',
			'required' => false,
			'filters' => array(
				array('name' => 'StringTrim'),
			),
		)));

		return $inputFilter;
	}
	
	public function authenticatedAction()
	{
		//StringLength validator generate error based on min/max checking
		//We write custom code to have exact match error message
		//Still keep using zend validator for consistent and extendable
		if (strlen(trim($this->getRequest()->getQuery('token'))) != Token::TOKEN_LENGTH) {
			return $this->error("Token length is invalid");
		}
		
		$filter = $this->getFilterForAuthenticated();
		$filter->setData($this->params()->fromQuery());
		if (!$filter->isValid()) {
			return $this->error($filter->getMessages());
		}

		$data = $filter->getValues();
		$tokenString = $data['token'];

		$sl = $this->getServiceLocator();
		/* @var $token \HtAuthentication\Model\Token */
		$token = $sl->get('Token');
		
		if(!$token->loadByTokenString($tokenString)) {
			return $this->error("Invalid token");
		}
		
		$user = $token->getUser();
		if (!$user) {
			return $this->error("Token belong to no user");
		}
		
		if ($token->isExpired()) {
			return new JsonModel(array(
				'user_id' => $user->getUserId(),
				'status' => 'Expired',
				'date_generated' => $token->getCreationDate()
			));
		}

		return new JsonModel(array(
			'user_id' => $user->getUserId(),
			'status' => 'Authenticated',
			'date_generated' => $token->getCreationDate()
		));		
	}
	
	protected function error($message)
	{
		return new JsonModel(array(
			'result' => 0,
			'message' => $message
		));
	}
	
	/**
	 * Input filter for createAction
	 * @return InputFilter
	 */
	protected function getFilterForCreate() 
	{
		$sl = $this->getServiceLocator();
		
		$inputFilter = new InputFilter();
		$factory = new InputFactory();

		$inputFilter->add($factory->createInput(array(
			'name' => 'user_login',
			'required' => true,
			'filters' => array(
				array('name' => 'StringTrim'),
			),
			'validators' => array(
				array(
					'name' => 'HtAuthentication\Validator\CheckUniqueModelProperty',
					'options' => array(
						'model' => $sl->get('AuthAccountInternal'),
						'property' => 'username',
						'messages' => array(
							'notUnique' => 'This username was already used by another user.'
						),
					),
				),
			)			
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name' => 'user_password',
			'required' => true,
			'filters' => array(
				array('name' => 'StringTrim'),
				array('name' => 'StripTags'),
			),
		)));
			
		$inputFilter->add($factory->createInput(array(
			'name' => 'user_email',
			'required' => true,
			'filters' => array(
				array('name' => 'StringTrim'),
			),
			'validators' => array(
				array('name' => 'emailaddress'),
				array(
					'name' => 'HtAuthentication\Validator\CheckUniqueModelProperty',
					'options' => array(
						'model' => $sl->get('User'),
						'property' => 'email',
						'messages' => array(
							'notUnique' => 'This email address was already used by another user.'
						),
					),
				)				
			)
		)));

		$inputFilter->add($factory->createInput(array(
			'name' => 'application_id',
			'required' => true,
			'filters' => array(
				array('name' => 'StringTrim'),
			),
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name' => 'invitation_token',
			'required' => true,
			'filters' => array(
				array('name' => 'StringTrim'),
			),
			'validators' => array(
				array(
					'name' => 'HtAuthentication\Validator\CheckUniqueModelProperty',
					'options' => array(
						'model' => $sl->get('Invite'),
						'property' => 'code',
						'messages' => array(
							'notUnique' => 'This invitation code was already used by another user.'
						)
					),
				),
			),
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name' => 'server_id',
			'required' => false,
			'filters' => array(
				array('name' => 'StringTrim'),
			),
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name' => 'organisation_id',
			'required' => false,
			'filters' => array(
				array('name' => 'StringTrim'),
			),
		)));
				
		$inputFilter->add($factory->createInput(array(
			'name' => 'organisation_name',
			'required' => false,
			'filters' => array(
				array('name' => 'StringTrim'),
			),
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name' => 'WS_server_id',
			'required' => true,
			'filters' => array(
				array('name' => 'StringTrim'),
			),
		)));
				
		$inputFilter->add($factory->createInput(array(
			'name' => 'WS_server_ip',
			'required' => true,
			'filters' => array(
				array('name' => 'StringTrim'),
			),
		)));
		
		
		return $inputFilter;
	}
	
	/**
	 * Validate if a secret is correct
	 * 
	 * @param string $secret
	 * @return boolean
	 */
	protected function validServerSecret($secret)
	{
		//check secret valid
		$sl = $this->getServiceLocator();
		/* @var $server \HtAuthentication\Model\Server */
		$server = $sl->get('Server');
		$serverObject = $server->getBySecret($secret);
		if (!$serverObject) {
			return false;
		} else {
			return true;
		}
	}
	
	public function createAction()
	{
		$request = $this->getRequest();
		
		if ($request->isPost()) {
			$data = $request->getPost();
		} else {
			$data = $request->getQuery();
		}
		$filter = $this->getFilterForCreate();
		$filter->setData($data);
		if (!$filter->isValid()) {
			return $this->error($filter->getMessages());
		}
		
		$data = $filter->getValues();
		
		if (!$this->validServerSecret($data['WS_server_id'])) {
			return $this->error('Invalid server id');
		}
		
		$email = $data['user_email'];
		$sl = $this->getServiceLocator();
		/* @var $app \HtAuthentication\Model\Application */
		$app = $sl->get('App');
		if (!$app->load($data['application_id'])) {
			return $this->error('Application id is empty or invalid application id');
		}
		
		$serverId = $data['server_id'];
		/* @var $server \HtAuthentication\Model\Server */
		$server = $sl->get('Server');
		if (!empty($serverId)) {
			if (!$server->load($serverId)) {
				return $this->error('Invalid server id');
			}
			if (!$app->hasServer($server)) {
				return $this->error('Server doesn\'t belong to application');
			}
		} else {
			$server = null;
			if (!$app->hasServer()) {
				return $this->error('Application has no server');
			}
		}
		
		try {
			$connection = $this->getConnection();
			$connection->beginTransaction();
			
			//create user
			/* @var $user \HtUser\Model\User */
			$userService = new UserFactory();
			$user = $userService->createService($sl);
			$user->exchangeArray(array(
				'email' => $email,
			));
			$requiredActivate = true;
			$user->registerMemberAccount($requiredActivate);

			//create internal log adapter
			/* @var $authInternal \HtAuthentication\Model\Adapter\Internal */
			$authInternalService = new InternalFactory();
			$authInternal = $authInternalService->createService($sl);
			$authInternal->exchangeArray(array(
				'username' => $data['user_login'],
				'password' => $data['user_password'],//already encoded password, just need to store
				'is_activated' => 0
			));
			$authInternal->save();

			$authInternal->linkUser($user);

			//create invitation
			/* @var $invite \HtUser\Model\Invite */
			$inviteService = new InviteFactory();
			$invite = $inviteService->createService($sl);
			/* Create invitation if not exist*/
			
			if (!$invite->loadByEmail($email)) {
				$invite->createByEmail($email, $data['invitation_token']);
			}
			
			//find or create organisation (if any) then link user to org
			$orgId = $data['organisation_id'];
			$orgName = $data['organisation_name'];
			/* @var $org \HtAuthentication\Model\Organisation */
			$org = $sl->get('Org');

			/* redirect to organisation list page. */
			if (!$org->load($orgId)) {
				if (empty($orgName)) {
					$connection->rollback();
					return $this->error("Organisation id is invalid and organisation name to create new one is empty");
				}
				$orgService = new OrganisationFactory();
				$org = $orgService->createService($sl);
				$org->exchangeArray(array(
					'title' => $orgName,
					'description' => $orgName,
					'domain'	=> $orgName
				));
				$org->save();
			}
			$org->addUser($user);

			//find app, link user to app
			/* @var $userApp \HtAuthentication\Model\UserApplication */
			$userApp = $sl->get('UserApp');
			$userApp->setUserId($user->getUserId());
			
			$userApp->addUserAccess($org, $app, $server);
			
			//write log
			$this->writeLog(Log::TYPE_REGISTER_SUCCESS,
					$this->translate('Successful Registrations through User Create API.'),
					array('user_id' => $user->getUserId()));
			
			$connection->commit();
			return new JsonModel(array('result' => 1));
		} catch (\Exception $e) {
			$connection->rollback();
			return $this->error($e->getMessage());
//			throw $e;
		}
	}
	
	protected function getFilterForExist()
	{
		$inputFilter = new InputFilter();
		$factory = new InputFactory();
		
		$inputFilter->add($factory->createInput(array(
			'name' => 'WS_server_id',
			'required' => true,
			'filters' => array(
				array('name' => 'StringTrim'),
				array('name' => 'StripTags'),
			),
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name' => 'WS_server_ip',
			'required' => true,
			'filters' => array(
				array('name' => 'StringTrim'),
				array('name' => 'StripTags'),
			),
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name' => 'user_login',
			'required' => true,
			'filters' => array(
				array('name' => 'StringTrim'),
				array('name' => 'StripTags'),
			),
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name' => 'organisation_name',
			'required' => false,
			'filters' => array(
				array('name' => 'StringTrim'),
				array('name' => 'StripTags'),
			),
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name' => 'user_password',
			'required' => false,
			'filters' => array(
				array('name' => 'StringTrim'),
				array('name' => 'StripTags'),
			),
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name' => 'organisation_id',
			'required' => false,
			'filters' => array(
				array('name' => 'StringTrim'),
				array('name' => 'StripTags'),
			),
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name' => 'organisation_name',
			'required' => false,
			'filters' => array(
				array('name' => 'StringTrim'),
				array('name' => 'StripTags'),
			),
		)));
		
		
		return $inputFilter;
	}
	
	public function existAction()
	{
		$filter = $this->getFilterForExist();
		$filter->setData($this->getRequest()->getQuery());
		if (!$filter->isValid()) {
			return $this->error($filter->getMessages());
		}
		
		if (!$this->validServerSecret($filter->getValue('WS_server_id'))) {
			return $this->error('Invalid server id');
		}
		
		$sl = $this->getServiceLocator();
		$where = array('username' => $filter->getValue('user_login'));
		/* @var $internal \HtAuthentication\Model\Adapter\Internal */
		$internal = $sl->get('AuthAccountInternal');
		if ($filter->getValue('user_password')) {
			$where['password'] = $filter->getValue('user_password');
		}
		
		$internalLogin = $internal->getOne($where);
		if (!$internalLogin) {
			return $this->error('User does not exist.');
		}
		
		$organisationId = $filter->getValue('organisation_id');
		$organisationName = $filter->getValue('organisation_name');
		
		if (!$organisationId && !$organisationName) {
			return new JsonModel(array(
				'result' => 1,
			));
		}
		
		/* @var $userOrganisation \HtAuthentication\Model\UserOrganisation */
		$userOrganisation = $sl->get('UserOrg');
		
		$user = $internalLogin->getUser();
		$where = array('user_id' => $user->getUserId());
		$data = $filter->getValues();
		
		if ($data['organisation_id']) {
			$where['organisation.organisation_id'] = $data['organisation_id'];
		}
		
		if ($data['organisation_name']) {
			$where['title'] = $data['organisation_name'];
		}
		
		$count = $userOrganisation->count(function (Select $select) use ($where) {
			$select->join('organisation', 'organisation.organisation_id = user_organisation.organisation_id', array());
			$select->where($where);
		});

		if ($count == 0) {
			return $this->error('User does not exist.');
		}
		
		return new JsonModel(array(
			'result' => 1,
		));
	}
	
	/**
	 * Input filter for updateAction
	 * @return InputFilter
	 */
	protected function getFilterForUpdate() 
	{
		
		$inputFilter = new InputFilter();
		$factory = new InputFactory();

		$inputFilter->add($factory->createInput(array(
			'name' => 'user_login',
			'required' => true,
			'filters' => array(
				array('name' => 'StringTrim'),
			),
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name' => 'user_password',
			'required' => true,
			'filters' => array(
				array('name' => 'StringTrim'),
			)
 		)));
			
		$inputFilter->add($factory->createInput(array(
			'name' => 'organisation_id',
			'required' => false,
			'filters' => array(
				array('name' => 'StringTrim'),
			),
		)));
				
		$inputFilter->add($factory->createInput(array(
			'name' => 'organisation_name',
			'required' => false,
			'filters' => array(
				array('name' => 'StringTrim'),
			),
		)));

		$inputFilter->add($factory->createInput(array(
			'name' => 'application_id',
			'required' => false,
			'filters' => array(
				array('name' => 'StringTrim'),
			),
		)));
		
		
		$inputFilter->add($factory->createInput(array(
			'name' => 'server_id',
			'required' => false,
			'filters' => array(
				array('name' => 'StringTrim'),
			),
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name' => 'updated_password',
			'required' => false,
			'filters' => array(
				array('name' => 'StringTrim'),
			),
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name' => 'WS_server_id',
			'required' => true,
			'filters' => array(
				array('name' => 'StringTrim'),
			),
		)));
				
		$inputFilter->add($factory->createInput(array(
			'name' => 'WS_server_ip',
			'required' => true,
			'filters' => array(
				array('name' => 'StringTrim'),
			),
		)));
		
		
		return $inputFilter;
	}
	
	public function updateAction()
	{
		$request = $this->getRequest();
		if ($request->isPost()) {
			$data = $request->getPost();
		} else {
			$data = $request->getQuery();
		}
		
		$filter = $this->getFilterForUpdate();
		$filter->setData($data);
		if (!$filter->isValid()) {
			return $this->error($filter->getMessages());
		}
		
		$data = $filter->getValues();
		
		if (!$this->validServerSecret($data['WS_server_id'])) {
			return $this->error('Invalid server id');
		}
		
		$sl = $this->getServiceLocator();
		/* @var $internalAdapter \HtAuthentication\Model\Adapter\Internal */
		$internalAdapter = $sl->get('AuthAccountInternal')
			->getOne(array(
				'username' => $data['user_login'],
				'password' => $data['user_password']
			));
		if (!$internalAdapter) {
			return $this->error("User login or user password is wrong");
		}
		$user = $internalAdapter->getUser();
		
		$message = array();
		
		//find org and add user
		/* @var $org \HtAuthentication\Model\Organisation */
		$org = $sl->get('Org');
		
		$where = function (Select $select) use ($data) {
			if (!empty($data['organisation_id'])) {
				$select->where(array('organisation_id' => $data['organisation_id']));
			}
			
			if (!empty($data['organisation_name'])) {
				$select->where->like('title', '%' . $data['organisation_name'] . '%');
			}
		};
		
		if (!empty($where)) {
			$org = $org->getOne($where);
		}
		
		if ($org) {
			if ($org->hasUser($user)) {
				$message[] = "User already add to submit organisation";
			} else {
				$org->addUser($user);
				$message[] = sprintf("Add user to organisation with id %s successfully", $org->getOrganisationId());
			}
		}
		
		//update password
		if (!empty($data['updated_password'])) {
			$internalAdapter->setPassword($data['updated_password']);
			$internalAdapter->save();
			$message[] = "Change password successfully";
		}
		
		//find app add add user
		/* @var $app \HtAuthentication\Model\Application */
		$app = $sl->get('App');
		if (empty($data['application_id'])) {
			return new JsonModel(array(
				'result' => 1,
				'message' => $message
			));
		}
			
		if (!$app->load($data['application_id'])) {
			$message[] = sprintf("Application id %s is not valid", $app->getApplicationId());
			return new JsonModel(array(
				'result' => 1,
				'message' => $message
			));
		}
		//find user's org to add

		//select random one org to add
		//@todo: apply a more specific rule
		/* @var $userOrg \HtAuthentication\Model\UserOrganisation */
		$userOrg = $sl->get('UserOrg')
			->getAll(array('user_id' => $user->getUserId()))
			->current();

		if (!$userOrg) {
			$message[] = sprintf("User %s doesn't belong to any organisation", $user->getUserId());
			return new JsonModel(array(
				'result' => 1,
				'message' => $message
			));
		}
		
		$org->load($userOrg->getOrganisationId());

		//add user to app-org
		/* @var $server \HtAuthentication\Model\Server */
		$server = $sl->get('Server');
		if (empty($data['server_id']) || !$server->load($data['server_id'])) {
			$server = null;
		}
		/* @var $userApp \HtAuthentication\Model\UserApplication */
		$userApp = $sl->get('UserApp');
		$userApp->setUserId($user->getUserId());
		if ($userApp->hasUserAccess($org, $app, $server)) {
			if ($server) {
				$message[] = sprintf("Application %s - server %s is already added to user %s in org %s", 
					$app->getApplicationId(),
					$server->getServerId(),
					$user->getUserId(),
					$org->getOrganisationId()
				);
			} else {
				$message[] = sprintf("Application %s is already added to user %s in org %s", 
					$app->getApplicationId(),
					$user->getUserId(),
					$org->getOrganisationId()
				);
			}
		} else {
			$userApp->addUserAccess($org, $app, $server);
			if ($server) {
				$message[] = sprintf("Add application %s - server %s for user %s in org %s successfully", 
					$app->getApplicationId(),
					$server->getServerId(),
					$user->getUserId(),
					$org->getOrganisationId()
				);
			} else {
				$message[] = sprintf("Add application %s for user %s in org %s successfully", 
					$app->getApplicationId(),
					$user->getUserId(),
					$org->getOrganisationId()
				);
			}
		}
		
		return new JsonModel(array(
			'result' => 1,
			'message' => $message
		));
	}
	
	protected function getFilterForDelete()
	{
		$inputFilter = new InputFilter();
		$factory = new InputFactory();
		
		$inputFilter->add($factory->createInput(array(
			'name' => 'user_login',
			'required' => true,
			'filters' => array(
				array('name' => 'StringTrim'),
				array('name' => 'StripTags'),
			),
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name' => 'user_password',
			'required' => true,
			'filters' => array(
				array('name' => 'StringTrim'),
				array('name' => 'StripTags'),
			),
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name' => 'WS_server_id',
			'required' => true,
			'filters' => array(
				array('name' => 'StringTrim'),
				array('name' => 'StripTags'),
			),
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name' => 'WS_server_ip',
			'required' => true,
			'filters' => array(
				array('name' => 'StringTrim'),
				array('name' => 'StripTags'),
			),
		)));
		
		return $inputFilter;
	}

	public function deleteAction()
	{
		$request = $this->getRequest();
		if ($request->isPost()) {
			$data = $request->getPost();
		} else {
			$data = $request->getQuery();
		}
		
		$filter = $this->getFilterForDelete();
		$filter->setData($data);
		if (!$filter->isValid()) {
			return $this->error($filter->getMessages());
		}
		
		if (!$this->validServerSecret($filter->getValue('WS_server_id'))) {
			return $this->error('Invalid server id');
		}
		
		$sl = $this->getServiceLocator();
		/* @var $internal \HtAuthentication\Model\Adapter\Internal */
		$internal = $sl->get('AuthAccountInternal');
		
		$internalLogin = $internal->getOne(array(
			'username' => $filter->getValue('user_login'),
			'password' => $filter->getValue('user_password')
		));
		
		if (!$internalLogin) {
			return $this->error('User does not exist.');
		}
		
		/* @var $user User */
		$user = $internalLogin->getUser();
		
		$connection = $this->getConnection();
		try {
			$connection->beginTransaction();
			$user->delete();
			$connection->commit();
			return new JsonModel(array(
				'result' => 1, 
			));
		} catch (\Exception $e) {
			$connection->rollback();
			return $this->error('System Error.');
		}
		
	}
}