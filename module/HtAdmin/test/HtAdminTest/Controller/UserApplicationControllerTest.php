<?php

namespace HtAdminTest\Controller;

use HtAdmin\Test\AbstractHttpControllerTestCase;
use HtUser\Model\User;
use HtAuthentication\Model\Server;
use HtAuthentication\Model\Application;

class UserApplicationControllerTest extends AbstractHttpControllerTestCase
{
	protected $faceApplicationId = 321346545;
	protected $faceServerId = 1325465432;
	protected $faceUserId = 12321354332;
	
	public function testAddAppActionSuccess()
	{
		$user = $this->createUserWithInternalAuth();
		
		$application = $this->createApplication();
		$server = $this->createServer();
		
		$this->linkServerToApp($server, $application);
		$data = array(
			'application_id' => $application->getApplicationId(),
			'server_id' => $server->getServerId()
		);
		$this->loginAdmin();
		$this->assertFlashMessengerHas('Add application successfully.', self::SUCCESS);
		$this->dispatch('/admin/user-app/add-app/' . $user->getUserId(), 'POST', $data);
	}
	
	public function testAddAppActionFailed()
	{
		$url = '/admin/user-app/add-app/';
		$this->_testAclPermission($url . $this->faceUserId);
		
		$data = array( 'application_id' => $this->faceApplicationId, 'server_id' => $this->faceServerId);
		/* test face user */
		$this->reset();
		$this->loginAdmin();
		$this->assertFlashMessengerHas('User does not exists.', self::ERROR);
		$this->dispatch($url . $this->faceUserId, 'POST', $data);
		
		/* test method isn't POST'*/
		$this->reset();
		$this->loginAdmin();
		$user = $this->createUserWithInternalAuth();
		$url .= $user->getUserId();
		$this->dispatch($url);
		$this->assertRedirectTo('/admin/user/app/' . $user->getUserId());
		
		/* test face applcation */
		$this->reset();
		$this->loginAdmin();
		$this->assertFlashMessengerHas(sprintf('Not found application with id: %s', $this->faceApplicationId), self::ERROR);
		$this->dispatch($url, 'POST', $data);
		
		/* test face server*/
		$this->reset();
		$this->loginAdmin();
		$application = $this->createApplication();
		$data['application_id'] = $application->getApplicationId();
		$this->assertFlashMessengerHas(sprintf('Not found server with id: %s', $this->faceServerId), self::ERROR);
		$this->dispatch($url, 'POST', $data);
		
		/* test application has not server */
		$this->reset();
		$this->loginAdmin();
		$server = $this->createServer();
		$data['server_id'] = $server->getServerId();
		$this->assertFlashMessengerHas(
				sprintf('<b>%s</b> has not <b>%s</b> server.', $application->getTitle(), $server->getDomain()),
				self::ERROR
				);
		$this->dispatch($url, 'POST', $data);
		
		/* test user already has permission to access server. */
		$this->linkUserToApp($user, $application, $server);
		$this->reset();
		$this->loginAdmin();
		$this->assertFlashMessengerHas('User is already has permission to access this server.', self::ERROR);
		$this->dispatch($url, 'POST', $data);
		
		/* test database exception */
		$this->reset();
		$this->loginAdmin();
		$newServer = $this->createServer();
		$data['server_id'] = $newServer->getServerId();
		$this->linkServerToApp($newServer, $application);
		$this->mockDatabaseException('UserApp');
		$this->dispatch($url, 'POST', $data);
		$this->assertResponseStatusCode(500);
		$this->assertResponseHasString(self::DATABASE_EXCEPTION_MESSAGE);
	}
	
	public function testDeleteAppActionSuccess()
	{
		$userApp = $this->linkUserToApp();
		$this->loginAdmin();
		$this->assertFlashMessengerHas('Delete application successfully.', self::SUCCESS);
		$this->dispatch('/admin/user-app/delete-app/' . $userApp->getUserApplicationId());
	}
	
	public function testDeleteAppActionFailed()
	{
		$url = '/admin/user-app/delete-app/';
		$userAppId = '13213453213'; // face id
		$this->_testAclPermission($url . $userAppId);
		
		$this->loginAdmin();
		$this->assertFlashMessengerHas('Unknow user application id.', self::ERROR);
		$this->dispatch($url . $userAppId);
		
		/* test error exception */
		$this->reset();
		$this->loginAdmin();
		$userApplication = $this->linkUserToApp();
		$this->mockDatabaseException('UserApp');
		$this->dispatch($url . $userApplication->getUserApplicationId());
		$this->assertResponseStatusCode(500);
		$this->assertResponseHasString(self::DATABASE_EXCEPTION_MESSAGE);
	}
	
	public function testListActionSuccess()
	{
		$userApp = $this->linkUserToApp();
		$this->loginAdmin();
		$this->dispatch('/admin/user/app/' . $userApp->getUserId());
		$this->assertResponseStatusCode(200);
	}
	
	public function testListActionFailed()
	{
		$url = '/admin/user/app/';
		
		$this->_testAclPermission($url . $this->faceUserId);
		$this->reset();
		$this->loginAdmin();
		$this->assertFlashMessengerHas('User does not exists.', self::ERROR);
		$this->dispatch($url . $this->faceUserId);
	}
	
	public function testViewAppActionSuccess()
	{
		$application = $this->createApplication();
		
		$this->loginAdmin();
		$this->dispatch('/admin/user-app/view-app/' . $application->getApplicationId());
		$this->assertResponseStatusCode(200);
	}
	
	public function testViewAppActionFailed()
	{
		$url = '/admin/user-app/view-app/';
		
		$this->_testAclPermission($url . $this->faceApplicationId);
		$this->reset();
		$this->loginAdmin();
		
		$this->assertFlashMessengerHas(sprintf('Not found application with %s', $this->faceApplicationId), self::ERROR);
		$this->dispatch($url . $this->faceApplicationId);
	}

	/**
	 * 
	 * @param User | int | null $user
	 * @param Application | int | null $application
	 * @param Server | int | null $server
	 * @return \HtAuthentication\Model\UserApplication
	 */
	protected function linkUserToApp($user = null, $application = null, $server = null)
	{
		if ($user === null) {
			$user = $this->createUserWithInternalAuth();
			$userId = $user->getUserId();
		} elseif ($user instanceof User) {
			$userId = $user->getUserId();
		} else {
			$userId = (int) $user;
		}
		
		if ($application === null) {
			$application = $this->createApplication();
			$applicationId = $application->getApplicationId();
		} elseif ($application instanceof Application) {
			$applicationId = $application->getApplicationId();
		} else {
			$applicationId = (int) $application;
		}
		
		if ($server === null) {
			$server = $this->createServer();
			$serverId = $server->getServerId();
		} elseif ($server instanceof Server) {
			$serverId = $server->getServerId();
		} else {
			$serverId = (int) $server;
		}
		
		$this->linkServerToApp($serverId, $applicationId);
		
		$userApp = $this->getApplicationServiceLocator()->get('UserApp');
		/* @var $userApp \HtAuthentication\Model\UserApplication */
		
		$userApp->setUserId($userId);
		$userApp->addAccess($applicationId, $serverId);
		return $userApp;
	}
	
}
