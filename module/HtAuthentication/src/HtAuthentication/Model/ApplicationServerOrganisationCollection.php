<?php
namespace HtAuthentication\Model;

use AppCore\Mvc\Model\Collection;
use AppCore\Mvc\Model\SoftDeleteableInterface;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use AppCore\Mvc\Model\TableBase;

class ApplicationServerOrganisationCollection extends Collection implements SoftDeleteableInterface
{
	/**
	 * 
	 * @param int $orgId
	 * @param int $offset
	 * @param int $limit
	 * @return \Zend\Db\ResultSet\ResultSet
	 */
	public function getByOrganisationId($orgId, $offset = 0, $limit = 10)
	{
		$sm = $this->getServiceManager();
		$table = $this->getTableName();
		
		/* @var $application \HtAuthentication\Model\ApplicationServerOrganisation */
		$application = $sm->get('App');
		
		/* @var $server \HtAuthentication\Model\Server */
		$server = $sm->get('Server');
		
		$serverTable = $server->getTableName();
		
		$appTable = $application->getTableName();
		
		return $this->getTableGateway()->select(function (Select $select) use ($orgId, $offset, $limit, $table, $appTable, $serverTable) {
			$select->join($appTable, "{$table}.application_id = {$appTable}.application_id", array('app_title' => "title"))
					->join($serverTable, "{$table}.server_id = {$serverTable}.server_id", array('server_title' => "title"));
					
			$select->where(array('organisation_id' => $orgId))
					->limit($limit)
					->offset($offset);
		});
	}
	
	public function getByApplicationId($appId, $offset = 0, $limit = 10)
	{
		$sm = $this->getServiceManager();
		$table = $this->getTableName();
		
		/* @var $organisation Organisation */
		$organisation = $sm->get('Org');
		$orgTable = $organisation->getTableName();
		
		/* @var $serverTable Server */
		$server = $sm->get('Server');
		$serverTable = $server->getTableName();
		
		return $this->getTableGateway()->select(function (Select $select) use ($appId, $offset, $limit, $table, $serverTable, $orgTable) {
		
			$select->join($serverTable, "{$table}.server_id = {$serverTable}.server_id", array('server_domain' => 'domain'))
					->join($orgTable, "{$table}.organisation_id = {$orgTable}.organisation_id");
			
			$select->where(array('application_id' => $appId))
					->limit($limit)
					->offset($offset);
		});
	}
    
    public function getOrgByServerId($serverId, $offset = null, $limit = null)
    {
        $sm = $this->getServiceManager();
        $table = $this->getTableName();
        
        /* @var $org Organisation */
        $org = $sm->get('Org');
        
        $orgTable = $org->getTableName();
        
        $where = function (Select $select) use ($serverId, $table, $orgTable, $offset, $limit) {
            $select->join($orgTable, "{$table}.organisation_id = {$orgTable}.organisation_id");
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
	 * count number application of an oganisation.
	 * @param array $organisationIds
	 * @return \Zend\Db\ResultSet\ResultSet
	 */
	public function countApplication($organisationIds = array())
	{
		return $this->getTableGateway()->select(function (Select $select) use ($organisationIds) {
			$select->columns(array(
				'organisation_id' => 'organisation_id',
				'count_apps' => new Expression('COUNT( DISTINCT application_id)')
			));
			$select->where->in('organisation_id', $organisationIds);
			$select->group('organisation_id');
		});
	}
	
	/**
	 * Count number organisation.
	 * @param \Zend\Db\Sql\Where | array | \Closure
	 * @return \Zend\Db\ResultSet\ResultSet
	 */
	public function countOrg($where = array(), $group = null)
	{
		return $this->getTableGateway()->select(function (Select $select) use ($where, $group) {
			$select->columns(array(
				'application_id' => 'application_id',
                'server_id' => 'server_id',
				'count_orgs' => new Expression('COUNT( DISTINCT organisation_id)')
			));
            $select->where($where);
            if ($group) {
                $select->group($group);
            }
		});
	}
	
	/**
	 * get list server.
	 * @param array  $where
	 * @return \Zend\Db\ResultSet\ResultSet
	 */
	public function getServerOrg($where)
	{
		$sm = $this->getServiceManager();
		
		/* @var $server Server */
		$server = $sm->get('Server');
		
		$serverTable = $server->getTableName();
		
		$table = $this->getTableName();
		
		return $server->getAll(function (Select $select) use ($where, $table, $serverTable) {
			$select->join($table, "{$table}.server_id = {$serverTable}.server_id");
			$select->where
					->nest()
						->notEqualTo("{$table}.is_deleted", TableBase::STATUS_DELETED)
						->or->isNull("{$table}.is_deleted")
					->unnest();
			$select->where($where);
		});
	}
	
}
