<?php

namespace HtAuthentication\Model\Adapter;

use AppCore\Mvc\Model\Model as AppCoreModel;
use AppCore\Mvc\Model\SoftDeleteableInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
// For AuthenticationService

use Zend\Authentication\Result as AuthenticationResult;
use ReverseOAuth2\AbstractOAuth2Client;
use HtUser\Model\User;
use Zend\Db\Sql\Select;
use HtAuthentication\Model\AuthenticationAccount;
use HtAuthentication\Service\Model\AuthenticationAccountFactory;

class OAuth extends AppCoreModel implements InputFilterAwareInterface, AdapterInterface, SoftDeleteableInterface
{

	protected $provider;
	protected $providerUserId;
	protected $authenticationAccountId;
	protected $id;
	protected $isDeleted;

	public function setProvider($value)
	{
		$this->provider = $value;
		return $this;
	}

	public function getProvider()
	{
		return $this->provider;
	}

	public function setProviderUserId($value)
	{
		$this->providerUserId = $value;
		return $this;
	}

	public function getProviderUserId()
	{
		return $this->providerUserId;
	}

	public function getAuthenticationAccountId()
	{
		return $this->authenticationAccountId;
	}

	public function getId()
	{
		return $this->id;
	}

	public function setAuthenticationAccountId($authenticationAccountId)
	{
		$this->authenticationAccountId = $authenticationAccountId;
		return $this;
	}

	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	public function getIsDeleted()
	{
		return $this->isDeleted;
	}

	public function setIsDeleted($isDeleted)
	{
		$this->isDeleted = $isDeleted;
		return $this;
	}

	protected static $providerMapping = array(
		'facebook' => 'ReverseOAuth2\Facebook',
		'google' => 'ReverseOAuth2\Google',
	);

	public static function isProviderRegisted($provider)
	{
		return isset(OAuth::$providerMapping[$provider]);
	}

	public static function getProviderService($provider)
	{
		if (!OAuth::isProviderRegisted($provider))
			throw new \Exception("Provider is not registed!");
		return OAuth::$providerMapping[$provider];
	}

	protected $client;

	public function setOAuth2Client($oauth2)
	{
		if ($oauth2 instanceof AbstractOAuth2Client) {
			$this->client = $oauth2;
		}
		return $this;
	}

	protected $request;

	public function getRequest()
	{
		return $this->request;
	}

	public function setRequest($request)
	{
		$this->request = $request;
		return $this;
	}

	/* @var $authenticationResult AuthenticationResult */
	protected $authenticationResult;
	public function authenticate()
	{
		//check request object
		$client = $this->client;

		//call OAuth provider website (Facebook/Google/...) for authentication
		if ($client->getToken($this->request)) {
			$client->getSessionToken(); // token in session
		} else {
			// last returned error (array)
			$this->authenticationResult = new AuthenticationResult(AuthenticationResult::FAILURE, null, $client->getError());
			return $this->authenticationResult;
		}

		if (is_object($this->client) AND is_object($this->client->getInfo())) {
			$this->authenticationResult =  new AuthenticationResult(AuthenticationResult::SUCCESS, $client->getInfo());
			return $this->authenticationResult;
		} else {
			$this->authenticationResult = new  AuthenticationResult(AuthenticationResult::FAILURE, null, $client->getError());
			return $this->authenticationResult;
		}
	}

	/**
	 * Check if oauth account is added in the system
	 * @return bool
	 */
	public function isAdded($providerUserId = null, $oauthProvider = null)
	{
		if ($providerUserId === null) {
			$providerUserId = $this->getProviderUserId();
		}
		if ($oauthProvider === null) {
			$oauthProvider = $this->getProvider();
		}
		if (!isset($providerUserId) || !isset($oauthProvider)) {
			throw new \Exception("Provider user id and provider not found");
		}
		if ($this->count(array(
			'provider' => $oauthProvider,
			'provider_user_id' => $providerUserId,
		)) == 0) {
			return false;
		}
		return true;
	}
	
	public function loadByProviderId($providerUserId = null, $provider = null)
	{
		if ($providerUserId === null) {
			$providerUserId = $this->getProviderUserId();
		}
		
		if ($provider === null) {
			$provider = $this->getProvider();
		}
		
		if (!isset($providerUserId) || !isset($provider)) {
			throw new \Exception("Provider user id and provider not found");
		}
		$identity = $this->getOne(array('provider' => $provider, 'provider_user_id' => $providerUserId));
		if ($identity) {
			return $this->exchangeArray($identity->toArray());
		}
		return false;
	}

	/**
	 * Get user which link to this OAuth authentication account
	 * @return User
	 * @throws \Exception
	 */
	public function getUser()
	{
		$sm = $this->getServiceManager();
        /* @var $user User */
		$user = $sm->get('User');
        if (!$this->getAuthenticationAccountId()) {
            throw new \Exception('authentication account id cannot be empty.');
        }
        
        $authAccount = $sm->get('AuthAccount');
        /**
         * We should use authenticatonAccountId to load object because it always unique.
         */
        if ($authAccount->load($this->getAuthenticationAccountId())) {
            if ($user->load($authAccount->getUserId())) {
                return $user;
            }
        }
		return null;
	}

	/**
	 * Get result row object
	 * @return boolean | \stdClass
	 */
	public function getResultRowObject()
	{
		if (!$this->resultRow) {
			return false;
		}
		return $this->resultRow;
	}

	protected $inputFilter;
	public $mapping = array(
		array('dbColumn' => 'id', 'objectProperty' => 'id', 'isIdentifier' => true),
		array('dbColumn' => 'provider', 'objectProperty' => 'provider'),
		array('dbColumn' => 'provider_user_id', 'objectProperty' => 'providerUserId'),
		array('dbColumn' => 'authentication_account_id', 'objectProperty' => 'authenticationAccountId'),
		array('dbColumn' => 'is_deleted', 'objectProperty' => 'isDeleted'),
	);

	/**
	 * @SuppressWarnings(PHPMD)
	 */
	public function setInputFilter(InputFilterInterface $inputFilter)
	{
		throw new \Exception('not use');
	}

	public function getInputFilter()
	{
		if (!$this->inputFilter) {
			$inputFilter = new InputFilter();
			$this->inputFilter = $inputFilter;
		}
		return $this->inputFilter;
	}

	public function linkUser($user)
	{
		if ($user instanceof User) {
			$userId = $user->getUserId();
		} else {
			$userId = (int) $user;
		}
		
		//make sure that we create new AuthenticationAccount object
		$sm = $this->getServiceManager();
		$factory = new AuthenticationAccountFactory();
		/* @var $authAccount \HtAuthentication\Model\AuthenticationAccount */
		$authAccount = $factory->createService($sm);
		$authAccount->disableSoftDelete();
		
		/* Restore deleted account */
		if ($this->getAuthenticationAccountId() && $authAccount->load($this->getAuthenticationAccountId())) {
			$authAccount->setIsDeleted(false);
		} else {
			$authAccount->setType(AuthenticationAccount::TYPE_OAUTH);
		}
		
		$authAccount->setUserId($userId);
		$authAccountId = $authAccount->save();
		$this->setAuthenticationAccountId($authAccountId);
		
		$this->save();
	}

	public function register()
	{
		
	}

	public function getIdentityForLogin($user = null)
	{
		if ($user === null) {
			$user = $this->getUser();
		} elseif (!$user instanceof User) {
			throw new \Exception('User must instanceof HtUser\Model\User');
		}
		$identity = $user->toStdClass();
		if ($this->authenticationResult && $this->authenticationResult->isValid()) {
			$identity->oauthProfile = $this->authenticationResult->getIdentity();
		}
		$identity->oauthProviderId = $this->getProviderUserId();
		$identity->authenticationProvider = $this->getProvider();
		return $identity;
	}

}
