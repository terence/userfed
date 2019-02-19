<?php

namespace HtAuthentication\Model;

use AppCore\Mvc\Model\Collection;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Predicate\Expression;
use AppCore\Mvc\Model\SoftDeleteableInterface;

class UserApplicationCollection extends Collection implements SoftDeleteableInterface
{

	/**
	 * Get list application used by user.
	 * @param int $userId
	 * @return \Zend\Db\ResultSet\ResultSet
	 */
	public function getUserApps($userId, $limit = null, $offset = null)
	{
		$sm = $this->getServiceManager();

		/* @var $application \HtAuthentication\Model\Application */
		$application = $sm->get('App');
		$appTable = $application->getTableName();

		$userAppTable = $this->getTableName();

		/* @var $server \HtAuthentication\Model\Server */
		$server = $sm->get('Server');
		$serverTable = $server->getTableName();
		
		$where = function (Select $select) use ($serverTable, $appTable, $userAppTable, $userId, $offset, $limit) {
			$select->join($serverTable, "{$serverTable}.server_id = {$userAppTable}.server_id", array('server_title' => 'title', 'domain' => 'domain'));
			$select->join($appTable, "{$appTable}.application_id = {$userAppTable}.application_id", array('app_title' => 'title'));
			$select->where(array("{$userAppTable}.user_id" => $userId));
			if ($offset) {
				$select->offset($offset);
			}
			if ($limit) {
				$select->limit($limit);
			}
		};
		
		$rowset = $this->getTableGateway()->select($where);

		return $rowset;
	}

	/**
	 * @SuppressWarnings(PHPMD)
	 */
	public function getAppUsers($where = null, $offset = null, $limit = null, $order = null)
	{
		$sm = $this->getServiceManager();
		$table = $this->getTableName();

		/* @var $user \HtUser\Model\User */
		$user = $sm->get('User');
		$userTable = $user->getTableName();

		/* @var $serverTable Server */
		$server = $sm->get('Server');
		$serverTable = $server->getTableName();

		/* @var $organisation Organisation */
		$organisation = $sm->get('Org');
		$orgTable = $organisation->getTableName();

		$where = function (Select $select) use ($where, $table, $orgTable, $userTable, $serverTable, $offset, $limit) {
			$select->join($userTable, "{$table}.user_id = {$userTable}.user_id", array('email', 'firstname', 'lastname'))
				->join($serverTable, "{$table}.server_id = {$serverTable}.server_id", array('server_title' => 'title'))
				->join($orgTable, "{$table}.organisation_id = {$orgTable}.organisation_id", array('org_title' => 'title'),
						//Organization is optional as there is user register to the site without organization info
						Select::JOIN_LEFT
				);
			$select->where($where);
				
			if ($offset) {
				$select->offset($offset);
			}
			if ($limit) {
				$select->limit($limit);
			}
		};
		
		return $this->getTableGateway()->select($where);
	}

	/**
	 * Count number user.
     * @param \Zend\Db\Sql\Where | array | \Closure $where
	 */
	public function countUser($where = null, $group = null)
	{
		return $this->getTableGateway()->select(function (Select $select) use ($where, $group) {
            $select->columns(array(
                'application_id' => 'application_id',
                'server_id' => 'server_id',
                'count_users' => new Expression('COUNT( DISTINCT user_id)')
            ));
            $select->where($where);
            if ($group) {
                $select->group($group);
            }
        });
	}
    
    /**
     * @param int $serverId
     * @param int $offset
     * @param int $limit
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function getUserServer($serverId, $offset = null, $limit = null)
    {
        
        $sm = $this->getServiceManager();
        
        /* @var $user \HtUser\Model\User */
		$user = $sm->get('User');
		$userTable = $user->getTableName();
        
        $table = $this->getTableName();
        
        $where = function (Select $select) use ($serverId, $userTable, $table, $offset, $limit) {
            $select->join($userTable, "{$userTable}.user_id = {$table}.user_id");
            $select->where(array('server_id' => $serverId));
            
            if ($offset) {
                $select->offset($offset);
            }
            
            if ($limit) {
                $select->limit($limit);
            }
        };
        
        return $this->getTableGateway()->select($where);
        
    }

	/**
	 * Count number application of user.
	 * @param array $userIds
	 */
	public function countApplication($userIds = array())
	{
		return $this->getTableGateway()->select(function (Select $select) use ($userIds) {
					$select->columns(array(
						'user_id' => 'user_id',
						'count_apps' => new Expression('COUNT(DISTINCT application_id)')
					));
					$select->where->in('user_id', $userIds);
					$select->group('user_id');
				});
	}

}
