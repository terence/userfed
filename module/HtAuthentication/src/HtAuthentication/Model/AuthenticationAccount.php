<?php
namespace HtAuthentication\Model;

use AppCore\Mvc\Model\Model as AppCoreModel;
use HtUser\Model\User;
use AppCore\Mvc\Model\SoftDeleteableInterface;
use Zend\Db\Sql\Where;
class AuthenticationAccount extends AppCoreModel implements SoftDeleteableInterface
{
	protected $authenticationAccountId;
	protected $userId;
	protected $type;

	const TYPE_INTERNAL = 'internal';
	const TYPE_OAUTH = 'oauth';
	
	protected $mapping = array(
		array('dbColumn' => 'authentication_account_id', 'objectProperty' => 'authenticationAccountId', 'isIdentifier' => true),
		array('dbColumn' => 'user_id', 'objectProperty' => 'userId'),
		array('dbColumn' => 'type', 'objectProperty' => 'type'),
	);
	
	public function getAuthenticationAccountId()
	{
		return $this->authenticationAccountId;
	}

	public function getUserId()
	{
		return $this->userId;
	}

	public function getType()
	{
		return $this->type;
	}

	public function setAuthenticationAccountId($authenticationAccountId)
	{
		$this->authenticationAccountId = $authenticationAccountId;
		return $this;
	}

	public function setUserId($userId)
	{
		$this->userId = $userId;
		return $this;
	}

	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}
	
	public function getInternalLogin($autoSetProperties = true, $user = null)
	{
		if ($user === null) {
			$userId = $this->getUserId();
		} elseif ($user instanceof User) {
			$userId = $user->getUserId();
		} else {
			$userId = (int) $user;
		}
		
		$internalAccount = $this->getOne(array('user_id' => $userId, 'type' => self::TYPE_INTERNAL));
		if (!$internalAccount) {
			return false;
		}
		
		if ($autoSetProperties) {
			$this->exchangeArray($internalAccount->toArray());
		}
		
		$sm = $this->getServiceManager();
		/* @var $internal Adapter\Internal */
		$internal = $sm->get('AuthAccountInternal');
		$internal = $internal->getOne(array('authentication_account_id' => $internalAccount->getAuthenticationAccountId()));
		return $internal;
	}
}
