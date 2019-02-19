<?php

/**
 * Tescases for InternalController
 * 
 * @see http://www.afewmorelines.com/mocking-user-identities-in-zf2-action-controller-unit-tests/
 */

namespace HtAuthenticationTest\Controller;

use HtAuthentication\Test\AbstractHttpControllerTestCase;
use Zend\Stdlib\Parameters;
use Zend\Session\Container;
use Zend\Authentication\Result as AuthenticationResult;
use HtApplication\Model\Log;

class OAuthControllerTest extends AbstractHttpControllerTestCase
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
	
	public function testRegisterAction()
	{
		$this->dispatch('/oauth/register/facebook');
		$this->assertRedirectRegex("/.*facebook\.com.*/");
	}
	
	public function testRegisterCallbackActionFacebookSuccess()
	{
		$sl = $this->getApplicationServiceLocator();
		$sl->setAllowOverride(true);

		$oauthProvider = "facebook";
		$oauthUserId = $this->getUniqueOAuthProviderId();
		$oauthClientMock = $this->getMockedFacebookOauthClient($oauthUserId);
		$sl->setService('ReverseOAuth2\\' . ucfirst($oauthProvider), $oauthClientMock);

		$this->getRequest()
				->setMethod('GET');
		$this->dispatch('/oauth/register-callback/' . $oauthProvider);
		
		$this->assertUserExistByOAuthAccount($oauthUserId, $oauthProvider);
	}

	public function testRegisterCallbackActionGoogleSuccess()
	{
		$sl = $this->getApplicationServiceLocator();
		$sl->setAllowOverride(true);

		$oauthProvider = "google";
		$oauthUserId = $this->getUniqueOAuthProviderId();
		$oauthClientMock = $this->getMockedGoogleOauthClient($oauthUserId);
		$sl->setService('ReverseOAuth2\\' . ucfirst($oauthProvider), $oauthClientMock);

		$this->getRequest()
				->setMethod('GET');
		$this->dispatch('/oauth/register-callback/' . $oauthProvider);
		
		$this->assertUserExistByOAuthAccount($oauthUserId, $oauthProvider);
	}
	
	public function testRegisterCallbackActionFailed()
	{
		//register using oauth user provider id which already linked to another account
		$oauthProvider = 'google';
			
		$oauthUserId = $this->getUniqueOAuthProviderId();
		$this->createUserWithOAuth($oauthUserId, $oauthProvider);
		$this->reset();
		//register again
		$sl = $this->getApplicationServiceLocator();
		$oauthClientMock = $this->getMockedGoogleOauthClient($oauthUserId);
		$sl->setAllowOverride(true);
		$sl->setService('ReverseOAuth2\\' . ucfirst($oauthProvider), $oauthClientMock);
		$this->dispatch('/oauth/register-callback/' . $oauthProvider);
		$this->assertRedirectTo('/profile');
		$this->reset();
		
		//oauth info's email already used by another user
		$oauthUserId = $this->getUniqueOAuthProviderId();
		$email = $this->getUniqueEmail();
		$user = $this->createUserWithInternalAuth($email);
		$this->reset();
		$oauthClientMock = $this->getMockedGoogleOauthClient($oauthUserId, array('email' => $email));
		$sl = $this->getApplicationServiceLocator();
		$sl->setAllowOverride(true);
		$sl->setService('ReverseOAuth2\\' . ucfirst($oauthProvider), $oauthClientMock);
		$this->dispatch('/oauth/register-callback/'. $oauthProvider);
		$this->assertResponseHasString(sprintf("Your email '%s', already registered in our website, try login using that email.", $email));
        
        $this->reset();
        /* register with user has been soft deleted */
        $user->delete();
        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);
		$sl->setService('ReverseOAuth2\\' . ucfirst($oauthProvider), $oauthClientMock);
		$this->dispatch('/oauth/register-callback/'. $oauthProvider);
        $this->assertResponseHasString(sprintf('Email is existed in our system but you can not login with  that email.Please contact administrator for support.'));
        
	}
	
	public function testLoginAction()
	{
		$this->dispatch('/oauth/login/facebook');
		$this->assertRedirectRegex("/.*facebook\.com.*/");
	}
	
	public function testLoginCallbackSuccessAction()
	{
		$oauthProvider = "google";
		$oauthUserId = $this->getUniqueOAuthProviderId();
		$this->createUserWithOAuth($oauthUserId, $oauthProvider);
		$this->reset();

		$oauthClientMock = $this->getMockedGoogleOauthClient($oauthUserId);
		$sl = $this->getApplicationServiceLocator();
		$sl->setAllowOverride(true);
		$sl->setService('ReverseOAuth2\\' . ucfirst($oauthProvider), $oauthClientMock);
		$this->dispatch('/oauth/login-callback/' . $oauthProvider);
		
		/* @var $authService \Zend\Authentication\AuthenticateService */
		$authService = $sl->get("AuthenticateService");
		$this->assertTrue($authService->hasIdentity());
		
		$this->reset();
		/* login with an oauth account not registered. */
		$oauthUserId = $this->getUniqueOAuthProviderId();
		$oauthClientMock = $this->getMockedGoogleOauthClient($oauthUserId);
		$sl = $this->getApplicationServiceLocator();
		$sl->setAllowOverride(true);
		$sl->setService('ReverseOAuth2\\' . ucfirst($oauthProvider), $oauthClientMock);
		
		$countUserBeforeLogin = $this->countUser();
		$this->dispatch('/oauth/login-callback/' . $oauthProvider);
		$countUserAfterLogin = $this->countUser();
		$this->assertEquals($countUserAfterLogin - 1, $countUserBeforeLogin);
		
		/* checkout user logged.*/
		/* @var $authService \Zend\Authentication\AuthenticateService */
		$authService = $sl->get("AuthenticateService");
		$this->assertTrue($authService->hasIdentity());
	}
	
	public function testLoginCallbackFailedAction()
	{
		$oauthProvider = "google";
		$errorMessage = "errormessage";
		$oauthClientMock = $this->getMockFailedAuthenticateOAuthClient($errorMessage, $oauthProvider);

		$sl = $this->getApplicationServiceLocator();
		$sl->setAllowOverride(true);
		$sl->setService('ReverseOAuth2\\' . ucfirst($oauthProvider), $oauthClientMock);
		$this->dispatch('/oauth/login-callback/' . $oauthProvider);
		
		/* @var $authService \Zend\Authentication\AuthenticateService */
		$authService = $sl->get("AuthenticateService");
		$this->assertFalse($authService->hasIdentity());
		$this->assertResponseHasString($errorMessage);
	}
	
	public function testDeleteLoginAction()
	{
		$oauthProvider = 'google';
		$oauthUserId = $this->getUniqueOAuthProviderId();
		
		//create user, login
		$email = $this->getUniqueEmail();
		$user = $this->createUserWithInternalLogin($email);
		$this->logUserIn($user->toStdClass());
		
		$sl = $this->getApplicationServiceLocator();
		$oauthClientMock = $this->getMockedGoogleOauthClient($oauthUserId);
		$sl->setAllowOverride(true);
		$sl->setService('ReverseOAuth2\\' . ucfirst($oauthProvider), $oauthClientMock);

		//create oauth login
		$this->dispatch('/oauth/add-login-callback/' . $oauthProvider);
		
		//delete oauth login
		$oauthAdapter = $sl->get('AuthAccount\OAuth')
							->getOne(array(
								'provider_user_id' => $oauthUserId,
								'provider' => $oauthProvider
							));
		$this->assertFlashMessengerHas(sprintf("Delete %s login successfully.", ucfirst($oauthProvider)), self::SUCCESS);
		$this->dispatch('/oauth/delete-login/' . $oauthAdapter->getId());
		$this->assertRedirectTo('/identity');
	}

	public function testDeleteLoginFailedAction()
	{
		$oauthProvider = 'google';
		
		//delete unique login
		$oauthUserId = $this->getUniqueOAuthProviderId();		
		$user = $this->createUserWithOAuth($oauthUserId, $oauthProvider);
		$this->reset();
		$this->logUserIn($user->toStdClass(), $oauthProvider, $oauthUserId);
		$sl = $this->getApplicationServiceLocator();
		$oauthAdapter = $sl->get('AuthAccount\OAuth')
							->getOne(array(
								'provider_user_id' => $oauthUserId,
								'provider' => $oauthProvider
							));		
		$this->assertFlashMessengerHas('You are not allowed to delete this login because this is your last remaining one.', self::ERROR);
		$this->dispatch('/oauth/delete-login/' . $oauthAdapter->getId());		
		$this->assertRedirectTo('/identity');
		
		//invalid oauth id
		$oauthUserId = $this->getUniqueOAuthProviderId();		
		$user = $this->createUserWithOAuth($oauthUserId, $oauthProvider);
		$email = $this->getUniqueEmail();
		$this->addInternalLoginForUser($user, $email);
		$this->reset();
		
		$this->logUserIn($user->toStdClass(), $oauthProvider, $oauthUserId);
		$id = '11111111';//fake id
		$this->assertFlashMessengerHas(sprintf('Not found oauth login with id: %s', $id), self::ERROR);
		$this->dispatch('/oauth/delete-login/' . $id);
		$this->assertRedirectTo('/identity');
		
		//delete current login
		$this->reset();
		$this->logUserIn($user->toStdClass(), $oauthProvider, $oauthUserId);
		$oauthAdapter = $sl->get('AuthAccount\OAuth')
							->getOne(array(
								'provider_user_id' => $oauthUserId,
								'provider' => $oauthProvider
							));		
		$this->assertFlashMessengerHas('You cannot delete currently login.', self::ERROR);
		$this->dispatch('/oauth/delete-login/' . $oauthAdapter->getId());
		$this->assertRedirectTo('/identity');
	}
	
	public function testAddLoginCallbackSuccessAction()
	{
		$oauthProvider = 'google';
		$oauthUserId = $this->getUniqueOAuthProviderId();
		
		//create user, login
		$email = $this->getUniqueEmail();
		$user = $this->createUserWithInternalLogin($email);
		$this->logUserIn($user->toStdClass());
		
		$sl = $this->getApplicationServiceLocator();
		$oauthClientMock = $this->getMockedGoogleOauthClient($oauthUserId);
		$sl->setAllowOverride(true);
		$sl->setService('ReverseOAuth2\\' . ucfirst($oauthProvider), $oauthClientMock);

		//check message
		$this->assertFlashMessengerHas(sprintf("Add login using %s successfully", $oauthProvider), self::SUCCESS);

		$logCountBefore = $this->countLog($user->getUserId(), Log::TYPE_ADD_LOGIN_SUCCESS);
		
		$this->dispatch('/oauth/add-login-callback/' . $oauthProvider);
		
		//check creation
		$this->assertUserExistByOAuthAccount($oauthUserId, $oauthProvider, $user->getUserId());
		
		//check log
		$logCountAfter = $this->countLog($user->getUserId(), Log::TYPE_ADD_LOGIN_SUCCESS);		
		$this->assertEquals($logCountAfter, $logCountBefore + 1);
		
		$this->assertRedirectTo('/identity');
	}
	
	public function testAddLoginCallbackFailedAction()
	{
		//oauth account already linked with another user
		$oauthProvider = 'google';
		$oauthUserId = $this->getUniqueOAuthProviderId();
		
		//create userA with oauth id
		$this->createUserWithOAuth($oauthUserId, $oauthProvider);
		$this->reset();
		
			//create another userB 
		$email = $this->getUniqueEmail();
		$user = $this->createUserWithInternalLogin($email);
		$this->logUserIn($user->toStdClass());

			//login then add the same oauth id
		$sl = $this->getApplicationServiceLocator();
		$oauthClientMock = $this->getMockedGoogleOauthClient($oauthUserId);
		$sl->setAllowOverride(true);
		$sl->setService('ReverseOAuth2\\' . ucfirst($oauthProvider), $oauthClientMock);
		$this->assertFlashMessengerHas(sprintf('Your %s id already linked with an existed account', $oauthProvider), self::ERROR);
		$this->dispatch('/oauth/add-login-callback/' . $oauthProvider);
		
		//oauth error during add
		$this->reset();
		$this->logUserIn($user->toStdClass());
		$sl->setService('ReverseOAuth2\\' . ucfirst($oauthProvider), $oauthClientMock);
		$countAccountBefore = $this->countAccount($user);
		$this->dispatch('/oauth/add-login-callback/' . $oauthProvider);
		$countAccountAfter = $this->countAccount($user);
		$this->assertEquals($countAccountAfter, $countAccountBefore);
	}
			
	public function testAddLoginAction()
	{
		$email = $this->getUniqueEmail();
		$user = $this->createUserWithInternalLogin($email);
		$this->logUserIn($user->toStdClass());
		
		$oauthProvider = 'facebook';
		$this->dispatch('/oauth/add-login/' . $oauthProvider);
		$this->assertRedirectRegex("/.*facebook\.com.*/");
	}
	
	protected function getMockFailedAuthenticateOAuthClient($errorMessage = "errormessage", $provider = "google")
	{
		//setup standard mock version
		$oauthClientMock = $this->getMock("ReverseOAuth2\\Client\\" . ucfirst($provider), array('getOptions', 'getHttpClient', 'getToken', 'getError'));
		
		$oauthClientMock->expects($this->any())
				->method('getOptions')
				->will($this->returnValue(new \ReverseOAuth2\ClientOptions()));

		$httpClient = new \Zend\Http\Client();
		$httpClient->setAdapter(new \Zend\Http\Client\Adapter\Curl());
		$oauthClientMock->expects($this->any())
				->method('getHttpClient')
				->will($this->returnValue($httpClient));
		
		//should return error
		$oauthClientMock->expects($this->any())
				->method('getToken')
				->will($this->returnValue(false));
		
		$oauthClientMock->expects($this->any())
				->method('getError')
				->will($this->returnValue($errorMessage));
		
		return $oauthClientMock;		
	}
	
	protected function assertUserExistByOAuthAccount($oauthUserId, $oauthProvider, $userId = null)
	{
		$sl = $this->getApplicationServiceLocator();
		/* @var $oauthAdapter \HtAuthentication\Model\Adapter\OAuth */
		$oauthAdapter = $sl->get('AuthAccount\OAuth');
		$oauthAdapter = $oauthAdapter->getOne(array(
			'provider_user_id' => $oauthUserId,
			'provider' => $oauthProvider
		));
		$this->assertNotNull($oauthAdapter, sprintf("No oauth account found with oauth user id: %s, oauth provider: %s", $oauthUserId, $oauthProvider));
		
		if (!$oauthAdapter) {
			$user = $oauthAdapter->getUser();
			if (isset($userId)) {
				$this->assertEquals($user->getUserId(), $userId);									
			} else {
				$this->assertTrue(($user->getUserId() > 0));					
			}
		}
	}
	
	/**
	 * 
	 * @param string $oauthUserId
	 * @param string $oauthProvider
	 * @return \HtUser\Model\User
	 */
	protected function getUserByOAuthAccount($oauthUserId, $oauthProvider)
	{
		$sl = $this->getApplicationServiceLocator();
		/* @var $oauthAdapter \HtAuthentication\Model\Adapter\OAuth */
		$oauthAdapter = $sl->get('AuthAccount\OAuth');
		$oauthAdapter = $oauthAdapter->getOne(array(
			'provider_user_id' => $oauthUserId,
			'provider' => $oauthProvider
		));
		$user = $oauthAdapter->getUser();
		return $user;
	}
	
	protected function countUser()
	{
		$sl = $this->getApplicationServiceLocator();
		/* @var $user \HtUser\Model\User */
		$user = $sl->get('User');
        /**
         * some action after we dispatch call disableSoftDelete method,
         * When we call model again ServiceManager return a model has been disabledSoftDelete
         * So we need enable soft-delete again for ensure model don't return the result unexpectedly
         * 
         * @todo New A Zend\Db\TableGateway\TableGateway
         * */
        $user->enableSoftDelete();
		return $user->count();
	}
	
	protected function countAccount($user)
	{
		if ($user instanceof \HtUser\Model\User) {
			$userId = $user->getUserId();
		} else {
			$userId = (int) $user;
		}
		/* @var $authenticationAccount \HtAuthentication\Model\AuthenticationAccount */
		$authenticationAccount = $this->getApplicationServiceLocator()->get('AuthAccount');
		return $authenticationAccount->count(array('user_id' => $userId));
	}
}
