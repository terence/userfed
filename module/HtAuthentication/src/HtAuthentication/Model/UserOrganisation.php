<?php
namespace HtAuthentication\Model;

use AppCore\Mvc\Model\Model;
use AppCore\Mvc\Model\SoftDeleteableInterface;
use HtAuthentication\Model\Organisation;
use HtUser\Model\User;
use Zend\Db\Sql\Where;

class UserOrganisation extends Model implements SoftDeleteableInterface
{
	protected $userOrganisationId;
	protected $organisationId;
	protected $userId;
	
	protected $mapping = array(
		array('dbColumn' => 'user_organisation_id', 'objectProperty' => 'userOrganisationId', 'isIdentifier' => true),
		array('dbColumn' => 'organisation_id', 'objectProperty' => 'organisationId'),
		array('dbColumn' => 'user_id', 'objectProperty' => 'userId'),
	);
	
	public function getUserOrganisationId()
	{
		return $this->userOrganisationId;
	}

	public function getOrganisationId()
	{
		return $this->organisationId;
	}

	public function getUserId()
	{
		return $this->userId;
	}

	public function setUserOrganisationId($userOrganisationId)
	{
		$this->userOrganisationId = $userOrganisationId;
		return $this;
	}

	public function setOrganisationId($organisationId)
	{
		$this->organisationId = $organisationId;
		return $this;
	}

	public function setUserId($userId)
	{
		$this->userId = $userId;
		return $this;
	}
	
	public function addToOrganisation($organisation)
	{
		if (!$this->getUserId()) {
			throw new \Exception('userId cannot be null.');
		}
		if ($organisation instanceof Organisation) {
			$organisationId = $organisation->getOrganisationId();
		} else {
			$organisationId = (int) $organisation;
		}
		
		$this->setOrganisationId($organisationId);
		$this->save();
	}
	
	/**
	 * Add an organisation to user.
	 * @param \HtAuthentication\Model\Organisation | null | int $org
	 * @param \HtUser\Model\User | null | int $user
	 */
	public function addOrgToUser($org = null, $user = null)
	{
		if ($user === null ) {
			$userId = $this->getUserId();
		} else if ($user instanceof  User) {
			$userId = $user->getUserId();
		} else {
			$userId = (int) $user;
		}
		
		if ($org === null) {
			$orgId = $this->getOrganisationId();
		} else if ($org instanceof Organisation) {
			$orgId = $org->getOrganisationId();
		} else {
			$orgId = (int) $org;
		}
		/* @var $userOrg self */
		$this->setUserId($userId)
			->setOrganisationId($orgId)
			->save();
	}
	
	public function isAdded($organisation, $user = null)
	{
		if ($user === null) {
			$userId = $this->getUserId();
		} elseif ($user instanceof User) {
			$userId = $user->getUserId();
		} else {
			$userId = (int) $user;
		}
		
		if ($organisation instanceof Organisation) {
			$organisationId = $organisation->getOrganisationId();
		} else {
			$organisationId = (int) $organisation;
		}
		
		$count = $this->count(array('user_id' => $userId, 'organisation_id' => $organisationId));
		if ($count > 0 ) {
			return true;
		}
		return false;
	}
	
	protected function loadSoftDeletedInstance($where = null)
	{
		$where = new Where();
		$where->equalTo('organisation_id', $this->getOrganisationId());
		$where->equalTo('user_id', $this->getUserId());
		return parent::loadSoftDeletedInstance($where);
	}
}
