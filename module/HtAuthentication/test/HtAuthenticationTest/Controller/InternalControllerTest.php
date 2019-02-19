<?php

/**
 * Tescases for InternalController
 * 
 * @see http://www.afewmorelines.com/mocking-user-identities-in-zf2-action-controller-unit-tests/
 */

namespace HtAuthenticationTest\Controller;

use HtAuthentication\Test\AbstractHttpControllerTestCase;
use Zend\Stdlib\Parameters;
use HtUser\Model\User;
use HtApplication\Model\Log;

class InternalControllerTest extends AbstractHttpControllerTestCase
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

	public function testRegisterActionSuccess()
	{
		$correctEmail = $this->getUniqueEmail();
		$correctPassword = "12345678";
		$this->getRequest()
				->setMethod('POST')
				->setPost(new Parameters(array(
					'firstname' => 'value',
					'lastname' => 'value',
					'email' => $correctEmail,
					'email_confirm' => $correctEmail,
					'password' => $correctPassword,
					'password_confirm' => $correctPassword,
		)));

		$this->mockSessionManager();		
		$this->dispatch('/internal/register');

		$this->assertResponseStatusCode(200);
		$sm = $this->getApplicationServiceLocator();
		/* @var $usersModel \HtUser\Model\User */
		$usersModel = $sm->get('User');

		//check if user is created
		/* @var $user \HtUser\Model\User */
		$user = $usersModel->getOne(array('email' => $correctEmail));
		$this->assertEquals($user->getEmail(), $correctEmail);

		$logCountAfter = $this->countLog($user->getUserId(), Log::TYPE_REGISTER_SUCCESS);
		$this->assertEquals($logCountAfter, 1);
				
		//check if internal auth is created
		/* @var $authInternalModel \HtAuthentication\Model\Adapter\Internal */
		$authInternalModel = $sm->get('AuthAccountInternal');
		/* @var $authInternal \HtAuthentication\Model\Adapter\Internal */
		$authInternal = $authInternalModel->getOne(array('username' => $correctEmail));
		$this->assertEquals($authInternal->getUsername(), $correctEmail);

		//internal auth links to correct user
		$this->assertEquals($authInternal->getUser()->getUserId(), $user->getUserId());
	}

	public function testRegisterActionFailed()
	{
		$this->mockSessionManager();

		$correctEmail = $this->getUniqueEmail();
		$correctPassword = "12345678";
        
        $data = new Parameters(array(
					'firstname' => 'value',
					'lastname' => 'value',
					'email' => $correctEmail,
					'email_confirm' => $correctEmail,
					'password' => $correctPassword,
					'password_confirm' => $correctPassword,
                ));
        
		$this->getRequest()
				->setMethod('POST')
				->setPost($data);

		//create user
		$this->dispatch('/internal/register');

		//create user with same info
		$this->dispatch('/internal/register');
		//we should see this error message
		$this->assertResponseHasString("This email address was already used by another");

		//too short password
		$email = $this->getUniqueEmail();
		$this->createUserUsingController($email, "11");
		$this->assertUserNotExistWithEmail($email);
		//long short password
		$email = $this->getUniqueEmail();
		$this->createUserUsingController($email, "0123456789012345678901234567890123456789");
		$this->assertUserNotExistWithEmail($email);

		//not valid email
		$this->createUserUsingController("asdfasdf", "11");
		$this->assertUserNotExistWithEmail($email);
        
        
        // register with deleted oauth email
        $oauthUserId = $this->getUniqueOAuthProviderId();
        $user = $this->createUserWithOAuth($oauthUserId);
        
        $user->setIsDeleted(true);
        $user->save();
        $this->reset();
        
        $data->set('email', $user->getEmail())
                ->set('email_confirm', $user->getEmail());
        
        $this->getRequest()->setMethod('POST')->setPost($data);
        $this->dispatch('/internal/register');
        $this->assertResponseHasString("This email address was already used by another");
	}

	public function testActivateActionSuccess()
	{
		$email = $this->getUniqueEmail();
		$this->createUserUsingController($email);

		//activate
		$sm = $this->getApplicationServiceLocator();
		/* @var $authInternalModel \HtAuthentication\Model\Adapter\Internal */
		$authInternalModel = $sm->get('AuthAccountInternal');
		/* @var $authInternal \HtAuthentication\Model\Adapter\Internal */
		$authInternal = $authInternalModel->getOne(array('username' => $email));
		
		$user = $authInternal->getUser();
		$logCountBefore = $this->countLog($user->getUserId(), Log::TYPE_LOGIN_SUCCESS);
		
		$this->dispatch('/internal/activate?key=' . $authInternal->getActivationKey());
		
		$logCountAfter = $this->countLog($user->getUserId(), Log::TYPE_LOGIN_SUCCESS);
		$this->assertEquals($logCountAfter, $logCountBefore + 1);
		
		$authInternal = $authInternalModel->getOne(array('username' => $email));

		//user should be activated
		$this->assertSame((int) $authInternal->getIsActivated(), 1);
	}

	public function testActivateActionFailed()
	{
		$wrongKey = "";
		$this->dispatch('/internal/activate?key=' . $wrongKey);
		$this->assertResponseHasString('Invalid activation key.');

		$wrongKey = "wewillneverhasthiskey";
		$this->dispatch('/internal/activate?key=' . $wrongKey);
		$this->assertResponseHasString('Invalid activation key.');

		//activate an already activated account
		$this->mockSessionManager();
		$email = $this->getUniqueEmail();
		$this->createUserUsingController($email);
		$sm = $this->getApplicationServiceLocator();
		/* @var $authInternalModel \HtAuthentication\Model\Adapter\Internal */
		$authInternalModel = $sm->get('AuthAccountInternal');
		/* @var $authInternal \HtAuthentication\Model\Adapter\Internal */
		$authInternal = $authInternalModel->getOne(array('username' => $email));
		$this->dispatch('/internal/activate?key=' . $authInternal->getActivationKey());
		$this->reset();
		$this->dispatch('/internal/activate?key=' . $authInternal->getActivationKey());

		$this->assertResponseHasString('User is already activated.');
	}

	public function testLoginActionSuccess()
	{
		$this->mockSessionManager();
		$email = $this->getUniqueEmail();
		$password = "asdfasdf";
		$user = $this->createUserWithInternalLogin($email, $password);

		//should log
		$logCountBefore = $this->countLog($user->getUserId(), Log::TYPE_LOGIN_SUCCESS);

		$this->getRequest()
				->setMethod('POST')
				->setPost(new Parameters(array(
					'email' => $email,
					'password' => $password,
		)));

		$this->dispatch('/internal/login');

		//should redirect
		$this->assertRedirectTo('/profile');
		$logCountAfter = $this->countLog($user->getUserId(), Log::TYPE_LOGIN_SUCCESS);
		/* @var $log \HtApplication\Model\Log */
		$this->assertEquals($logCountAfter, $logCountBefore + 1);
	}

	public function testLoginActionAdminLoginSuccess()
	{
		$this->mockSessionManager();
		//admin should redirect to another page
		$this->getRequest()
				->setMethod('POST')
				->setPost(new Parameters(array(
					'email' => 'admin@hometradies.com',
					'password' => '123456',
		)));
		$this->dispatch('/internal/login');

		$this->assertRedirectTo('/profile');
	}

	public function testLoginActionFailed()
	{
		$wrongPass = 'wrong-pass';
		//wrong user/pass
		$this->getRequest()
				->setMethod('POST')
				->setPost(new Parameters(array(
					'email' => 'wrong-email',
					'password' => $wrongPass,
		)));
		$this->dispatch('/internal/login');
		//no redirect
		$this->assertResponseStatusCode(200);

		//correct username/wrong pass
		$this->mockSessionManager();
		$email = $this->getUniqueEmail();
		$password = "asdfasdf";
		$user = $this->createUserWithInternalLogin($email, $password);

		$this->getRequest()
				->setMethod('POST')
				->setPost(new Parameters(array(
					'email' => $email,
					'password' => $wrongPass,
		)));
		$logCountBefore = $this->countLog($user->getUserId(), Log::TYPE_LOGIN_FAILED);
		$this->dispatch('/internal/login');
		$logCountAfter = $this->countLog($user->getUserId(), Log::TYPE_LOGIN_FAILED);
		//increase log count
		$this->assertEquals($logCountAfter,$logCountBefore + 1);
		//no redirect
		$this->assertResponseStatusCode(200);
	}

	public function testLoginRememberMeSuccess()
	{
		$sl = $this->getApplicationServiceLocator();
		$sl->setAllowOverride(true);
		$sessionManager = $this->getMock('Zend\Session\SessionManager', array('regenerateId', 'rememberMe'));
		$sessionManager->expects($this->any())
				->method('regenerateId')
				->will($this->returnValue(true));

		$sessionManager->expects($this->once())
				->method('rememberMe')
				->with($this->equalTo(2 * 3600));
		$sl->setService('Zend\Session\SessionManager', $sessionManager);

		$email = $this->getUniqueEmail();
		$password = "asdfasdf";
		$this->createUserWithInternalLogin($email, $password);
		$this->getRequest()
				->setMethod('POST')
				->setPost(new Parameters(array(
					'email' => $email,
					'password' => $password,
					'remember' => 1
		)));
		$this->dispatch('/internal/login');
	}

	public function testLoginRememberMeFailed()
	{
		$sl = $this->getApplicationServiceLocator();
		$sl->setAllowOverride(true);
		$sessionManager = $this->getMock('Zend\Session\SessionManager', array('regenerateId', 'rememberMe'));
		$sessionManager->expects($this->any())
				->method('regenerateId')
				->will($this->returnValue(true));

		$sessionManager->expects($this->never())//remember me never call
				->method('rememberMe');
		$sl->setService('Zend\Session\SessionManager', $sessionManager);

		$email = $this->getUniqueEmail();
		$password = "asdfasdf";
		$this->createUserWithInternalLogin($email, $password);
		$this->getRequest()
				->setMethod('POST')
				->setPost(new Parameters(array(
					'email' => $email,
					'password' => $password,
					'remember' => 0
		)));
		$this->dispatch('/internal/login');
	}

	public function testAddActionSuccess()
	{
		$oauthUserId = $this->getUniqueOAuthProviderId();
		$oauthProvider = "facebook";
		//user already login after this
		$user = $this->createUserWithOAuth($oauthUserId, $oauthProvider);

		$logCountBefore = $this->countLog($user->getUserId(), Log::TYPE_ADD_LOGIN_SUCCESS);
		
		$email = $this->getUniqueEmail();
		$password = "asdfasdf";
		$this->getRequest()
				->setMethod('POST')
				->setPost(new Parameters(array(
					'email' => $email,
					'email_confirm' => $email,
					'password' => $password,
					'password_confirm' => $password,
		)));
		$this->dispatch('/internal/add');

		$logCountAfter = $this->countLog($user->getUserId(), Log::TYPE_ADD_LOGIN_SUCCESS);
		$this->assertEquals($logCountAfter, $logCountBefore + 1);
		
		$sl = $this->getApplicationServiceLocator();
		/* @var $internalAuth \HtAuthentication\Model\Adapter\Internal */
		$internalAuth = $sl->get('AuthAccountInternal');
		$internalAuth = $internalAuth->getOne(array(
			'username' => $email,
		));
		$this->assertTrue(($internalAuth->getAuthenticationAccountId() > 0));
		$this->assertTrue(($internalAuth->getUser()->getUserId() == $user->getUserId()));
	}

	public function testAddActionFailed()
	{
		$this->mockSessionManager();
		$email = $this->getUniqueEmail();
		$password = "asdfasdf";

		//already had an internal login
		$user = $this->createUserWithInternalLogin($email, $password);

		//login
		$this->logUserIn($user->toStdClass());

		//add internal login
		$this->getRequest()
				->setMethod('POST')
				->setPost(new Parameters(array(
					'email' => $email,
					'password' => $password,
		)));
		$this->dispatch('/internal/add');
		//should be failed and redirect to index page
		$this->assertRedirectTo('/identity');
	}

	public function testValidateUniqueFieldActionSuccess()
	{
		//email is duplicate
		$this->mockSessionManager();
		$email = $this->getUniqueEmail();
		$password = "asdfasdf";

		//already had an internal login
		$this->createUserWithInternalLogin($email, $password);

		$this->dispatch(
				'/internal/validate-unique-field', 'GET', array(
			'field' => 'username',
			'value' => $email
				)
		);
		$this->assertResponseHasString('false');

		$this->reset();

		//email is not duplicate
		$email = $this->getUniqueEmail();
		$this->dispatch(
				'/internal/validate-unique-field', 'GET', array(
			'field' => 'username',
			'value' => $email
				)
		);
		$this->assertResponseHasString('true');
	}

	/**
	 * Email not in Internal Adapter but from OAuth to user table
	 */
	public function testValidateUniqueFieldActionWithEmailImporteFromOAuth()
	{
		$email = $this->getUniqueEmail();
		$oauthUserId = $this->getUniqueOAuthProviderId();
		$oauthProvider = 'google';
		$oauthUserData = array(
			'email' => $email
		);
		$this->createUserWithOAuth($oauthUserId, $oauthProvider, $oauthUserData);
        /* reset to clean identity created by action dispatch to register-callback */
        $this->reset();
		$this->dispatch(
				'/internal/validate-unique-field', 'GET', array(
			'field' => 'username',
			'value' => $email
				)
		);
		$this->assertResponseHasString('false');
	}

	public function testValidateUniqueFieldActionFailed()
	{
		//check not allow field
		$this->dispatch(
				'/internal/validate-unique-field', 'GET', array(
			'field' => 'notallowfield',
			'value' => 'whatever'
				)
		);
		$this->assertResponseIsJson();
		$obj = $this->getResponseObjectFromJson();
		$this->assertTrue((
				isset($obj) &&
				isset($obj->msg) &&
				$obj->msg == "This operation is not allowed"
		));
	}

	public function testDeleteActionSuccess()
	{
		$sl = $this->getApplicationServiceLocator();

		$email = $this->getUniqueEmail();
		$password = "asdfasdf";
		$user = $this->createUserWithFacebookAndAddInternalLogin($email, $password);

		/* @var $internalAuth \HtAuthentication\Model\Adapter\Internal */
		$internalAuth = $sl->get('AuthAccountInternal');
		$internalAuth = $internalAuth->getOne(array(
			'username' => $email,
		));
		$internalId = $internalAuth->getAuthenticationInternalId();
		$this->assertTrue(($internalId > 0));

		$logCountBefore = $this->countLog($user->getUserId(), Log::TYPE_DELETE_LOGIN_SUCCESS);
		
		$this->dispatch('/internal/delete');
		
		$logCountAfter = $this->countLog($user->getUserId(), Log::TYPE_DELETE_LOGIN_SUCCESS);
		$this->assertEquals($logCountAfter, $logCountBefore + 1);
		
		$internalAuth = $internalAuth->getOne(array(
			'username' => $email,
		));
		$this->assertTrue(is_null($internalAuth));
	}

	/**
	 * For now, update internal login mean change password
	 */
	public function testUpdateActionSuccess()
	{
		//=== 1. change pass success ===/
		$sl = $this->getApplicationServiceLocator();

		$email = $this->getUniqueEmail();
		$password = "asdfasdf";
		$user = $this->createUserWithFacebookAndAddInternalLogin($email, $password);

		/* @var $internalAuth \HtAuthentication\Model\Adapter\Internal */
		$internalAuth = $sl->get('AuthAccountInternal');
		$internalAuth = $internalAuth->getOne(array(
			'username' => $email,
		));
		$this->assertTrue(($internalAuth->getAuthenticationAccountId() > 0));

		$logCountBefore = $this->countLog($user->getUserId(), Log::TYPE_CHANGE_PASSWORD_SUCCESS);
		//do change password
		$newPassword = 'newpassword';
		$this->getRequest()
				->setMethod('POST')
				->setPost(new Parameters(array(
					'current_password' => $password,
					'password' => $newPassword,
					'password_confirm' => $newPassword,
		)));
		$this->dispatch('/internal/update');
		$internalAuth = $internalAuth->getOne(array(
			'username' => $email,
		));
		$logCountAfter = $this->countLog($user->getUserId(), Log::TYPE_CHANGE_PASSWORD_SUCCESS);

		//check new password change
		$this->assertEquals($internalAuth->createHashPassword($newPassword), $internalAuth->getPassword());
		//check log increase
		$this->assertEquals($logCountAfter, $logCountBefore + 1);

		//=== 2. change with wrong password ===/
		$logCountBefore = $this->countLog($user->getUserId(), Log::TYPE_CHANGE_PASSWORD_FAILED);
		//do change password
		$newPassword2 = 'newpassword2';
		$this->getRequest()
				->setMethod('POST')
				->setPost(new Parameters(array(
					'current_password' => 'wrong current password',
					'password' => $newPassword2,
					'password_confirm' => $newPassword2,
		)));
		$this->dispatch('/internal/update');
		$internalAuth = $internalAuth->getOne(array(
			'username' => $email,
		));
		$logCountAfter = $this->countLog($user->getUserId(), Log::TYPE_CHANGE_PASSWORD_FAILED);

		//password should not change
		$this->assertEquals($internalAuth->createHashPassword($newPassword), $internalAuth->getPassword());
		//check log increase
		$this->assertEquals($logCountAfter - $logCountBefore, 1);
	}

	public function testUpdateActionFailed()
	{
		//no internal login
		$sl = $this->getApplicationServiceLocator();
		$sl->setAllowOverride(true);
		$flashMessenger = $this->getMock('Zend\Mvc\Controller\Plugin\FlashMessenger', array('addErrorMessage'));
		$flashMessenger->expects($this->once())
				->method('addErrorMessage')
				->with('You do not have internal login.');
		$sl->get('ControllerPluginManager')->setService('flashMessenger', $flashMessenger);

		$oauthUserId = $this->getUniqueOAuthProviderId();
		$this->createUserWithOAuth($oauthUserId);
		$this->dispatch('/internal/update');
	}

	protected function assertUserNotExistWithEmail($email)
	{
		$sm = $this->getApplicationServiceLocator();
		/* @var $usersModel \HtUser\Model\User */
		$usersModel = $sm->get('User');
		$this->assertEquals($usersModel->count(array('email' => $email)), 0);
	}

	protected function assertUserExistWithEmail($email)
	{
		$sm = $this->getApplicationServiceLocator();
		/* @var $usersModel \HtUser\Model\User */
		$usersModel = $sm->get('User');
		$this->assertEquals($usersModel->count(array('email' => $email)), 1);
	}

	/**
	 * Create user using Facebook login then create another internal login
	 * @param string $email
	 * @param string $password
	 * @return User
	 */
	protected function createUserWithFacebookAndAddInternalLogin($email, $password = "asdfasdf")
	{
		$oauthUserId = $this->getUniqueOAuthProviderId();
		$oauthProvider = "facebook";
		$user = $this->createUserWithOAuth($oauthUserId, $oauthProvider);

		$this->getRequest()
				->setMethod('POST')
				->setPost(new Parameters(array(
					'email' => $email,
					'email_confirm' => $email,
					'password' => $password,
					'password_confirm' => $password,
		)));
		$this->dispatch('/internal/add');
		return $user;
	}

	/**
	 * 
	 * @param string $email
	 * @param string $password
	 * @param string $firstName
	 * @param string $lastName
	 * @return User
	 */
	protected function createUserUsingController($email, $password = '12345678', $firstName = 'value', $lastName = 'value')
	{
		$userData = array(
			'firstname' => $firstName,
			'lastname' => $lastName,
			'email' => $email,
			'email_confirm' => $email,
			'password' => $password,
			'password_confirm' => $password,
		);
		$this->getRequest()
				->setMethod('POST')
				->setPost(new Parameters($userData));
		$this->dispatch('/internal/register');

		$sm = $this->getApplicationServiceLocator();
		/* @var $user \HtUser\Model\User */
		$usersModel = $sm->get('User');
		//check if user is created
		/* @var $user \HtUser\Model\User */
		$user = $usersModel->getOne(array('email' => $email));
		return $user;
	}

	protected function activateUser($email)
	{
		$sm = $this->getApplicationServiceLocator();
		/* @var $authInternalModel \HtAuthentication\Model\Adapter\Internal */
		$authInternalModel = $sm->get('AuthAccountInternal');
		/* @var $authInternal \HtAuthentication\Model\Adapter\Internal */
		$authInternal = $authInternalModel->getOne(array('username' => $email));
		$this->dispatch('/internal/activate?key=' . $authInternal->getActivationKey());
		$this->dispatch('/logout');
	}
}
