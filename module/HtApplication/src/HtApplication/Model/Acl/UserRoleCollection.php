<?php

namespace HtApplication\Model\Acl;

use AppCore\Mvc\Model\Collection;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;

class UserRoleCollection extends Collection
{
    /**
     * Count role's user.
     * @param array $roleIds
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function countUser($roleIds = array())
    {
        return $this->getTableGateway()->select(function (Select $select) use ($roleIds) {
			$select->columns(array(
				'role_id' => 'role_id',
				'count_users' => new Expression('COUNT(user_id)')
			));
			$select->where->in('role_id', $roleIds);
			$select->group('role_id');
		});
    }
    
    /**
     * Build query filter user with keyword and doesn't belong to role_id
     * @param int | string $excludeRoleId
     * @param string $keyword
     * @return \Closure
     */
    public function buildQueryJoinWithUserTable($excludeRoleId, $keyword)
    {
        $table = $this->getTableName();
        
        $sm = $this->getServiceManager();
        /* @var $user \HtUser\Model\User */
        $user = $sm->get('User');
        $userTable = $user->getTableName();
        
        return function (Select $select) use ($excludeRoleId, $keyword, $table, $userTable) {
            $select->join($table, "{$userTable}.user_id = {$table}.user_id", array());
            $select->where
                    ->notEqualTo('role_id', $excludeRoleId);
            if ($keyword) {
                $select->where->nest()
                    ->like('email', "%{$keyword}%")
                    ->or
                    ->like('firstname', "%{$keyword}%")
                    ->or
                    ->like('lastname', "%{$keyword}%")
                ->unnest();
            }
        };
    }
}
