<?php
namespace HtAdmin\Test;

use HtApplication\Test\AbstractHttpControllerTestCase as ApplicationAbstractHttpControllerTestCase;
use HtAdminTest\Bootstrap;

class AbstractHttpControllerTestCase extends ApplicationAbstractHttpControllerTestCase
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
	
	protected function loginAdmin()
	{
		$sl = $this->getApplicationServiceLocator();
		$this->mockSessionManager();
		/* @var $user \HtUser\Model\User */
		$user = $sl->get('User')
				->getOne(array('email' => 'admin@hometradies.com'));
		$identity = $user->toStdClass();
		$this->logUserIn($identity);
	}

	protected $user;
	
	protected function _testAclPermission($url)
	{
		/* test not login*/
		$this->dispatch($url);
		$this->assertRedirectRegex('/\/login/');
		
		/* test user logged isn't admin*/
		$this->reset();
		if (!$this->user) {
			$this->user = $this->createUserWithInternalAuth();
		}
		$this->logUserIn($this->user->toStdClass());
		$this->dispatch($url);
		$this->assertRedirectTo('/access-denied');
	}
}