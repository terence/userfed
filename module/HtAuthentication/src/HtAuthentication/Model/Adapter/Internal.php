<?php

/**
 * Because this system allow an user can login with
 * diffirent method (use internal account, external account...)
 */

namespace HtAuthentication\Model\Adapter;

use AppCore\Mvc\Model\Model as AppCoreModel;
use AppCore\Mvc\Model\SoftDeleteableInterface;
use HtAuthentication\Model\AuthenticationAccount;
use HtUser\Model\User;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\Factory as InputFactory;
use Zend\Validator\Identical;
use Zend\Authentication\Result;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;

use HtApplication\Model\Password;

class Internal extends AppCoreModel implements InputFilterAwareInterface, AdapterInterface, SoftDeleteableInterface
{
	const MIN_PASSWORD_LENGTH = 6;
	const MAX_PASSWORD_LENGTH = 32;
	
	const FAILURE_NOT_ACTIVATED = -5;
	const FAILURE_UNKNOWN_ERROR = -999;
	protected $authenticationInternalId;
	protected $authenticationAccountId;
	protected $username;
	protected $password;
	protected $changePasswordKey;
	protected $activationKey;
	protected $isActivated;
	protected $isDeleted;
			
	protected $inputFilter;
	protected $user;
	protected $mapping = array(
		array('dbColumn' => 'authentication_internal_id', 'objectProperty' => 'authenticationInternalId', 'isIdentifier' => true),
		array('dbColumn' => 'authentication_account_id', 'objectProperty' => 'authenticationAccountId'),
		array('dbColumn' => 'username', 'objectProperty' => 'username'),
		array('dbColumn' => 'password', 'objectProperty' => 'password'),
		array('dbColumn' => 'change_password_key', 'objectProperty' => 'changePasswordKey'),
		array('dbColumn' => 'activation_key', 'objectProperty' => 'activationKey'),
		array('dbColumn' => 'is_activated', 'objectProperty' => 'isActivated'),
		array('dbColumn' => 'is_deleted', 'objectProperty' => 'isDeleted'),
	);

	public function getAuthenticationInternalId()
	{
		return $this->authenticationInternalId;
	}

	public function getAuthenticationAccountId()
	{
		return $this->authenticationAccountId;
	}

	public function getUsername()
	{
		return $this->username;
	}

	public function getPassword()
	{
		return $this->password;
	}

	public function getChangePasswordKey()
	{
		return $this->changePasswordKey;
	}

	public function getActivationKey()
	{
		return $this->activationKey;
	}

	public function getIsActivated()
	{
		return $this->isActivated;
	}

	public function setAuthenticationInternalId($authenticationInternalId)
	{
		$this->authenticationInternalId = $authenticationInternalId;
		return $this;
	}

	public function setAuthenticationAccountId($authenticationAccountId)
	{
		$this->authenticationAccountId = $authenticationAccountId;
		return $this;
	}

	public function setUsername($username)
	{
		$this->username = $username;
		return $this;
	}

	public function setPassword($password)
	{
		$this->password = $password;
		return $this;
	}

	public function setChangePasswordKey($changePasswordKey)
	{
		$this->changePasswordKey = $changePasswordKey;
		return $this;
	}

	public function setActivationKey($activationKey)
	{
		$this->activationKey = $activationKey;
		return $this;
	}

	public function setIsActivated($isActivated)
	{
		$this->isActivated = $isActivated;
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

		/**
	 * 
	 * @param string $password
	 * @return string
	 */
	public function createHashPassword($password)
	{
		return sha1($password);
	}

	/**
	 * 
	 * @param string $key
	 * @return string
	 */
	public function createHashActivation($key)
	{
		return hash("sha256", $key . date("Y-m-d H:i:s"));
	}

	public function setUser($user)
	{
		$this->user = $user;
		return $this;
	}

	/**
	 * @return \HtUser\Model\User
	 * @throws \Exception
	 */
	public function getUser()
	{
		if (!$this->user) {
			$sm = $this->getServiceManager();
			$authAccount = $sm->get('AuthAccount');
			/* @var $authAccount \HtAuthentication\Model\AuthenticationAccount */
			if (!$authAccount->load($this->getAuthenticationAccountId())) {
				throw new \Exception('No Authentication Account with id: ' . $this->getAuthenticationAccountId());
			}

			$user = $sm->get('User');
			if (!$user->load($authAccount->getUserId())) {
				throw new \Exception('No user with id: ' . $authAccount->getUserId());
			}

			$this->user = $user;
		}
		return $this->user;
	}

	/**
	 * Add login for
	 * @param \HtUser\Model\User | string $user
	 */
	public function linkUser($user)
	{
		$sm = $this->getServiceManager();
		if ($user instanceof User) {
			$userId = $user->getUserId();
		} else {
			$userId = (int) $user;
		}
		$userModel = $sm->get('User');
		if (!$userModel->load($userId)) {
			throw new \Exception('Not found user with id: ' . $userId);
		}
		/* @var $authAccount AuthenticationAccount */
		$authAccount = $sm->get('AuthAccount');
		$authAccount->setUserId($userId)->setType(AuthenticationAccount::TYPE_INTERNAL);
		$authAccountId = $authAccount->save();
		$this->setAuthenticationAccountId($authAccountId);
		$this->save();
	}

	/**
	 * input filter for register internal account.
	 * @return InputFilter
	 */
	public function getInputFilter()
	{
		if (!$this->inputFilter) {
			$inputFilter = new InputFilter();
			$factory = new InputFactory();

			$inputFilter->add($factory->createInput(array(
						'name' => 'firstname',
						'required' => true,
						'filters' => array(
							array('name' => 'StringTrim'),
							array('name' => 'StripTags'),
						),
			)));

			$inputFilter->add($factory->createInput(array(
						'name' => 'lastname',
						'required' => true,
						'filters' => array(
							array('name' => 'StringTrim'),
							array('name' => 'StripTags'),
						),
			)));

			$sm = $this->getServiceManager();
			$inputFilter->add($factory->createInput(array(
						'name' => 'email',
						'required' => true,
						'filters' => array(
							array('name' => 'StringTrim'),
						),
						'validators' => array(
							array(
                                /* When user register we should check all record in the table
                                 * That's why we disable soft delete here */
								'name' => 'HtAuthentication\Validator\CheckUniqueModelProperty',
								'options' => array(
									'model' => $this->disableSoftDelete(),
									'property' => 'username',
									'messages' => array(
										'notUnique' => 'This email address was already used by another.'
									),
								),
							),
							array(
								'name' => 'HtAuthentication\Validator\CheckUniqueModelProperty',
								'options' => array(
									'model' => $sm->get('User')->disableSoftDelete(),
									'property' => 'email',
									'messages' => array(
										'notUnique' => 'This email address was already used by another.'
									),
								),
							)
						),
			)));

			$inputFilter->add($factory->createInput(array(
						'name' => 'email_confirm',
						'required' => true,
						'validators' => array(
							array(
								'name' => 'Identical',
								'strict' => false,
								'options' => array(
									'token' => 'email',
									'messages' => array(
										Identical::NOT_SAME => 'Your email and confirmation email do not match.'
									),
								)
							)
						)
			)));

			$inputFilter->add($factory->createInput(array(
						'name' => 'password',
						'required' => true,
						'validators' => array(
							array(
								'name' => 'StringLength',
								'options' => array(
									'min' => self::MIN_PASSWORD_LENGTH,
									'max' => self::MAX_PASSWORD_LENGTH,
								),
							),
						),
			)));

			$inputFilter->add($factory->createInput(array(
						'name' => 'password_confirm',
						'required' => true,
						'validators' => array(
							array(
								'name' => 'Identical',
								'strict' => false,
								'options' => array(
									'token' => 'password',
									'messages' => array(
										Identical::NOT_SAME => 'Your password and confirmation password do not match.'
									),
								)
							)
						)
			)));

			$this->inputFilter = $inputFilter;
		}
		return $this->inputFilter;
	}

	/**
	 * get InputFilter for add an internal login.
	 * @return InputFilter
	 */
	public function getFitlerForAddLogin()
	{
		$inputFilter = $this->getInputFilter();
		$inputFilter->remove('firstname')
				->remove('lastname')
				->remove('email');
		
		$sm = $this->getServiceManager();
		$inputFilter->add(array(
			'name' => 'email',
			'required' => true,
			'filters' => array(
				array('name' => 'StringTrim'),
			),
			'validators' => array(
				array(
					'name' => 'HtAuthentication\Validator\CheckUniqueModelProperty',
					'options' => array(
						'model' => $this,
						'property' => 'username',
						'messages' => array(
							'notUnique' => 'This email address was already used by another.'
						),
					),
				),
			),
		));
		
		return $inputFilter;
	}

	/**
	 * get InputFilter for update an internal login.
	 * @return InputFilter
	 */
	public function getFilterForUpdate()
	{
		$inputFilter = $this->getInputFilter();
		$inputFilter->remove('firstname')
				->remove('lastname')
				->remove('email')
				->remove('email_confirm');
		return $inputFilter;
	}
	
	public function getFilterForAdmin()
	{
		$inputFilter = $this->getInputFilter();

		$inputFilter
				->remove('password')
				->remove('password_confirm');
		return $inputFilter;
	}
	
	public function getFilterForAdminUpdate()
	{
		$inputFilter = $this->getFilterForAdmin();
		$inputFilter->remove('email');
		$inputFilter->remove('email_confirm');
		$inputFilter->add(array(
			'name' => 'email',
			'required' => false
			));
		return $inputFilter;
	}
	
	/**
	 * @SuppressWarnings(PHPMD)
	 */
	public function setInputFilter(InputFilterInterface $inputFilter)
	{
		throw new \Exception('Not use.');
	}

	/**
	 * Register an internal login account
	 */
	public function register($requiredActivation = true)
	{
		$this->setIsActivated(!$requiredActivation);
		if ($requiredActivation) {
			$this->setActivationKey($this->createActivationKey($this->getUsername()));
		}
		$this->setPassword($this->createHashPassword($this->getPassword()));
		$this->save();
	}
	
	/**
	 * 
	 * @param string $username
	 * @return boolean|\HtAuthentication\Model\Adapter\Internal
	 */
	public function loadByUsername($username)
	{
		$internal = $this->getOne(array('username' => $username));
		if ($internal) {
			$this->exchangeArray($internal->toArray());
			return $this;
		}
		return false;
	}

	/**
	 * Create activation key and check unique.
	 * @return string Activation
	 */
	protected function createActivationKey($key = '')
	{
		$sm = $this->getServiceManager();
		/* @var $internal \HtAuthentication\Model\Adapter\Internal */
		$internal = $sm->get('AuthAccountInternal');

		do {
			$isUnique = true;
			$value = $key . microtime();
			$key = $this->createHashActivation($value);
			$count = $internal->count(array('activation_key' => $key));
			if ($count > 0) {
				$isUnique = false;
			}
		} while (!$isUnique);

		return $key;
	}
	
	/**
	 * Authentication
	 * @return boolean | \Zend\Authentication\Result
	 */
	public function authenticate()
	{
		/* @var $authInternal \HtAuthentication\Model\Adapter\Internal */
		$internalLogin = $this->getOne(array(
			'username' => $this->username,
			'password' => $this->createHashPassword($this->password),
		));
		
		if(!$internalLogin) {
			return new Result(Result::FAILURE_CREDENTIAL_INVALID, $this->username, array('Invalid email or password'));
		}
		
		if (!$internalLogin->getIsActivated()) {
			return new Result(self::FAILURE_NOT_ACTIVATED, $this->username, array('User has not activated yet.'));
		}
		$sm = $this->getServiceManager();
		$authAccount = $sm->get('AuthAccount');
		/* @var $authAccount \HtAuthentication\Model\AuthenticationAccount */
		if (!$authAccount->load($internalLogin->getAuthenticationAccountId())) {
			return new Result(self::FAILURE_UNKNOWN_ERROR, $this->username, array('Unknow Error.'));
		}
		
		$user = $sm->get('User');
		/* @var $user \HtUser\Model\User */
		if (!$user->load($authAccount->getUserId())) {
			$user->disableSoftDelete();
			if ($user->load($authAccount->getUserId()) && $user->getIsDeleted()) {
				return new Result(Result::FAILURE_CREDENTIAL_INVALID, $this->username, array('Invalid email or password'));
			}
			return new Result(self::FAILURE_UNKNOWN_ERROR, $this->username, array('Unknow Error.'));
		}
				
		if (!$user->getIsEnabled()) {
			return new Result(self::FAILURE_UNKNOWN_ERROR, $this->username, array('This account is suspend, please contact admin if you think this is an error.'));
		}
		
		$identity = $user->toStdClass();
		$identity->authenticationProvider = AuthenticationAccount::TYPE_INTERNAL;
		return new Result(Result::SUCCESS, $identity, array('Authentication successfully.'));
	}	

	public function loadByUser($user)
	{
		if ($user instanceof User) {
			$userId = $user->getUserId();
		} else {
			$userId = (int) $user;
		}
		
		$sm = $this->getServiceManager();
		/* @var $authenticationAccount AuthenticationAccount */
		$authenticationAccount = $sm->get('AuthAccount');
		if (!$this->getSoftDeleteable()) {
			$authenticationAccount->disableSoftDelete();
		}
		$authenticationAccount->getOne(array('user_id' => $userId, 'type' => AuthenticationAccount::TYPE_INTERNAL));
		
		if ($authenticationAccount) {
			$internal = $this->getOne(array('authentication_account_id' => $authenticationAccount->getAuthenticationAccountId()));
			if ($internal) {
				$this->exchangeArray($internal->toArray());
				return true;
			}
		}
		return false;
	}
    
    /**
    * Create a random password string.
    * @param int $length Number character of password
    * @return string
    */
    public function genaratePassword($length = 10)
    {
        $passwordGenerator = new Password();
        return $passwordGenerator->generatePassword($length);
    }
}
