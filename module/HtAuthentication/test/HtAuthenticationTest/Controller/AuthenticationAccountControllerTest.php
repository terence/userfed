<?php
namespace HtAuthenticationTest\Controller;

use HtAuthentication\Test\AbstractHttpControllerTestCase;
use Zend\Stdlib\Parameters;
use Zend\Session\Container;
use HtApplication\Model\Log;

class AuthenticationAccountControllerTest extends AbstractHttpControllerTestCase
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
	
	public function testLoginManagementAction()
	{
		$oauthProvider = 'google';
			
		$oauthUserId = $this->getUniqueOAuthProviderId();
		$user = $this->createUserWithOAuth($oauthUserId, $oauthProvider);
		$this->reset();
		$this->logUserIn($user->toStdClass());
		$this->dispatch('/identity');
		$this->assertResponseStatusCode(200);
	}
}