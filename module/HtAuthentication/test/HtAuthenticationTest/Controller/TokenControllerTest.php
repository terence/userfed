<?php
namespace HtAuthenticationTest\Controller;

use HtAuthentication\Test\AbstractHttpControllerTestCase;
use HtAuthentication\Model\Token;

use Zend\Stdlib\Parameters;
use Zend\Session\Container;
use HtApplication\Model\Log;

class TokenControllerTest extends AbstractHttpControllerTestCase
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

	public function testVerifyAction()
	{
		$this->markTestIncomplete();
	}
	
	/**
	 * User has permission to App-Server without org info
	 */
	public function testRedirectToClientAppActionWithoutOrg()
	{
		//setup user and login
		$oauthUserId = $this->getUniqueOAuthProviderId();
		$oauthProvider = 'facebook';
		$user = $this->createUserWithOAuth($oauthUserId, $oauthProvider);
		$this->reset();

		$sl = $this->getApplicationServiceLocator();
		$this->mockSessionManager();
		//redirect to app-server (without org)
		$this->logUserIn($user->toStdClass(), $oauthProvider, $oauthUserId);
			//find default server-app -> redirect -> check token.
		/* @var $userAppPermission \HtAuthentication\Model\UserApplication */
		$userAppPermission = $sl->get('UserApp')
			->getOne(array('user_id' => $user->getUserId()));
		$this->dispatch('/token/redirect-to-client-app?' . 
			'server=' . $userAppPermission->getServerId() . 
			'&app=' . $userAppPermission->getApplicationId());
		$this->assertRedirectRegex('/.*\/[a-zA-Z0-9]{' . Token::TOKEN_LENGTH . '}/');
	}
	
	/**
	 * User has permission to App-Server with org info
	 */
	public function testRedirectToClientAppActionWithOrg()
	{
		$sl = $this->getApplicationServiceLocator();
		//setup user and login
		$oauthUserId = $this->getUniqueOAuthProviderId();
		$oauthProvider = 'facebook';
		$user = $this->createUserWithOAuth($oauthUserId, $oauthProvider);
		$this->reset();
		
		//redirect to app-server under an org
		$this->logUserIn($user->toStdClass(), $oauthProvider, $oauthUserId);
		/* @var $app \HtAuthentication\Model\Application */
		$app = $sl->get('App');
		$app->setTitle('App title')->setDescription('App description')->save();
		/* @var $server \HtAuthentication\Model\Server */
		$server = $sl->get('Server');
		$server->exchangeArray(array(
				'title' => 'Sample title',
				'description' => 'Sample description',
				'ip' => '127.0.0.1',
				'domain' => 'fakedomain.com',
				'secret' => 'samplesecret',
				'location' => 'sample location',
				'status' => \HtAuthentication\Model\Server::STATUS_ACTIVE_PRODUCT
			))
			->save();
		/* @var $org \HtAuthentication\Model\Organisation */
		$org = $sl->get('Org');
		$org->exchangeArray(array(
				'title' => 'Sample title',
				'description' => 'Sample description',
				'domain' => 'fakedomain.com',				
			))
			->save();
		$app->addServer($server);
		$app->addAccess($org, $server);
		$org->addUser($user);
		
		/* @var $userAppPermission \HtAuthentication\Model\UserApplication */
		$userAppPermission = $sl->get('UserApp');
		$userAppPermission->setUserId($user->getUserId())
			->addAccess($app, $server, $org);
		
		$this->mockSessionManager();
		$this->dispatch('/token/redirect-to-client-app?' . 
			'server=' . $server->getServerId() . 
			'&app=' . $app->getApplicationId() .
			'&org=' . $org->getOrganisationId()
		);
		$this->assertRedirectRegex('/.*\/[a-zA-Z0-9]{' . Token::TOKEN_LENGTH . '}/');
		$this->reset();		
	}
	
	public function testRedirectToClientAppActionFailed()
	{
		$sl = $this->getApplicationServiceLocator();
		
		//setup user and login
		$oauthUserId = $this->getUniqueOAuthProviderId();
		$oauthProvider = 'facebook';
		$user = $this->createUserWithOAuth($oauthUserId, $oauthProvider);
		$this->reset();

		/* @var $userAppPermission \HtAuthentication\Model\UserApplication */
		$userAppPermission = $sl->get('UserApp')
			->getOne(array('user_id' => $user->getUserId()));
		
		//invalid server id
		$invalidId = 'invalidId';
		$this->logUserIn($user->toStdClass(), $oauthProvider, $oauthUserId);
		$this->dispatch('/token/redirect-to-client-app?' . 
			'server=' . $invalidId);
		$this->assertResponseStatusCode(500);
		$this->assertResponseHasString('Server not found');
		$this->reset();
		
		//invalid app id
		$this->logUserIn($user->toStdClass(), $oauthProvider, $oauthUserId);
		$this->dispatch('/token/redirect-to-client-app?' . 
			'server=' . $userAppPermission->getServerId() . 
			'&app=' . $invalidId);
		$this->assertResponseStatusCode(500);
		$this->assertResponseHasString('Application not found');
		$this->reset();
		
		//invalid server-app pair
			//create new app, this app will not link with default server
		/* @var $application \HtAuthentication\Model\Application */
		$application = $sl->get('App');
		$application->setTitle('App title')->setDescription('App description')->save();
		$this->logUserIn($user->toStdClass(), $oauthProvider, $oauthUserId);
		$this->dispatch('/token/redirect-to-client-app?' . 
			'server=' . $userAppPermission->getServerId() . 
			'&app=' . $application->getApplicationId());
		$this->assertResponseStatusCode(500);
		$this->assertResponseHasString('Request server not belong to request application');
		$this->reset();	
		
		//invalid org
		$this->mockSessionManager();
		$this->logUserIn($user->toStdClass(), $oauthProvider, $oauthUserId);
		$this->dispatch('/token/redirect-to-client-app?' . 
			'server=' . $userAppPermission->getServerId() . 
			'&app=' . $userAppPermission->getApplicationId()  . 
			'&org=' . $invalidId
		);
		
		$this->assertResponseStatusCode(500);
		$this->assertResponseHasString('Organisation not found');
		$this->reset();	
	}
	
	/**
	 * Mock regenerateId and getId
	 */
	protected function mockSessionManager()
	{
		$sl = $this->getApplicationServiceLocator();
		//mock session manager
		$sl->setAllowOverride(true);
		$sessionManager = $this->getMock('Zend\Session\SessionManager', array('regenerateId', 'getId'));
		$sessionManager->expects($this->any())
				->method('regenerateId')
				->will($this->returnValue(true));
		$sessionManager->expects($this->any())
				->method('getId')
				->will($this->returnValue(rand(0, 99999999)));
		$sl->setService('Zend\Session\SessionManager', $sessionManager);				
	}
}