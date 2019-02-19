<?php
namespace HtAdmin\Controller;

use HtApplication\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Null as PaginatorNull;
use Zend\Db\Sql\Select;

class UserApplicationController extends AbstractActionController
{
	public function addAppAction()
	{
		$sl = $this->getServiceLocator();
		
		$userId = $this->params()->fromRoute('id');
		
		$user = $sl->get('User');
		if (!$user->load($userId)) {
			return $this->gotoUserList($this->translate('User does not exists.'));
		}
		
		if ($this->getRequest()->isPost()) {
			$applicationId = $this->params()->fromPost('application_id');
			$serverId = $this->params()->fromPost('server_id');
		} else {
			return $this->gotoUserAppPage($userId);
		}
		
		$application = $sl->get('App');
		
		if (!$application->load($applicationId)) {
			return $this->gotoUserAppPage($userId, sprintf($this->translate('Not found application with id: %s'), $applicationId));
		}
		
		$server = $sl->get('Server');
		if (!$server->load($serverId)) {
			return $this->gotoUserAppPage($userId, sprintf($this->translate('Not found server with id: %s'), $serverId));
		}
		
		if (!$application->hasServer($server)) {
			return $this->gotoUserAppPage($userId,
					sprintf($this->translate('<b>%s</b> has not <b>%s</b> server.'), $application->getTitle(), $server->getDomain())
					);
		}
		
		/* @var $userApplication \HtAuthentication\Model\UserApplication */
		$userApplication = $sl->get('UserApp');
		
		$userApplication->setUserId($userId);
		if ($userApplication->hasAccess($application, $server)) {
			return $this->gotoUserAppPage($userId,
					$this->translate('User is already has permission to access this server.')
					);
		}
		
		$connection = $this->getConnection();
		try {
			$connection->beginTransaction();
			$userApplication->addAccess($application, $server);
			$connection->commit();
			return $this->gotoUserAppPage($userId, $this->translate('Add application successfully.'), false);
		} catch(\Exception $e) {
			$connection->rollback();
			throw $e;
		}
		
	}
	
	public function deleteAppAction()
	{
		$userAppId = $this->params()->fromRoute('id', null);

		$sl = $this->getServiceLocator();
		/* @var $userApplication \HtAuthentication\Model\UserApplication */
		$userApplication = $sl->get('UserApp');
		if (!$userApplication->load($userAppId)) {
			return $this->gotoUserList('Unknow user application id.');
		}

		$connection = $this->getConnection();
		try {
			$connection->beginTransaction();
			$userApplication->delete();
			$connection->commit();
			return $this->gotoUserAppPage($userApplication->getUserId(), 
					$this->translate('Delete application successfully.'),
					false);
		} catch (\Exception $e) {
			$connection->rollback();
			throw $e;
		}
	}
	
	public function viewUserAction()
	{
		$sl = $this->getServiceLocator();
		$userId = $this->params()->fromRoute('id');
		/* @var $user \HtUser\Model\User */
		$user = $sl->get('User');
		$flashMessager = $this->flashMessenger();
		if (!$user->load($userId)) {
			return $this->gotoUserList($this->translate('User does not exists.'));
		}
		/* @var $userAppCollection \HtAuthentication\Model\UserApplicationCollection */
		$userAppCollection = $sl->get('UserAppCollection');
		
		$totalUserApps = $userAppCollection->count(array('user_id' => $userId));
		$pagingParams = $this->pagingParams();
		$pagingParamsData = $pagingParams->get($totalUserApps);
		$userApplications = $userAppCollection->getUserApps($userId, $pagingParamsData['limit'], $pagingParamsData['offset']);
		$userApps = $userApplications->buffer();
		$serverIds = array();
		foreach ($userApps as $userApp) {
			$serverIds[] = $userApp->server_id;
		}
		
		/* @var $application \HtAuthentication\Model\Application */
		$application = $sl->get('App');
		$allApplication = $application->getAll();
		if ($allApplication) {
			$defaultApps = $allApplication->buffer();
			$defaultApp = $defaultApps->current();
			$defaultAppId = $defaultApp->getApplicationId();
			/* @var $appServerCollection \HtAuthentication\Model\ApplicationServerCollection */
			$appServerCollection = $sl->get('AppServerCollection');
			$serverRowset = $appServerCollection->getServerByApplicationId($defaultAppId, $serverIds);
		}
		
		$paginator = new Paginator(new PaginatorNull($totalUserApps));
		$paginator->setCurrentPageNumber($pagingParamsData['page']);
		$paginator->setItemCountPerPage($pagingParamsData['limit']);
		
		$errorMessage = '';
		if ($flashMessager->hasErrorMessages()) {
			$errorMessage = implode('<br />', $flashMessager->getErrorMessages());
		}
		
		$successMessage = '';
		if ($flashMessager->hasSuccessMessages()) {
			$successMessage = implode('<br />', $flashMessager->getSuccessMessages());
		}
		
		return array(
			'allApplication' => $allApplication,
			'userApplications' => $userApplications,
			'user' => $user,
			'paginator' => $paginator,
			'successMessage' => $successMessage,
			'errorMessage' => $errorMessage,
			'serverDefault' => $serverRowset
		);
	}
	
	/**
	 * Get server by application id for XMLHttpRequest
	 * @return \Zend\View\Model\JsonModel
	 */
	public function getServerAction()
	{
		$appId = $this->params()->fromQuery('app-id');
		$userId = $this->params()->fromRoute('id');
		$errorCode = 0;
		
		if ($appId) {
			$sl = $this->getServiceLocator();
			/* @var $appServerCollection \HtAuthentication\Model\ApplicationServerCollection */
			$appServerCollection = $sl->get('AppServerCollection'); 
			
			/* @var $userAppCollection \HtAuthentication\Model\UserApplicationCollection */
			$userAppCollection = $sl->get('UserAppCollection');
			$userApplications = $userAppCollection->getUserApps($userId);

			$userApps = $userApplications->buffer();
			$userAppIds = array();
			foreach ($userApps as $userApp) {
				$userAppIds[] = $userApp->server_id;
			}
			$serverRowset = $appServerCollection->getServerByApplicationId($appId, $userAppIds);
			$server = $serverRowset->toArray();
			return new JsonModel(array('errorCode' => $errorCode, 'server' => $server));
		} else {
			return new JsonModel(array(
				'errorCode' => 1,
				'errorMessage' => $this->translate('You must select an application.')
			));
		}
	}
	
	/**
	 * Redirect to list
	 * @param int $userId
	 * @param string $message
	 * @param bool $$error
	 * @return \Zend\Http\Response
	 */
	protected function gotoUserAppPage($userId, $message = '', $error = true)
	{
		if ($error) {
			$this->flashMessenger()->addErrorMessage($message);
		} else {
			$this->flashMessenger()->addSuccessMessage($message);
		}
		return $this->redirect()->toRoute('admin/user/app', array('id' => $userId));
	}
	
	public function viewAppAction()
	{
		$applicationId = $this->params()->fromRoute('id');
		
		$sl = $this->getServiceLocator();
		
		/* @var $application \HtAuthentication\Model\Application */
		$application = $sl->get('App');
		
		if (!$application->load($applicationId)) {
			return $this->gotoAppListPage(sprintf($this->translate('Not found application with %s'), $applicationId));
		}
		
		/* @var $userApplicationCollection \HtAuthentication\Model\UserApplicationCollection */
		$userApplicationCollection = $sl->get('UserAppCollection');
		
		$where = array("application_id" => $applicationId);
		
		$totalUsers = $userApplicationCollection->count($where);
		
		$pagingParams = $this->pagingParams();
		$pagingParamsData = $pagingParams->get($totalUsers);
		
		$paginator = new Paginator(new PaginatorNull($totalUsers));
		$paginator->setItemCountPerPage($pagingParamsData['itemPerPage']);
		$paginator->setCurrentPageNumber($pagingParamsData['page']);
		
		$errorMessage = '';
		if ($this->flashMessenger()->hasErrorMessages()) {
			$errorMessage = implode('<br />', $this->flashMessenger()->getErrorMessages());
		}
		
		$successMessage = '';
		if ($this->flashMessenger()->hasSuccessMessages()) {
			$successMessage = implode('<br />', $this->flashMessenger()->getSuccessMessages());
		}
		
		$users = $userApplicationCollection->getAppUsers($where, $pagingParamsData['offset'], $pagingParamsData['limit']);
		
		return array(
			'users' => $users,
			'application' => $application,
			'totalUsers' => $totalUsers,
			'paginator' => $paginator,
			'errorMessage' => $errorMessage,
			'successMessage' => $successMessage,
		);
	}
	
	/**
	 * Add message to flashMessager and redirect to application list page.
	 * @param string $message
	 * @param error $error
	 * @return \Zend\Http\Response
	 */
	protected function gotoAppListPage($message = '', $error = true)
	{
		if ($error) {
			$this->flashMessenger()->addErrorMessage($message);
		} else {
			$this->flashMessenger()->addSuccessMessage($message);
		}
		
		return $this->redirect()->toRoute('admin/app');
	}
	
	/**
	 * Get list user for XmlHttpRequest
	 * route: '/admin/user-app/get-user/:applicationId?search={keysearch}'
	 * @return JsonModel
	 */
	public function getUserAction()
	{
		$page = $this->params()->fromQuery('page', 1);
		$limit = (int) $this->params()->fromQuery('page_limit', 10);
		$search = $this->params()->fromQuery('search');
		$sl = $this->getServiceLocator();
		
		$offset = ($page - 1) * $limit;
		
		/* @var $user \HtUser\Model\User */
		$user = $sl->get('User');
		
		$where = function (Select $select) use ($search) {
			$select->where
					->like('firstname', "%{$search}%")
					->or->like('lastname', "%{$search}%")
					->or->like('email', "%{$search}%");
		};
		
		$totalCount = $user->count($where);
		
		$results = $user->getAll($where, $offset, $limit);
		$users = $results->toArray();
		
		return new JsonModel(array(
			'total' => $totalCount,
			'users' => $users,
		));
	}
	
	/**
	 * Allow an user access to an application.
	 */
	public function addUserAction()
	{
		$request = $this->getRequest();
		$applicationId = $this->params()->fromRoute('id');
		if ($request->isPost()) {
			$organisationId = $this->params()->fromPost('organisation_id');
			$userId = $this->params()->fromPost('user_id');
			$serverId = $this->params()->fromPost('server_id');
		} else {
			if ($applicationId) {
				return $this->gotoAppUserList($applicationId);
			}
			return $this->gotoAppListPage();
		}
		
		$sl = $this->getServiceLocator();
		
		/* @var $application \HtAuthentication\Model\Application */
		$application = $sl->get('App');
		if (!$application->load($applicationId)) {
			return $this->gotoAppListPage(sprintf($this->translate('Not found application with id: %s'), $applicationId));
		}
		
		/* @var $user \HtUser\Model\User */
		$user = $sl->get('User');
		if (!$user->load($userId)) {
			return $this->gotoAppUserList($applicationId, sprintf($this->translate('Not found user with id: %s'), $userId));
		}
		
		/* @var $organisation \HtAuthentication\Model\Organisation */
		$organisation = $sl->get('Org');
		if (!$organisation->load($organisationId)) {
			return $this->gotoAppUserList($applicationId, sprintf($this->translate('Not found organisation with id: %s'), $organisationId));
		}
		
		/* @var $server \HtAuthentication\Model\Server */
		$server = $sl->get('Server');
		if (!$server->load($serverId)) {
			return $this->gotoAppUserList($applicationId, sprintf($this->translate('Not found server with id: %s'), $serverId));
		}
		
		/* @var $userOrg \HtAuthentication\Model\UserOrganisation */
		$name = $user->getFirstName() . ' ' . $user->getLastName();
		if (!$organisation->hasUser($user)) {
			return $this->gotoAppUserList($applicationId, sprintf(
					$this->translate('User <b>%s</b> not in <b>%s</b> Organisation.'), $name, $organisation->getTitle()
			));
		}
		
		if (!$application->hasServer($server)) {
			return $this->gotoAppUserList($applicationId,
					sprintf($this->translate('<b>%s</b> has not <b>%s</b> server.'), $application->getTitle(), $server->getDomain())
			);
		}
		if (!$organisation->hasAccess($application, $server)) {
			return $this->gotoAppUserList($applicationId,
					sprintf($this->translate('<b>%s</b> organisation has not permission access to server <b>%s</b>'),
					$organisation->getTitle(), $server->getDomain()
			));
		}
		
		/* @var $userApplication \HtAuthentication\Model\UserApplication */
		$userApplication = $sl->get('UserApp');
		
		$userApplication->setUserId($user->getUserId());
		if ($userApplication->hasUserAccess($organisation, $application, $server)) {
			return $this->gotoAppUserList($applicationId,
					sprintf($this->translate('<b>%s</b> already permission access to server <b>%s</b>.'), $name, $server->getDomain()
					
			));
		}
		
		$connection = $this->getConnection();
		try {
			$connection->beginTransaction();
			$userApplication->addUserAccess($organisation, $application, $server);
			$userApplication->save();
			$connection->commit();
			return $this->gotoAppUserList($applicationId, $this->translate('Add user successfully.'), false);
		} catch(\Exception $e) {
			$connection->rollback();
			throw $e;
		}
		
	}
	
	/**
	 * Add message to flashMessager and redirect to App-User list page.
	 * @param int $applicationId Application Id
	 * @param string $message
	 * @param boolean $error
	 * @return \Zend\Http\Response
	 */
	protected function gotoAppUserList($applicationId, $message = '', $error = true)
	{
		if ($error) {
			$this->flashMessenger()->addErrorMessage($message);
		} else {
			$this->flashMessenger()->addSuccessMessage($message);
		}
		return $this->redirect()->toRoute('admin/app/user', array('id' => $applicationId));
	}
	
	/**
	 * Add message to flashMessager and redirect to user list page.
	 * @param string $message
	 * @param boolean $error
	 * @return \Zend\Http\Response
	 */
	protected function gotoUserList($message = '', $error = true)
	{
		if ($error) {
			$this->flashMessenger()->addErrorMessage($message);
		} else {
			$this->flashMessenger()->addSuccessMessage($message);
		}
		return $this->redirect()->toRoute('admin/user');
	}


	public function deleteUserAction()
	{
		$sl = $this->getServiceLocator();
		$userAppId = $this->params()->fromRoute('id');
		
		/* @var $userApplication \HtAuthentication\Model\UserApplication */
		$userApplication = $sl->get('UserApp');
		
		if (!$userApplication->load($userAppId)) {
			return $this->redirect()->toRoute('admin/app');
		}
		
		$applicationId = $userApplication->getApplicationId();
		
		$connection = $this->getConnection();
		try {
			$connection->beginTransaction();
			$userApplication->delete();
			$connection->commit();
			return $this->gotoAppUserList($applicationId, $this->translate('Delete user successfully.'), false);
		} catch(\Exception $e) {
			$connection->rollback();
			throw $e;
		}
	}
}
