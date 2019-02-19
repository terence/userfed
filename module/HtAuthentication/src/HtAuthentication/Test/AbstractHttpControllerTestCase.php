<?php
/**
 * Base Tescase for HtAuthentication module
 * 
 * @see http://www.afewmorelines.com/mocking-user-identities-in-zf2-action-controller-unit-tests/
 */
namespace HtAuthentication\Test;

use HtApplication\Test\AbstractHttpControllerTestCase as ApplicationAbstractHttpControllerTestCase;
use Zend\Stdlib\Parameters;
use HtUser\Model\User;
use HtApplication\Model\Log;
use Zend\Session\Container;
use Zend\Authentication\Storage\Session;

class AbstractHttpControllerTestCase extends ApplicationAbstractHttpControllerTestCase
{
	protected function getUniqueOAuthProviderId()
	{
		return rand(1000000000, 9999999999);
	}

	protected function getMockedFacebookOauthClient($oauthUserId, $oauthUserData = array())
	{
		$oauthClientMock = $this->getMock('ReverseOAuth2\Client\Facebook', array('getToken', 'getSessionToken', 'getInfo', 'getOptions', 'getHttpClient', 'getSessionContainer'));
		$oauthClientMock->expects($this->any())
				->method('getToken')
				->will($this->returnValue(true));

		$oauthClientMock->expects($this->any())
				->method('getSessionToken')
				->will($this->returnValue(array(
							'access_token' => 'CAADPBUzTsBYBAD2DYFYPcKkuJxZBuubBxMl1dluZBnQxf2FTWuf0v8XDA9dg3ZCZAipGIRvt6t1AZCj9WGzlHBv0wWBhZCKQiDtZB6mX2We1lFWRqFt9VZCljdLoRm2rgq5CJocwpqu3Tq2fdbSgygCxYOUM6t7lyWNhhL1oO0yzDVQefZCeUE8pnVWaXzzeNCrQZD',
							'expires' => '5183997'
		)));

		$oauthUserData = array_merge($oauthUserData, array(
			'id' => $oauthUserId,
			'name' => 'FacebookBlabla - ' . $oauthUserId,
			'first_name' => 'FacebookBla - ' . $oauthUserId,
			'middle_name' => 'FacebookBla - ' . $oauthUserId,
			'last_name' => 'FacebookBla - ' . $oauthUserId,
			'link' => 'https://www.facebook.com/blabla - ' . $oauthUserId,
		));
		$oauthInfo = (object) $oauthUserData;

		$oauthClientMock->expects($this->any())
				->method('getInfo')
				->will($this->returnValue($oauthInfo));

		$oauthClientMock->expects($this->any())
				->method('getOptions')
				->will($this->returnValue(new \ReverseOAuth2\ClientOptions()));

		$httpClient = new \Zend\Http\Client();
		$httpClient->setAdapter(new \Zend\Http\Client\Adapter\Curl());
		$oauthClientMock->expects($this->any())
				->method('getHttpClient')
				->will($this->returnValue($httpClient));

		$session = new Container('ReverseOAuth2_' . 'ReverseOAuth2\Client\Facebook');
		$oauthClientMock->expects($this->any())
				->method('getSessionContainer')
				->will($this->returnValue($session));

		return $oauthClientMock;
	}

	protected function getMockedGoogleOauthClient($oauthUserId, $oauthUserData = array())
	{
		$oauthClientMock = $this->getMock('ReverseOAuth2\Client\Google', array('getToken', 'getSessionToken', 'getInfo', 'getOptions', 'getHttpClient', 'getSessionContainer'));
		$oauthClientMock->expects($this->any())
				->method('getToken')
				->will($this->returnValue(true));

		$oauthClientMock->expects($this->any())
				->method('getSessionToken')
				->will($this->returnValue(array(
							'access_token' => 'CAADPBUzTsBYBAD2DYFYPcKkuJxZBuubBxMl1dluZBnQxf2FTWuf0v8XDA9dg3ZCZAipGIRvt6t1AZCj9WGzlHBv0wWBhZCKQiDtZB6mX2We1lFWRqFt9VZCljdLoRm2rgq5CJocwpqu3Tq2fdbSgygCxYOUM6t7lyWNhhL1oO0yzDVQefZCeUE8pnVWaXzzeNCrQZD',
							'expires' => '5183997'
		)));

		if (!array_key_exists('email', $oauthUserData)) {
			$oauthUserData['email'] = 'email_google_' . $oauthUserId . '@gmail.com';
		}
		$oauthUserData = array_merge($oauthUserData, array(
			'id' => $oauthUserId,
			'name' => 'GoogleBlabla - ' . $oauthUserId,
			'given_name' => 'GoogleBla - ' . $oauthUserId,
			'last_name' => 'GoogleBla - ' . $oauthUserId,
			'verified_email' => true,			
			'family_name' => 'Mr',
			'picture' => 'https://lh3.googleusercontent.com/-ECEsUf_HnNc/AAAAAAAAAAI/AAAAAAAAAA0/xU1VcZ2hRjQ/photo.jpg?sz=50',
			'gender' => 'other',
			'locale' => 'vi',
		));
		
		$oauthInfo = (object) $oauthUserData;

		$oauthClientMock->expects($this->any())
				->method('getInfo')
				->will($this->returnValue($oauthInfo));

		$oauthClientMock->expects($this->any())
				->method('getOptions')
				->will($this->returnValue(new \ReverseOAuth2\ClientOptions()));

		$httpClient = new \Zend\Http\Client();
		$httpClient->setAdapter(new \Zend\Http\Client\Adapter\Curl());
		$oauthClientMock->expects($this->any())
				->method('getHttpClient')
				->will($this->returnValue($httpClient));

		$session = new Container('ReverseOAuth2_' . 'ReverseOAuth2\Client\Google');
		$oauthClientMock->expects($this->any())
				->method('getSessionContainer')
				->will($this->returnValue($session));

		return $oauthClientMock;
	}

	/**
	 * 
	 * @param string $oauthUserId
	 * @param string $oauthProvider
	 * @return User
	 */
	protected function createUserWithOAuth($oauthUserId, $oauthProvider = "facebook", $oauthUserData = array())
	{
		$sl = $this->getApplicationServiceLocator();
		$sl->setAllowOverride(true);

		if ($oauthProvider == 'facebook') {
			$oauthClientMock = $this->getMockedFacebookOauthClient($oauthUserId, $oauthUserData);
		} else {
			$oauthClientMock = $this->getMockedGoogleOauthClient($oauthUserId, $oauthUserData);
		}

		$sl->setService('ReverseOAuth2\\' . ucfirst($oauthProvider), $oauthClientMock);

		$this->getRequest()
				->setMethod('GET');
		$this->dispatch('/oauth/register-callback/' . $oauthProvider);

		/* @var $oauthAdapter \HtAuthentication\Model\Adapter\OAuth */
		$oauthAdapter = $sl->get('AuthAccount\OAuth');
		$oauthAdapter->exchangeArray(array(
			'provider_user_id'	=> $oauthUserId,
			'provider'			=> $oauthProvider
		));
		$user = $oauthAdapter->getUser();
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
	protected function createUserWithInternalLogin($email, $password = '12345678', $firstName = 'value', $lastName = 'value')
	{
		$userData = array(
			'firstname' => $firstName,
			'lastname' => $lastName,
			'email' => $email,
		);

		$requiredActivation = false;

		$sm = $this->getApplicationServiceLocator();
		/* @var $usersModel \HtUser\Model\User */
		$user = $sm->get('User');
		$user->exchangeArray($userData);
		$user->registerMemberAccount($requiredActivation);

		$authInternal = $sm->get('AuthAccountInternal');
		/* @var $authInternal \HtAuthentication\Model\Adapter\Internal */
		$data = array(
			'username' => $email,
			'password' => $password,
		);
		$authInternal->exchangeArray($data);
		$authInternal->linkUser($user);
		$authInternal->register($requiredActivation);

		return $user;
	}
	
	protected function addInternalLoginForUser($user, $email, $password = '12345678')
	{
		$sm = $this->getApplicationServiceLocator();

		$authInternal = $sm->get('AuthAccountInternal');
		/* @var $authInternal \HtAuthentication\Model\Adapter\Internal */
		$data = array(
			'username' => $email,
			'password' => $password,
		);
		$authInternal->exchangeArray($data);
		$authInternal->linkUser($user);
		$requiredActivation = false;
		$authInternal->register($requiredActivation);

		return $user;		
	}
}
