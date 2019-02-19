<?php
namespace HtAdmin\Controller;

use HtApplication\Controller\AbstractActionController;
use HtAdmin\Form\CreateUser as CreateUserForm;
use HtAdmin\Form\EditUser as EditUserForm;
use HtApplication\Model\EmailTemplate;
use HtUser\Model\User as UserModel;
use HtAuthentication\Model\AuthenticationAccount;

use HtApplication\Model\Acl\Role;

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use Zend\Db\Sql\Where;
class UserController extends AbstractActionController
{
	public function indexAction()
	{
        $showDeletedUser = $this->params()->fromRoute('show-deleted-user', false);
		
		$successMessage = '';
		$errorMessage = '';
		$flashMessager = $this->flashMessenger();
		if ($flashMessager->hasErrorMessages()) {
			$errorMessage = implode('<br />', $flashMessager->getErrorMessages());
		}
		if ($flashMessager->hasSuccessMessages()) {
			$successMessage = implode('<br />', $flashMessager->getSuccessMessages());
		}
        
        $pargingParamsData = $this->pagingParams()->get();
		return array(
			'successMessage' => $successMessage,
			'errorMessage' => $errorMessage,
            'showDeletedUser' => $showDeletedUser,
            'itemPerPage' => $pargingParamsData['itemPerPage']
		);
	}
    
    public function deletedUsersAction()
    {
        return $this->forward()->dispatch('HtAdmin\Controller\User', array(
            'controller' => 'HtAdmin\Controller\User',
            'action' => 'index',
            'show-deleted-user' => true
        ));
    }
	
	public function listAction()
	{
		$sl = $this->getServiceLocator();
		/* @var $userCollecttion \HtUser\Model\UserCollection */
		$userCollecttion = $sl->get('UserCollection');
		
		$where = new Where();
		$page = $this->params()->fromQuery('draw');
		$offset = $this->params()->fromQuery('start');
		$limit = $this->params()->fromQuery('length');
        $search = $this->params()->fromQuery('search');
		$keyword = $search['value'];
        
        $columns = $this->params()->fromQuery('columns');
        $order = $this->params()->fromQuery('order');
        $orders = null;
        $notOrderableColumn = array('status', 'app_count', 'org_count', 'log_url', 'role');
        if (!empty($order)) {
            foreach($order as $orderInfo) {
                $columnName = $columns[$orderInfo['column']]['data'];
                if (!in_array($columnName, $notOrderableColumn)) {
                    $orders .= $columnName . " " . $orderInfo['dir'];
                }
            }            
        }
        
        $status = $this->params()->fromQuery('status');
        if ($status) {
			$where = $userCollecttion->getQueryByStatus($status, $where);
		}
        
        if (!empty($keyword)) {
            $where->nest()
                ->like('firstname', "%{$keyword}%")
                ->or->like('lastname', "%{$keyword}%")
                ->or->like('email', "%{$keyword}%")
            ->unnest();
        }
        
		$count = $userCollecttion->count($where);
		
		$allUsers = $userCollecttion->getAll($where, $offset, $limit, $orders);
		$allUsers->buffer();
		
		$userIds = array();
		foreach($allUsers as $user) {
			$userIds[] = $user->user_id;
		}
		
		/**
		 * Count number application this user can use.
		 */
		$userAppNumber = array();
		$userOrgNumber = array();
		if (!empty($userIds)) {
			/* @var $userOrgCollection \HtAuthentication\Model\UserOrganisationCollection */
			$userOrgCollection = $sl->get('UserOrgCollection');
			$rowset = $userOrgCollection->countOrganisation($userIds);
			foreach ($rowset as $row) {
				$userOrgNumber[$row->user_id] = $row->count_org;
			}
			
			/* @var $userApplicationCollection \HtAuthentication\Model\UserApplicationCollection */
			$userApplicationCollection = $sl->get('UserAppCollection');

			$results = $userApplicationCollection->countApplication($userIds);
			foreach ($results as $result) {
				$userAppNumber[$result->user_id] = $result->count_apps;
			}
		}
		
		$items = array();
		foreach($allUsers as $user) {
			if (array_key_exists($user->user_id, $userOrgNumber)) {
				$user->org_count = $userOrgNumber[$user->user_id];
			} else {
				$user->org_count = 0;
			}
			
			if (array_key_exists($user->user_id, $userAppNumber)) {
				$user->app_count = $userAppNumber[$user->user_id];
			} else {
				$user->app_count = 0;
			}
			
			//user's name
			$name = $user->firstname . ' ' . $user->lastname;
			$name = trim($name);
			if (empty($name)) {
				$name = $user->email;
			}

			if (empty($name)) {
				$name = $user->user_id;
			}
			$user->name = $name;
			
			//user's status
			if ($user->is_deleted) {
				$user->status = $this->translate("deleted");
			} else if ($user->is_enabled) { 
				$user->status = $this->translate("enabled");
			} else {
				$user->status = $this->translate("disabled");
			}
			
			//user's role
			$user->role = $user->role_id;
			$user->log_url = "log";		
			
			$items[] = $user;
		}
		
		/*
		 * Output
		 */
		return new JsonModel(array(
			"draw"            => intval( $page ),
			"recordsTotal"    => intval( $count ),
			"recordsFiltered" => intval( $count ),
			"data"            => $items
		));
	}
	
    public function deleteMultipleAction()
	{
        $request = $this->getRequest();
        $ids = $request->getPost('ids');
        
        if (count($ids) == 0) {
            return new JsonModel();
        }
        $sl = $this->getServiceLocator();
		/* @var $collection \HtUser\Model\UserCollection */
		$collection = $sl->get('UserCollection');
        //@todo : exclude admin users here
        $items = $collection->getAll(array('user.user_id' => $ids));
        /* @var $user UserModel */
        $user = $sl->get("User");
        $connection = $this->getConnection();
		try {
			$connection->beginTransaction();
            foreach ($items as $item) {
                $user->exchangeArray($item);
                $user->delete();
            }
			$connection->commit();
		} catch (\Exception $e) {
			$connection->rollback();
			throw $e;
		}
        return new JsonModel();
    }
    
	public function deleteAction()
	{
		$userId = $this->params()->fromRoute('id');
		$sl = $this->getServiceLocator();
		
		/* @var $user \HtUser\Model\User */
		$user = $sl->get('User');
		if (!$user->load($userId)) {
			$this->flashMessenger()->addErrorMessage($this->translate('User does not exists.'));
			return $this->redirect()->toRoute(null, array('action' => 'index'));
		}
		
		$role = $user->getRole();
		if (!in_array(Role::MEMBER_ROLE, $role)) {
			$this->flashMessenger()->addErrorMessage($this->translate('Only allow delete member.'));
			return $this->redirect()->toRoute(null, array('action' => 'index'));
		}
		
		$connection = $this->getConnection();
		try {
			$connection->beginTransaction();
			$user->delete();
			$this->flashMessenger()->addSuccessMessage($this->translate('User has been deleted.'));
			$connection->commit();
		} catch (\Exception $e) {
			$connection->rollback();
			throw $e;
		}
		return $this->redirect()->toRoute(null, array('action' => 'index'));
	}
	
	public function permanentlyDeleteAction()
	{
		$userId = $this->params()->fromRoute('id');
		$sl = $this->getServiceLocator();
		
		/* @var $user \HtUser\Model\User */
		$user = $sl->get('User');
		$user->disableSoftDelete();
		if (!$user->load($userId)) {
			$this->flashMessenger()->addErrorMessage($this->translate('User does not exists.'));
			return $this->redirect()->toRoute(null, array('action' => 'index'));
		}
		
		$role = $user->getRole();
		if (!in_array(Role::MEMBER_ROLE, $role)) {
			$this->flashMessenger()->addErrorMessage($this->translate('Only allow delete member.'));
			return $this->redirect()->toRoute(null, array('action' => 'index'));
		}
		
		$connection = $this->getConnection();
		try {
			$connection->beginTransaction();
			$user->hardDelete();
			$this->flashMessenger()->addSuccessMessage($this->translate('User has been deleted.'));
			$connection->commit();
		} catch (\Exception $e) {
			$connection->rollback();
			throw $e;
		}
		return $this->redirect()->toRoute(null, array('action' => 'index'));
	}
	
    public function restoreMultipleAction()
	{
        $request = $this->getRequest();
        $ids = $request->getPost('ids');
        
        if (count($ids) == 0) {
            return new JsonModel();
        }
        $sl = $this->getServiceLocator();
		/* @var $collection \HtUser\Model\UserCollection */
		$collection = $sl->get('UserCollection');
        $collection->disableSoftDelete();
        $items = $collection->getAll(array('user.user_id' => $ids));
        /* @var $user UserModel */
        $user = $sl->get("User");
        $connection = $this->getConnection();
		try {
			$connection->beginTransaction();
            foreach ($items as $item) {
                $user->exchangeArray($item);
                $user->restore();
            }
			$connection->commit();
		} catch (\Exception $e) {
			$connection->rollback();
			throw $e;
		}
        return new JsonModel();
    }
    
	public function restoreAction()
	{
		$userId = $this->params()->fromRoute('id');
		$sl = $this->getServiceLocator();
		/* @var $user UserModel */
		$user = $sl->get('User');
		$url = $this->url()->fromRoute(null, array('action' => 'index'));
		if (!$user->loadSoftDeletedItem($userId)) {
			$this->flashMessenger()->addErrorMessage($this->translate('User does not exsits.'));
			return $this->referer()->redirect($url);
		}
		
		if ($user->getIsDeleted()) {
			try {
				$connection = $this->getConnection();
				$connection->beginTransaction();
				$user->restore();
				$connection->commit();
			} catch (\Exception $e) {
				$connection->rollback();
				throw $e;
			}
		}
		
		$this->flashMessenger()->addSuccessMessage($this->translate('Restore user successfully.'));
		return $this->referer()->redirect($url);
	}
	
	public function createAction()
	{
		$sl = $this->getServiceLocator();
		$form = new CreateUserForm();
		
		$errorMessage = '';
		$successMessage = '';
		$request = $this->getRequest();
		
		/* @var $user \HtUser\Model\User */
		$user = $sl->get('User');
		
		if ($request->isPost()) {
			$data = $request->getPost()->toArray();
			/* @var $authInternal \HtAuthentication\Model\Adapter\Internal */
			$authInternal = $sl->get('AuthAccount\Internal');
			$form->setInputFilter($authInternal->getFilterForAdmin());
			$form->setData($data);
			if ($form->isValid()) {
                
				if ($this->isUnique('email', $data['email'])) {
					$user->exchangeArray($data);
					$user->setUserId(null);
					$user->setIsEnabled(true);
					$connection = $this->getConnection();
					try {
						$connection->beginTransaction();
						$userId = $user->save();
                        
                        $user->addRole(Role::MEMBER_ROLE);
						$password = $authInternal->genaratePassword();
						$data['password'] = $password;
						$data['username'] = $user->getEmail();
						
						$authInternal->exchangeArray($data);
						$authInternal->register(false);
						$authInternal->linkUser($user);
						$connection->commit();
					} catch (\Exception $e) {
						$connection->rollback();
						$errorMessage = $this->translate('Cannot create user. Please try again later.');
						throw $e;
					}
					
					$this->sendMail($user->getEmail(), $password);
					$successMessage = $this->translate('Created user success. Please add one or more application this user can access.');
					$this->flashMessenger()->addSuccessMessage($successMessage);
					return $this->redirect()->toRoute('admin/user/app', array('id' => $userId));
					
				} else {
					$errorMessage = $this->translate('This email address was already used by another.');
				}
			}
		}
		
		return array(
			'form' => $form,
			'successMessage' => $successMessage,
			'errorMessage' => $errorMessage,
		);
	}
	
	public function editAction()
	{
		$sl = $this->getServiceLocator();
		$userId = $this->params()->fromRoute('id');
		
		$request = $this->getRequest();
		
		$form = new EditUserForm();
		$errorMessage = '';
		$successMessage = '';
		$showGeneratePassword = false;
        /* @var $user UserModel */
		$user = $sl->get('User');
        $user->disableSoftDelete();
		if (!$user->load($userId)) {
			$this->flashMessenger()->addErrorMessage($this->translate('User does not exists.'));
			return $this->redirect()->toRoute(null, array('action' => 'index'));
		}
				
		/* @var $authAccount \HtAuthentication\Model\AuthenticationAccount */
		$authAccount = $sl->get('AuthAccount')->getOne(
				array('user_id' => $userId, 'type' => AuthenticationAccount::TYPE_INTERNAL)
				);
		
		if ($authAccount) {
			$showGeneratePassword = true;
		}
		
		if ($request->isPost()) {
			$data = $request->getPost();
			$data->username = $user->getEmail();
			$data->email = $user->getEmail();

			$form->setData($data);
			/* @var $internal \HtAuthentication\Model\Adapter\Internal */
			$internal = $sl->get('AuthAccount\Internal');
			$inputFilter = $internal->getFilterForAdminUpdate();
			$form->setInputFilter($inputFilter);
			if ($form->isValid()) {
				$connection = $this->getConnection();
				$data = $form->getData();
				$user->exchangeArray($data);
				try {
					$connection->beginTransaction();
					$user->save();
					$connection->commit();
					$this->flashMessenger()->addSuccessMessage($this->translate('Save info successfully.'));
					return $this->redirect()->toRoute(null, array('action' => 'index'));
				} catch(\Exception $e) {
					$connection->rollback();
					throw $e;
				}
            }
		} else {
			$form->setData($user->toArray());
		}
		
        $view = new ViewModel(array(
			'form' => $form,
			'user' => $user,
			'showGeneratePassword' => $showGeneratePassword,
			'errorMessage' => $errorMessage,
			'successMessage' => $successMessage,
		));
        
        if ($user->getIsDeleted()) {
            $view->setTemplate('ht-admin/user/view-deleted.phtml');
        }
        return $view;
	}
	
	/**
	 * 
	 * @param string $email
	 * @param string $password
	 * @param string $typeMail create = 1 | regenerate-password = 2
	 */
	const TYPE_MAIL_CREATE = 1;
	const TYPE_MAIL_REGENERATE_PASSWORD = 2;
	protected function sendMail($email, $password, $typeMail = self::TYPE_MAIL_CREATE)
	{
		/* @var $mailModel \HtApplication\Model\Mail */
		$sl = $this->getServiceLocator();
		$mailModel = $sl->get("HtApplication\Model\Mail");
		/* @var $emailTemplateModel \HtApplication\Model\EmailTemplate */
		$emailTemplateModel = $sl->get("HtApplication\Model\EmailTemplate");
        $urlSite = $this->url()->fromRoute('home', array(), array('force_canonical' => true));
		if ($typeMail == self::TYPE_MAIL_CREATE) {
			$mailTemplate = $emailTemplateModel->buildMessage(EmailTemplate::ADMIN_CREATE_USER_MAIL_TEMPLATE,
				array(
					'recipient_name' => $email,
					'login_email' => $email,
					'url_site' => $urlSite,
					'login_password' => $password
				)
			);
		} else if ( $typeMail == self::TYPE_MAIL_REGENERATE_PASSWORD) {
			$mailTemplate = $emailTemplateModel->buildMessage(EmailTemplate::ADMIN_REGENERATE_PASSWORD, 
				array(
					'recipient_name' => $email,
					'new_password' => $password,
					'url_site' => $urlSite
				));
		}
		$mailModel->send($mailTemplate['subject'], $mailTemplate['body'], $email, $email);
	}


	protected function isUnique($field, $value)
	{
		/* @var $userCollection \HtUser\Model\UserCollection */
		$userCollection = $this->getServiceLocator()->get('UserCollection');
		$count = $userCollection->count(array(
			$field => $value,
		));
		if ($count > 0) {
			return false;
		}
		return true;
	}
	
	protected function gotoUserAppPage($userId, $errorMessage)
	{
		$this->flashMessenger()->addErrorMessage($errorMessage);
		return $this->redirect()->toRoute('admin/user-application', array('action' => 'list', 'id' => $userId));
	}
	
	public function generatePasswordAction()
	{
		$sl = $this->getServiceLocator();
		$userId = $this->params()->fromRoute('id');
		/* @var $user UserModel */
		$user = $sl->get('User');
		if (!$user->load($userId)) {
			return new JsonModel(array(
				'errorCode' => 1,
				'errorMessage' => $this->translate('No user found.'),
			));
		}
		
		$authAccount = $sl->get('AuthAccount')->getOne(
				array('user_id' => $userId, 'type' => AuthenticationAccount::TYPE_INTERNAL)
				);
		
		if (!$authAccount) {
			return new JsonModel(array(
				'errorCode' => 1,
				'errorMessage' => $this->translate('No internal account found.'),
			));
		}
		
		/* @var $internal \HtAuthentication\Model\Adapter\Internal */
		$internal = $sl->get('AuthAccount\Internal');
		try {
			$connection = $this->getConnection();
			$connection->beginTransaction();
			$internalLogin = $internal->getOne(array('authentication_account_id' => $authAccount->getAuthenticationAccountId()));
			$password = $internalLogin->genaratePassword();
			$internalLogin->setPassword($internalLogin->createHashPassword($password));
			$internalLogin->save();
			/**
			 * We should change password when we can sent email bring new password to user.
			 * So we should place sendMail method in transaction.
			 */
			$this->sendMail($internalLogin->getUsername(), $password, self::TYPE_MAIL_REGENERATE_PASSWORD);
			$connection->commit();
		} catch (\Exception $e) {
			$connection->rollback();
			throw $e;
		}
		
		return new JsonModel(array(
			'errorCode' => 0
		));
	}
}
