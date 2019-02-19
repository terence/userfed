<?php

namespace HtUser\Model;

use AppCore\Mvc\Model\Model as AppCoreModel;
use HtAuthentication\Model\AuthenticationAccount;
use AppCore\Mvc\Model\SoftDeleteableInterface;
use HtApplication\Model\Acl\Role;

class User extends AppCoreModel implements SoftDeleteableInterface
{

	protected $userId;
	protected $firstName;
	protected $lastName;
	protected $email;
	protected $isEnabled;
	protected $isDeleted;

	const PERMISSION_USER_ROLE_TABLE = 'permission_user_role';

	public function getIsDeleted()
	{
		return $this->isDeleted;
	}

	public function setIsDeleted($isDeleted)
	{
		$this->isDeleted = $isDeleted;
		return $this;
	}

	public function getFirstName()
	{
		return $this->firstName;
	}

	public function setFirstName($value)
	{
		$this->firstName = $value;
	}

	public function getLastName()
	{
		return $this->lastName;
	}

	public function setLastName($value)
	{
		$this->lastName = $value;
	}

	public function getUserId()
	{
		return $this->userId;
	}

	public function setUserId($userId)
	{
		$this->userId = $userId;
		return $this;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}

	public function getIsEnabled()
	{
		return $this->isEnabled;
	}

	public function setIsEnabled($isEnabled)
	{
		$this->isEnabled = $isEnabled;
		return $this;
	}
	
	public function getOAuthInfo()
	{
		$oAuth = $this->getServiceManager()->get('AuthAccount\OAuth');
		/* @var $oAuth \HtAuthentication\Model\Adapter\OAuth */
		$info = $oAuth->getOne(array('user_id' => $this->getUserId()));
		if (!$info) {
			return false;
		}
		return $info;
	}
	
	public function getTotalLogin($userId = null)
	{
		if ($userId === null) {
			$userId = $this->getUserId();
		}
		$authAccount = $this->getServiceManager()->get('AuthAccount');
		/* @var $authAccount \HtAuthentication\Model\AuthenticationAccount */
		$result = $authAccount->count(array('user_id' => $userId));
		return $result;
	}
	
	protected $inputFilter;
	protected $mapping = array(
		array('dbColumn' => 'user_id', 'objectProperty' => 'userId', 'isIdentifier' => true),
		array('dbColumn' => 'firstname', 'objectProperty' => 'firstName'),
		array('dbColumn' => 'lastname', 'objectProperty' => 'lastName'),
		array('dbColumn' => 'email', 'objectProperty' => 'email'),
		array('dbColumn' => 'is_enabled', 'objectProperty' => 'isEnabled'),
		array('dbColumn' => 'is_deleted', 'objectProperty' => 'isDeleted'),
	);
	protected $oauthProfileMapping = array(
		'facebook' => array(
			'username' => 'id',
			'email' => 'email',
			'firstName' => 'first_name',
			'lastName' => 'last_name'
		),
		'google' => array(
			'username' => 'id',
			'email' => 'email',
			'firstName' => 'given_name',
			'lastName' => 'family_name',
		)
	);

	/**
	 * Exchange users info provided by OAuth provider to User object
	 * @param Object $userInfo
	 * @param String $provider
	 * @throws Exception
	 */
	public function exchangeProperties($userInfo = null, $provider = '')
	{
		if(!is_object($userInfo))
			throw new \Exception('UserInfo provided is not a object');
		if(!isset($this->oauthProfileMapping[$provider]))
			throw new \Exception('Provider is not registed');
		foreach($this->oauthProfileMapping[$provider] as $property => $userInfoProperty) {
			if(isset($userInfo->{$userInfoProperty})) {
				$this->{$property} = $userInfo->{$userInfoProperty};
			}
		}
		return $this;
	}
	
	/**
	 * get user role of this user.
	 * @param type $userId
	 * @return null
	 */
	public function getRole($userId = null)
	{
		if ($userId) {
			$userId = (int) $userId;
		} else {
			$userId = $this->getUserId();
		}
		$rowset = $this->getTableGateway(User::PERMISSION_USER_ROLE_TABLE, false)->select(
				array('user_id' => $userId)
				);
        
        $roles = array();
        foreach ($rowset as $role) {
            $roles[] = $role->role_id;
        }
		return $roles;
	}
	
	/**
	 * Add role for user.
	 * @param array | string $roles
	 * @return \HtUser\Model\User
	 */
	public function addRole($roles)
	{
		$sm = $this->getServiceManager();
		$role = $sm->get("HtApplication\Model\Acl\UserRole");
		/* @var $role \HtApplication\Model\Acl\UserRole */
		if (!is_array($roles)) {
			$roles = (array) $roles;
		}
		if (!$this->userId) {
			throw new \Exception('User id cannot empty.');
		}
		$role->addRoles($this->userId, $roles);
		return $this;
	}
	
	/**
	 * Register account with member role, disable user if required activation
	 * @param type $requiredActivate
	 */
	public function registerMemberAccount($requiredActivate = true)
	{
		if (!$requiredActivate) {
			$this->setIsEnabled(true);
		} else {
			$this->setIsEnabled(false);
		}
		$this->save();
		$this->addRole(Role::MEMBER_ROLE);
		return $this;
	}
	
	public function loadByEmail($email)
	{
		$user = $this->getOne(array('email' => $email));
		if ($user) {
			$this->exchangeArray($user->toArray());
			return true;
		}
		return false;
	}
    
    public function getName()
    {
        $name = trim($this->getFirstName() . ' ' . $this->getLastName());
        if (!$name) {
            $name = $this->getEmail();
        }
        return $name;
    }
	
	public function hardDelete()
	{
		parent::hardDelete();
		
		$userId = $this->getUserId();
		
		$sm = $this->getServiceManager();
		/* @var $role \HtApplication\Model\Acl\UserRole */
		$role = $sm->get('HtApplication\Model\Acl\UserRole');
		$role->getTable()->delete(array('user_id' => $userId));
		/* Delete role of user */

		/* @var $authAccount \HtAuthentication\Model\AuthenticationAccount */
		$authAccount = $sm->get('AuthAccount');
		$authAccount->disableSoftDelete();
		$internalLogins = $authAccount->getAll(array('user_id' => $userId, 'type' => AuthenticationAccount::TYPE_INTERNAL));
		$oauthLogins = $authAccount->getAll(array('user_id' => $userId, 'type' => AuthenticationAccount::TYPE_OAUTH));
		
		$internalLoginIds = array();
		foreach ($internalLogins as $login) {
			/* @var $login \HtAuthentication\Model\Adapter\Internal */
			$login->setTable($authAccount->getTable());
			$internalLoginIds[] = $login->getAuthenticationAccountId();
			$login->hardDelete();
		}

		$oauthLoginIds = array();
		foreach ($oauthLogins as $login) {
			$login->setTable($authAccount->getTable());
			$oauthLoginIds[] = $login->getAuthenticationAccountId();
			$login->hardDelete();
		}
		
		if ($internalLoginIds) {
			/* @var $internal \HtAuthentication\Model\Adapter\Internal */
			$internal = $sm->get('AuthAccount\Internal');
			$internalLogins = $internal->getAll(array('authentication_account_id' => $internalLoginIds));
			foreach ($internalLogins as $login) {
				$login->setTable($internal->getTable());
				$login->hardDelete();
			}
		}

		if ($oauthLoginIds) {
			/* @var $oAuth \HtAuthentication\Model\Adapter\OAuth */
			$oAuth = $sm->get('AuthAccount\OAuth');
			$oauthLogins = $oAuth->getAll(array('authentication_account_id' => $oauthLoginIds));
			foreach ($oauthLogins as $login) {
				$login->setTable($oAuth->getTable());
				$login->hardDelete();
			}
			/* Delete OAuth info of user */
		}
		
		/* Delete all login */
		/* @var $log \HtApplication\Model\Log */
		$log = $sm->get('HtApplication\Model\Log');
		$log->disableSoftDelete();
		$userLogs = $log->getAll(array('user_id' => $userId));
		foreach ($userLogs as $userLog) {
			$userLog->setTable($log->getTable());
			$userLog->hardDelete();
		}
		/* Delete all log of user */

		/* @var $userApp \HtAuthentication\Model\UserApplication */
		$userApp = $sm->get('UserApp');
		$userApp->disableSoftDelete();
		$userApps = $userApp->getAll(array('user_id' => $userId));
		
		foreach ($userApps as $app) {
			$app->setTable($userApp->getTable());
			$app->hardDelete();
		}
		/* Delete all application cant access of this user.*/

		/**
		 * Delete user organisation
		 */
		/* @var $userOrg \HtAuthentication\Model\UserOrganisation */
		$userOrg = $sm->get('UserOrg');
		$userOrg->disableSoftDelete();
		$userOrgs = $userOrg->getAll(array('user_id' => $userId));
		
		foreach ($userOrgs as $org) {
			$org->setTable($userOrg->getTable());
			$org->hardDelete();
		}
	}
    
    /**
     * @todo Should merge with isAdmin() method in AbstractActionController
     */
    public function isAdmin()
    {
        $userRole = $this->getServiceManager()->get('HtApplication\Model\Acl\UserRole');
        /* @var $userRole \HtApplication\Model\Acl\UserRole */
        $rowset = $userRole->getRoles($this->getUserId());
        foreach ($rowset as $row) {
            if ($row == Role::ADMIN_ROLE) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Generate new password for user.
     * Only support internal login in this time
     * @return string New password before encryption
     */
    public function generatePassword()
    {
        $sm = $this->getServiceManager();
        /* @var $internal \HtAuthentication\Model\Adapter\Internal */
        $authAccount = $sm->get('AuthAccount')->getOne(
            array('user_id' => $this->getUserId(), 'type' => AuthenticationAccount::TYPE_INTERNAL)
        );
        
        if (!$authAccount) {
            throw new \Exception('Not found internal account.');
        }
        
        /* @var $internal \HtAuthentication\Model\Adapter\Internal */
        $internal = $sm->get('AuthAccount\Internal');
        $internalLogin = $internal->getOne(array('authentication_account_id' => $authAccount->getAuthenticationAccountId()));
        $password = $internalLogin->genaratePassword();
        $internalLogin->setPassword($internalLogin->createHashPassword($password));
        $internalLogin->save();
        return $password;
    }
}