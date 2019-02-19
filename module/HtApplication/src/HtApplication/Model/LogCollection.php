<?php

namespace HtApplication\Model;

use AppCore\Mvc\Model\Collection;
use Zend\Db\Sql\Select;

class LogCollection extends Collection
{
    /**
     * @param array | string | \Zend\Db\Where | \Closure $where
     * @param type $offset
     * @param type $limit
     * @param type $order
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function getLogWithUser($where = null, $offset = null, $limit = null, $order = null)
    {
        $sm = $this->getServiceManager();
        /* @var $userTable \HtUser\Model\User */
        $userTable = $sm->get('User')->getTableName();
        
        $table = $this->getTableName();
        
        return $this->getTableGateway()->select(function (Select $select) use ($userTable, $table, $where, $offset, $limit, $order) {
            $select->join($userTable, "{$table}.user_id = {$userTable}.user_id", Select::SQL_STAR, Select::JOIN_LEFT);
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
}
