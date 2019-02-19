<?php

namespace HtAuthentication\Model;

use AppCore\Mvc\Model\Collection;
use AppCore\Mvc\Model\SoftDeleteableInterface;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use AppCore\Mvc\Model\TableBase;
use HtUser\Model\User;

class UserOrganisationCollection extends Collection implements SoftDeleteableInterface
{
	public function getOrgUsers($where = null, $offset = null, $limit = null, $order = null)
	{
		$table = $this->getTableName();
		
		$sm = $this->getServiceManager();
		
		/* @var $user \HtUser\Model\User */
		$user = $sm->get('User');
		
		$userTable = $user->getTableName();
		
		if (!$order) {
			$order = "{$table}.user_organisation_id DESC";
		}
		
		return $this->getTableGateway()->select(function (Select $select) use ($where, $offset, $limit, $order, $table, $userTable) {
			$select->join($userTable, "{$table}.user_id = {$userTable}.user_id");
			$select->where($where);
			if ($limit) {
				$select->limit($limit);
			}
			if ($offset) {
				$select->offset($offset);
			}
			if ($order) {
				$select->order($order);
			}
		});
	}
	
	/**
	 * count number user of organisation
	 * @param array $organisationIds
	 * @return type
	 */
	public function countUser($organisationIds = array())
	{
		return $this->getTableGateway()->select(function (Select $select) use ($organisationIds) {
			$select->columns(array(
				'organisation_id' => 'organisation_id',
				'count_apps' => new Expression('COUNT(user_id)')
			));
			$select->where->in('organisation_id', $organisationIds);
			$select->group('organisation_id');
		});
	}
	
	public function buildGetUserForAjaxSelect(Select $select, $organisationId, $search)
	{
		$table = $this->getTableName();
		
		$sm = $this->getServiceManager();
		/* @var $user \HtUser\Model\User */
		$user = $sm->get('User');
		$userTable = $user->getTableName();
		
		$select->join($table, "{$table}.user_id = {$userTable}.user_id",array() , 'left');
		$select->where
			->nest()
				->like('firstname', "%{$search}%")->or->like('lastname', "%{$search}%")->or->like('email', "%{$search}%")
			->unnest();

		$select->where
			->nest()
			->notEqualTo("{$userTable}.is_deleted", TableBase::STATUS_DELETED)
			->or->isNull("{$userTable}.is_deleted")
			->unnest();

		$select->where
			->nest()
				->nest()
					//include already join but deleted
					->equalTo('organisation_id', $organisationId)->and->equalTo("{$table}.is_deleted", TableBase::STATUS_DELETED)
				->unnest()
				//include user hasn't join any org
				->or->isNull('organisation_id')//because of left join
				//exclude user already join org
				->or->notEqualTo('organisation_id', $organisationId)
			->unnest();
		$select->group("{$userTable}.user_id");
	}
	
	/**
	 * Get all users substract users already member of organisation has organisationId.
	 * @param int $organisationId OrgnisationId of organistion want filter.
	 */
	public function getUsersForAjax($organisationId, $search, $offset, $limit)
	{
		$sm = $this->getServiceManager();
		/* @var $user \HtUser\Model\User */
		$user = $sm->get('User');

		$that = $this;
		return $user->getAll(function (Select $select) use ($that, $organisationId, $search) {
			$that->buildGetUserForAjaxSelect($select, $organisationId, $search);
		}, $offset, $limit);
	}
	
	/**
	 * count number users substract users already member of organisation has organisationId.
	 * @param int $organisationId OrgnisationId of organistion want filter.
	 */
	public function countForAjax($organisationId, $search)
	{
		$sm = $this->getServiceManager();
		/* @var $user \HtUser\Model\User */
		$user = $sm->get('User');
		
		$that = $this;
		//check query explanation of getUsersForAjax
		return $user->count(function (Select $select) use ($that, $organisationId, $search) {
			$that->buildGetUserForAjaxSelect($select, $organisationId, $search);
		});
	}
	
	/**
	 * Get organisation for an user with userId
	 * @param int $userId
	 */
	public function getOrganisationForAjax($userId)
	{
		$table = $this->getTableName();
		
		$sm = $this->getServiceManager();
		
		/* @var $organisation Organisation */
		$organisation = $sm->get('Org');
		
		$orgTable = $organisation->getTableName();
		
		return $organisation->getAll(function (Select $select) use ($orgTable, $table, $userId) {
			$select->join($table, "{$orgTable}.organisation_id = {$table}.organisation_id", array());
			$select->where(array('user_id' => $userId));
		});
		
	}
	/**
	 * Count number organisation that user is member.
	 * @param array $userIds
	 */
	public function countOrganisation($userIds)
	{
		return $this->getTableGateway()->select(function (Select $select) use ($userIds) {
			$select->columns(array(
				'user_id' => 'user_id',
				'count_org' => new Expression('COUNT(DISTINCT organisation_id)')
			));
			$select->where->in('user_id', $userIds);
			$select->group('user_id');
		});
	}
	
	public function getUserOrgs($user, $limit = null, $offset = null, $order = null)
	{
		if ($user instanceof User) {
			$userId = $user->getUserId();
		} else {
			$userId = (int) $user;
		}
		
		/* @var $orgTableName Organisation */
		$orgTableName = $this->getServiceManager()->get('Org')->getTableName();
		$tableName = $this->getTableName();
		return $this->getTableGateway()->select(function (Select $select ) use ($userId, $limit, $offset, $order, $orgTableName, $tableName) {
			$select->join($orgTableName, "{$orgTableName}.organisation_id = {$tableName}.organisation_id", 'title');
			$select->where(array('user_id' => $userId));
			
			if ($limit) {
				$select->limit($limit);
			}
			
			if ($offset) {
				$select->offset($offset);
			}
			
			if ($order) {
				$select->order($order);
			}
		});
	}
}
