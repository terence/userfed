<?php
namespace HtUser\Model;

use AppCore\Mvc\Model\Collection;
use AppCore\Mvc\Model\SoftDeleteableInterface;
use Zend\Db\Sql;

class UserCollection extends Collection implements SoftDeleteableInterface
{
	public function getAll($where = null, $offset = null, $limit = null, $order = null)
	{
		$sm = $this->getServiceManager();
		$table = $this->getTableName();
		
		$permissionTable = User::PERMISSION_USER_ROLE_TABLE;
		/* @var $authAccount \HtAuthentication\Model\AuthenticationAccount */
		$authAccount = $sm->get('AuthAccount');
		$authAccountTable = $authAccount->getTableName();
		$select = function(Sql\Select $select) use ($where, $order, $table, $permissionTable, $authAccountTable , $limit, $offset) {
			$select->join($authAccountTable, "{$table}.user_id = {$authAccountTable}.user_id", array(), Sql\Select::JOIN_INNER);
			$select->join($permissionTable, "{$table}.user_id = {$permissionTable}.user_id", array('role_id'));
			if (!$order) {
				$order = "{$table}.user_id DESC";
			}
			$select->columns(array('*','total_login' => new Sql\Expression('COUNT(`authentication_account_id`)')));
			$select->group("{$table}.user_id");
			$select->where($where)->order($order);
            if ($offset) {
                $select->offset(intval($offset));
            }
            
            if ($limit) {
                $select->limit(intval($limit));
            }
		};
		        
		$rowset = $this->getTableGateway()
				->select($select);
		
		return $rowset;
	}
	
	/**
	 * 
	 * @param string $status
	 * deleted, disabled, enabled
	 * 
	 * @param \Zend\Db\Sql\Where $where
	 * @return \Zend\Db\Sql\Where
	 * @throws \Exception
	 */
	public function getQueryByStatus($status, $where = null)
	{
		if ($where === null ) {
			$where = new Sql\Where();
		} else if(!$where instanceof Sql\Where) {
			throw new \Exception('$where must instaceof Zend\Db\Sql\Where');
		}
		
		if ($status == 'deleted') {
			$this->disableSoftDelete();
			$where->equalTo('user.is_deleted', true);
		} elseif ($status == 'disabled') {
			$where->equalTo('is_enabled', false);
		} elseif ($status == 'enabled') {
			$where->equalTo('is_enabled', true);
		}
		return $where;
	}
}