<?php

namespace HtUserTest\Controller;

//use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Stdlib\Parameters;
use HtUserTest\Bootstrap;
use HtUser\Model\User;
use HtAuthentication\Test\AbstractHttpControllerTestCase;

class InviteControllerTest extends AbstractHttpControllerTestCase
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

	public function testActivateActionSuccess()
	{
		$email = $this->createEmail();
		$this->sendInvite($email);
		$content = $this->getResponseObjectFromJson();
		$code = $content->invitationCode;
		$params = array(
			'firstname' => 'Firstname',
			'lastname' => 'Lastname',
			'password' => '123456',
			'password_confirm' => '123456',
		);
		
		$countUserBefore = $this->countUser();
		$this->dispatch('/invite/register-with-invitation-code?code=' . $code, 'POST', $params);
		$countUserAfter = $this->countUser();

		$this->assertLessThan($countUserAfter, $countUserBefore);
		$this->assertRedirectTo('/profile');
	}

	public function testActivateActionFailed()
	{
		/* test invalid code. */
		$invalidCode = 'abcabcdwjkjlkiwernckwiqka09id82kdh6vb';

		$this->dispatch('/invite/register-with-invitation-code', 'GET', array('code' => $invalidCode));
		$this->assertResponseHasString('Invalid activation key.');

		/* test already logged user */
		$email = $this->createEmail();
		$this->sendInvite($email);
		$content = $this->getResponseObjectFromJson();
		$code = $content->invitationCode;
		$this->createUserWithInternalAuth($email);

		$this->reset();

		$this->mockSessionManager();
		$this->dispatch('/login', 'POST', array('email' => $email, 'password' => '123456'));

		$this->dispatch('/invite/register-with-invitation-code', 'GET', array('code' => $code));
		$this->assertResponseHasString('If you believe this is not your account then please logout');

		$this->reset();

		/* Test already registered email */
		$this->dispatch('/invite/register-with-invitation-code', 'GET', array('code' => $code));
		$this->assertResponseHasString('You already registered an account with this invitation code.');

		/* Test activation time is over */
		$this->reset();
		$otherEmail = $this->createEmail();
		$this->sendInvite($otherEmail);

		$content = $this->getResponseObjectFromJson();
		$otherCode = $content->invitationCode;
		
		$this->reset();
		
		$invite = $this->getInviteByEmail($otherEmail);
		$invite->setInvitedDate('2013-01-05 00:00:00');
		$invite->save();

		$this->dispatch('/invite/register-with-invitation-code', 'GET', array('code' => $otherCode));
		$this->assertResponseHasString('Invitation code activation time is over.');
		
		$this->reset();
		
		/* test validate */
		
		$this->sendInvite();
		$content = $this->getResponseObjectFromJson();
		$code = $content->invitationCode;
		$this->reset();
		
		$params = array(
			'firstname' => 'Firstname',
			'lastname' => 'Lastname',
			'password' => '123',
			'password_confirm' => '123468',
		);
		$this->dispatch('/invite/register-with-invitation-code?code=' . $code, 'POST', $params);
		$this->assertResponseHasString("The two given tokens do not match");
	}
	
	public function testActivateOAuthActionSuccess()
	{
		$email = $this->createEmail();
		$this->sendInvite($email);
		$content = $this->getResponseObjectFromJson();
		$code = $content->invitationCode;

		$this->reset();
		
		$this->dispatch('/invite/activate-oauth/facebook', 'GET', array('invite-code' => $code));
		$this->assertRedirect();
	}
	
	public function testActivateOAuthActionFailed()
	{
		$email = $this->createEmail();
		$this->sendInvite($email);
		$this->reset();
		
		$this->dispatch('/invite/activate-oauth/facebook', 'GET');
		$this->assertResponseHasString('Invalid activation key.');
	}
	
	
	
	public function testActivateOAuthCallbackActionSuccess()
	{
		$this->sendInvite();
		$contents = $this->getResponseObjectFromJson();
		$code = $contents->invitationCode;
		
		$this->reset();

		$sl = $this->getApplicationServiceLocator();
		$this->mockSessionManager();
		
		$sessionManager = $sl->get('Zend\Session\SessionManager');
		$sessionManager->getStorage()->setMetadata('invite-code', $code);
		
		$oauthProvider = 'google';
		/* activate an invitation with new google account. */
		$countUserBefore = $this->countUser();
		$this->sendActivateOAuth($oauthProvider);
		$countUserAfter = $this->countUser();
		
		$this->assertLessThan($countUserAfter, $countUserBefore);
		$this->assertRedirectTo('/profile');
		
		$this->reset();
		
		/* activate an invitation with exists google account (this google accout is already registered). */
		$oauthUserId = $this->getUniqueOAuthProviderId();
		$this->createUserWithOAuth($oauthUserId, $oauthProvider);
		
		$this->sendInvite();
		$contents = $this->getResponseObjectFromJson();
		$code = $contents->invitationCode;
		$this->reset();
		
		$sessionManager->getStorage()->setMetadata('invite-code', $code);
		$countUserBefore = $this->countUser();
		$this->sendActivateOAuth($oauthProvider, $oauthUserId);
		$countUserAfter = $this->countUser();
		
		$this->assertEquals($countUserBefore, $countUserAfter);
		$this->assertRedirectTo('/profile');
		
		$this->reset();
		
		/* activate an invitation with google account has email is already registered by internal authentication. */
		$oauthUserId = $this->getUniqueOAuthProviderId();
		$email = 'email_google_' . $oauthUserId . '@gmail.com';
		$this->createUserWithInternalAuth($email);
		
		
		$this->sendInvite();
		$contents = $this->getResponseObjectFromJson();
		$code = $contents->invitationCode;
		$this->reset();
		
		$sessionManager->getStorage()->setMetadata('invite-code', $code);
		
		$countUserBefore = $this->countUser();
		$this->sendActivateOAuth($oauthProvider, $oauthUserId);
		$countUserAfter = $this->countUser();
		
		$this->assertEquals($countUserBefore, $countUserAfter);
		$this->assertRedirectTo('/profile');
		
	}
	
	public function testActivateOAuthCallbackActionFailed()
	{
		$oauthProvider = 'facebook';
		$this->dispatch('/invite/activate-oauth-callback/' . $oauthProvider);
		$this->assertResponseHasString('Invalid activation key.');
	}
	
	protected function sendActivateOAuth($oauthProvider, $oauthUserId = null)
	{
		if ($oauthUserId == null) {
			$oauthUserId = $this->getUniqueOAuthProviderId();
		}
		
		if ($oauthProvider == 'facebook') {
			$oauthClientMock = $this->getMockedFacebookOauthClient($oauthUserId);
		} else {
			$oauthClientMock = $this->getMockedGoogleOauthClient($oauthUserId);
		}
		$sl = $this->getApplicationServiceLocator();
		$sl->setAllowOverride(true);
		$sl->setService('ReverseOAuth2\\' . ucfirst($oauthProvider), $oauthClientMock);
		
		$this->getRequest()
				->setMethod('GET');
		$this->dispatch('/invite/activate-oauth-callback/' . $oauthProvider);
	}
	
	/**
	 * @return int
	 */
	protected function countUser()
	{
		$sl = $this->getApplicationServiceLocator();
		/* @var $user User */
		$user = $sl->get('User');
		return $user->count(array('is_enabled' => true));
	}

	/**
	 * @param string $email
	 * @param string $secret
	 */
	protected function sendInvite($email = null, $secret = null)
	{
		if (!$email) {
			$email = $this->createEmail();
		}

		if (!$secret) {
			$secret = 'f100d8c5c68684f4770ba66bf90be2c9';
		}

		$this->dispatch('/invite', 'GET', array('email' => $email, 'secret' => $secret));
	}

	public function testInviteSuccess()
	{
		$secret = 'f100d8c5c68684f4770ba66bf90be2c9';
		$otherSecret = 'f100d8c5c68684f4770ba66bf90be2c8';
		$email = $this->createEmail();

		$inviteCountBefore = $this->countInvite();
		$this->sendInvite($email);
		$inviteCountAfter = $this->countInvite();

		$content = $this->getResponseObjectFromJson();
		$this->assertEquals($content->errorCode, 0);
		$this->assertLessThan($inviteCountAfter, $inviteCountBefore);

		/* test email is already invited by other application. */
		$countAppInviteBefore = $this->countApplicationByInvite($email);
		$this->sendInvite($email, $otherSecret);
		$countAppInviteAfter = $this->countApplicationByInvite($email);

		$content = $this->getResponseObjectFromJson();
		$this->assertEquals($content->errorCode, 0);
		$this->assertLessThan($countAppInviteAfter, $countAppInviteBefore);

		/* test email is already registered but not has permission access this server. */
		$email = $this->createEmail();
		$user = $this->createUserWithInternalAuth($email);
		$this->addApplicationForUser($secret, $user);
		$inviteCountBefore = $this->countInvite();

		$applicationCountBefore = $this->countApplicationByUser($user);
		$this->sendInvite($email, $otherSecret);
		$applicationCountAfter = $this->countApplicationByUser($user);
		$content = $this->getResponseObjectFromJson();

		$this->assertEquals($content->errorCode, 0);
		$this->assertLessThan($applicationCountAfter, $applicationCountBefore);

		/* test email is already invited by this application but over activation time */
		$this->reset();
		$email = $this->createEmail();
		$this->sendInvite($email);
		/* @var $invite \HtUser\Model\Invite */
		$invite = $this->getInviteByEmail($email);
		$oldCode = $invite->getCode();

		$expiredTime = '2013-01-05 00:00:00';
		$invite->setInvitedDate($expiredTime);
		$invite->save();

		$this->sendInvite($email);
		$content = $this->getResponseObjectFromJson();
		$this->assertTrue($content->errorCode === 0);

		$this->assertNotEquals($oldCode, $content->invitationCode);
	}

	public function testInviteFailed()
	{
		$correctEmail = $this->createEmail();
		$correctSecret = 'f100d8c5c68684f4770ba66bf90be2c9';
		$wrongSecret = 'wrong secret';
		$wrongEmail = 'wrong.email';

		$method = 'GET';
		$url = '/invite';
		/* Test wrong Email. */
		$this->dispatch($url, $method, array('email' => $wrongEmail, 'secret' => $correctSecret));

		$content = $this->getResponseObjectFromJson();
		$this->assertTrue($content->errorCode > 0);
		$this->assertContains('Email is invalid.', $content->errorMessage);

		$this->reset();
		
		/* test wrong secret */
		$this->dispatch($url, $method, array('email' => $correctEmail, 'secret' => $wrongSecret));
		$content = $this->getResponseObjectFromJson();
		$this->assertContains('Access denied.', $content->errorMessage);

		$this->reset();
		
		/* test email has permission access. */
		$user = $this->createUserWithInternalAuth($correctEmail);
		$this->addApplicationForUser($correctSecret, $user);

		$this->dispatch($url, $method, array('email' => $correctEmail, 'secret' => $correctSecret));
		$content = $this->getResponseObjectFromJson();
		$this->assertContains('User with this email already had access permission.', $content->errorMessage);

		$this->reset();

		/* test email email is already invited. */
		$newCorrectEmail = $this->createEmail();

		$this->dispatch($url, $method, array('email' => $newCorrectEmail, 'secret' => $correctSecret));
		$this->dispatch($url, $method, array('email' => $newCorrectEmail, 'secret' => $correctSecret));
		$content = $this->getResponseObjectFromJson();
		$this->assertContains('This email is already invited.', $content->errorMessage);
	}

	/**
	 * @param string $secret Secret of server will set for user.
	 */
	protected function addApplicationForUser($secret, $user)
	{
		$sl = $this->getApplicationServiceLocator();

		/* @var $appServerCollection \HtAuthentication\Model\ApplicationServerCollection */
		$appServerCollection = $sl->get('AppServerCollection');

		/* @var $appServer \HtAuthentication\Model\ApplicationServer */
		$appServer = $appServerCollection->getBySeverSecret($secret);

		$userApp = $sl->get('UserApp');
		/* @var $userApp \HtAuthentication\Model\UserApplication */

		if ($user instanceof User) {
			$userId = $user->getUserId();
		} else {
			$userId = (int) $user;
		}

		$userApp->setUserId($userId);
		$userApp->addAccess($appServer->getApplicationId(), $appServer->getServerId());
	}

	/**
	 * Count total invite in database
	 * @return int
	 */
	protected function countInvite()
	{
		$sl = $this->getApplicationServiceLocator();
		/* @var $invite \HtUser\Model\Invite */
		$invite = $sl->get('Invite');
		return $invite->count();
	}

	/**
	 * @param \HtUser\Model\User | int $user
	 * @return int
	 */
	protected function countApplicationByUser($user)
	{
		if ($user instanceof User) {
			$userId = $user->getUserId();
		} else {
			$userId = (int) $user;
		}

		$sl = $this->getApplicationServiceLocator();

		$userApp = $sl->get('UserApp');
		/* @var $userApp \HtAuthentication\Model\UserApplication */

		return $userApp->count(array('user_id' => $userId));
	}

	/**
	 * @param string $email
	 * @return int
	 */
	protected function countApplicationByInvite($email)
	{
		$sl = $this->getApplicationServiceLocator();

		$invite = $this->getInviteByEmail($email);

		/* @var $appInvite \HtUser\Model\ApplicationInvite */
		$appInvite = $sl->get('AppInvite');
		return $appInvite->count(array('invite_id' => $invite->getInviteId()));
	}

	/**
	 * @return string Email.
	 */
	protected function createEmail()
	{
		return 'test_invite_' . rand(1000000000, 9999999999) . '@mail.com';
	}

	/**
	 * @param string $email
	 * @return \HtUser\Model\Invite
	 */
	protected function getInviteByEmail($email)
	{
		$sl = $this->getApplicationServiceLocator();
		/* @var $invite \HtUser\Model\Invite */
		$invite = $sl->get('Invite');
		$invite->loadByEmail($email);
		return $invite;
	}

}
