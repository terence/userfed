<?php
namespace HtAuthenticationTest\Controller;

use HtAuthentication\Test\AbstractHttpControllerTestCase;
use Zend\Stdlib\Parameters;
use Zend\Session\Container;
use HtApplication\Model\Log;
use HtUser\Model\Invite;
use HtAuthentication\Model\Token;

class ApiUserControllerTest extends AbstractHttpControllerTestCase
{

	public function setUp()
	{
		/**
		 * We use style in Zend\Test 2.2 but not work
		 * @see http://framework.zend.com/manual/2.2/en/modules/zend.test.phpunit.html
		 */
//		$this->setApplicationConfig(
//			include 'D:\tuan-data\projects\repos\user-federation\userfed\code\config\application.config.php'
//		);
		/**
		 * So we mix the 2.0 way with 2.2 way
		 * http://framework.zend.com/manual/2.0/en/user-guide/unit-testing.html 
		 */
		chdir(ROOT_PATH);
		$this->setApplicationConfig(include ROOT_PATH . '/config/application.config.php');
		parent::setUp();
	}

	public function tearDown()
	{
		//clear session after each test
		$this->reset();

		parent::tearDown();
	}
	
	public function testAuthenticatedAction()
	{
		$this->dispatch('/api/user/authenticated', null, array(
			'token' => ''
		));
	}
	
	public function testAuthenticatedActionFailed()
	{
		$this->dispatch('/api/user/authenticated', 'GET');
		$this->assertResponseHasString('Token length is invalid');
		$this->reset();
		
		$this->dispatch('/api/user/authenticated', 'GET', array(
			'token' => $this->createRandomString(Token::TOKEN_LENGTH),
			'requestor' => 'not-required'
		));
		$this->assertResponseHasString('Invalid token');
	}
	
	protected function createRandomString($length)
	{
		$str = '';
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		
		do {
			$str .= str_shuffle($chars);
		} while (strlen($str) < $length);
		
		return substr($str, 0, $length);
	}
	
	public function testCreateActionSuccess()
	{
		//create user and link to existing org
		$email = $this->getUniqueEmail();
		$password = "123456";
		$org = $this->createOrganisation();
		$app = $this->createApplication();
		$server = $this->createServer();
		$app->addServer($server);
		$invitationToken = $this->getInvitationToken();
		$this->dispatch('/api/user/create', null, array(
			'user_login' => $email,
			'user_password' => $password,
			'user_email' => $email,
			'organisation_id' => $org->getOrganisationId(),
			'organisation_name' => '',
			'application_id' => $app->getApplicationId(),
			'invitation_token' => $invitationToken,
			'server_id' => $server->getServerId(),
			'WS_server_id' => $server->getSecret(),
			'WS_server_ip' => $server->getIp(),
		));
		$response = $this->getResponseObjectFromJson();
		$this->assertResponseIsJson();
		$this->assertEquals($response->result, 1);		
		
		$sm = $this->getApplicationServiceLocator();
		/* @var $usersModel \HtUser\Model\User */
		$usersModel = $sm->get('User');
		//check if user is created
		/* @var $user \HtUser\Model\User */
		$user = $usersModel->getOne(array('email' => $email));

		//verify org has user
		$this->assertTrue($org->hasUser($user));

		/* @var $userApplication \HtAuthentication\Model\UserApplication */
		$userApplication = $sm->get('UserApp');
		//verify assigned app, server to user
		$this->assertTrue($userApplication->hasUserAccess($org, $app, $server));
		
		//verify invite
		/* @var $invite \HtUser\Model\Invite */
		$invite = $sm->get('Invite');
		/* Create invitation if not existi*/
		$invite->loadByEmail($email);
		$this->assertEquals($invite->getCode(), $invitationToken);
		$this->assertEquals($invite->getEmail(), $email);
		
		//verify log
		$logCountAfter = $this->countLog($user->getUserId(), Log::TYPE_REGISTER_SUCCESS);
		$this->assertEquals($logCountAfter, 1);
	}
	
	protected function getInvitationToken($email = '')
	{
		$sl = $this->getApplicationServiceLocator();
		$factory = new \HtUser\Service\Model\InviteFactory();
		$invite = $factory->createService($sl);
		return $invite->generationCode($email);
	}
	
	public function testCreateActionFailed()
	{
		$this->dispatch('/api/user/create', null, array(
			'user_login' => '',
			'user_password' => '',
			'user_email' => '',
			'organisation_id' => '',
			'organisation_name' => '',
			'application_id' => '',
			'invitation_token' => '',
			'WS_server_id' => '',
			'WS_server_ip' => '',
		));
		
		$response = $this->getResponseObjectFromJson();
		$this->assertResponseIsJson();
		$this->assertEquals($response->result, 0);
		$this->assertTrue(!empty($response->message));
	}
	
	public function testExistActionSuccess()
	{
		$serverSecret = 'f100d8c5c68684f4770ba66bf90be2c9';
		$serverIp = $this->createIpv4();
		$user = $this->createUserWithInternalAuth();
		$this->dispatch('/api/user/exist', 'GET', array(
			'user_login' => $user->getEmail(),
			'WS_server_id' => $serverSecret,
			'WS_server_ip' => $serverIp,
		));
		
		$this->reset();
		$this->dispatch('/api/user/exist', 'GET', array(
			'user_login' => $user->getEmail(),
			'user_password' => sha1('123456'),
			'WS_server_id' => $serverSecret,
			'WS_server_ip' => $serverIp,
		));
		$contents = $this->getResponseObjectFromJson();
		$this->assertEquals($contents->result, 1);
		
		$this->reset();
		
		$organisation = $this->createOrganisation();
		$organisation->addUser($user);
		
		$this->dispatch('/api/user/exist', 'GET', array(
			'user_login' => $user->getEmail(),
			'user_password' => sha1('123456'),
			'organisation_id' => $organisation->getOrganisationId(),
			'WS_server_id' => $serverSecret,
			'WS_server_ip' => $serverIp,
		));
		
		$contents = $this->getResponseObjectFromJson();
		$this->assertEquals($contents->result, 1);
	}
	
	public function testExistActionFailed()
	{
		$serverSecret = 'f100d8c5c68684f4770ba66bf90be2c9';
		$serverIp = $this->createIpv4();
		
		$this->dispatch('/api/user/exist', 'GET', array(
			'user_login' => '',
			'WS_server_id' => '',
			'WS_server_ip' => $serverIp,
		));
		
		$contents = $this->getResponseObjectFromJson();
		$this->assertEquals($contents->result, 0);
		
		$this->reset();
		$user = $this->createUserWithInternalAuth();
		$this->dispatch('/api/user/exist', 'GET', array(
			'user_login' => $user->getEmail(),
			'user_password' => 'face password',
			'WS_server_id' => $serverSecret,
			'WS_server_ip' => $serverIp,
		));
		
		$contents = $this->getResponseObjectFromJson();
		$this->assertEquals($contents->result, 0);
		
		$this->reset();
		$org = $this->createOrganisation();
		$this->dispatch('/api/user/exist', 'GET', array(
			'user_login' => $user->getEmail(),
			'organisation_id' => $org->getOrganisationId(),
			'WS_server_id' => $serverSecret,
			'WS_server_ip' => $serverIp,
		));
		
		$contents = $this->getResponseObjectFromJson();
		$this->assertEquals($contents->result, 0);
	}
	
	public function testUpdateAction()
	{
		//create user and link to existing org
		$email = $this->getUniqueEmail();
		$password = "123456";
		$org = $this->createOrganisation();
		$app = $this->createApplication();
		$server = $this->createServer();
		$app->addServer($server);
		$app->addAccess($org, $server);
		$invitationToken = $this->getInvitationToken();
		$this->dispatch('/api/user/create', null, array(
			'user_login' => $email,
			'user_password' => $password,
			'user_email' => $email,
			'organisation_id' => $org->getOrganisationId(),
			'organisation_name' => '',
			'application_id' => $app->getApplicationId(),
			'invitation_token' => $invitationToken,
			'server_id' => $server->getServerId(),
			'WS_server_id' => $server->getSecret(),
			'WS_server_ip' => $server->getIp(),
		));
		$this->reset();
		
		$newOrg = $this->createOrganisation();
		$newApp = $this->createApplication();
		$newServer = $this->createServer();
		$newApp->addServer($newServer);
		$newApp->addAccess($newOrg, $newServer);
		$newPassword = 'update123456';
		$this->dispatch('/api/user/update', null, array(
			'user_login' => $email,
			'user_password' => $password,
			'organisation_id' => $newOrg->getOrganisationId(),
			'organisation_name' => '',
			'application_id' => $newApp->getApplicationId(),
			'server_id' => $newServer->getServerId(),
			'updated_password' => $newPassword,
			'WS_server_id' => $server->getSecret(),
			'WS_server_ip' => $server->getIp(),
		));
		
		$response = $this->getResponseObjectFromJson();
		$this->assertResponseIsJson();
		$this->assertEquals($response->result, 1);	
		
		$sl = $this->getApplicationServiceLocator();
		/* @var $usersModel \HtUser\Model\User */
		$usersModel = $sl->get('User');
		//check if user is created
		/* @var $user \HtUser\Model\User */
		$user = $usersModel->getOne(array('email' => $email));
		
		/* @var $userOrg \HtAuthentication\Model\UserOrganisation */
		$userOrg = $sl->get('UserOrg');
		$count = $userOrg->count(array('organisation_id' => $newOrg->getOrganisationId(), 'user_id' => $user->getUserId()));
		$this->assertEquals($count, 1);
		
		$internalAdapter = $sl->get('AuthAccountInternal')
			->getOne(array(
				'username' => $email,
			));
		$this->assertEquals($newPassword, $internalAdapter->getPassword());
		
		/* @var $userApp \HtAuthentication\Model\UserApplication */
		$userApp = $sl->get('UserApp');
		$count = $userApp->count(array(
			'user_id' => $user->getUserId(), 
			'application_id' => $newApp->getApplicationId(),
			'server_id' => $newServer->getServerId()
		));
		$this->assertEquals(1, $count);
	}
	
	public function testDeleteActionSuccess()
	{
		$serverSecret = 'f100d8c5c68684f4770ba66bf90be2c9';
		$serverIp = $this->createIpv4();
		$user = $this->createUserWithInternalAuth();
		$this->dispatch('/api/user/delete', 'GET', array(
			'user_login' => $user->getEmail(),
			'user_password' => sha1('123456'),
			'WS_server_id' => $serverSecret,
			'WS_server_ip' => $serverIp,
		));
		$contents = $this->getResponseObjectFromJson();
		$this->assertEquals($contents->result, 1);
	}
	
	public function testDeleteActionFailed()
	{
		$serverSecret = 'f100d8c5c68684f4770ba66bf90be2c9';
		$this->dispatch('/api/user/delete', 'GET', array(
			'user_login' => '',
			'user_password' => '',
			'WS_server_id' => $serverSecret,
		));
		
		$contents = $this->getResponseObjectFromJson();
		$this->assertEquals($contents->result, 0);
		
		/* test database exception */
		$serverIp = $this->createIpv4();
		$user = $this->createUserWithInternalAuth();
		$this->mockDatabaseException('User');
		$this->dispatch('/api/user/delete', 'GET', array(
			'user_login' => $user->getEmail(),
			'user_password' => sha1('123456'),
			'WS_server_id' => $serverSecret,
			'WS_server_ip' => $serverIp,
		));
		
		$contents = $this->getResponseObjectFromJson();
		$this->assertEquals($contents->result, 0);
	}
}