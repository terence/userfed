<?php

namespace HtAuthentication\Model;

use AppCore\Mvc\Model\Collection;
use AppCore\Mvc\Model\SoftDeleteableInterface;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Predicate\NotIn;

class ApplicationServerCollection extends Collection implements SoftDeleteableInterface
{
	/**
	 * return a application by server Secret.
	 * @param string $secret
	 * @return \HtAuthentication\Model\ApplicationServer
	 */
	public function getBySeverSecret($secret)
	{
		$sm = $this->getServiceManager();
		$serverTable = $sm->get('Server');
		$tableName = $this->getTableName();
		$serverTableName = $serverTable->getTableName();
		
		$result = $this->getOne(function (Select $select) use ($secret, $serverTableName, $tableName) {
			$select->join($serverTableName, "{$serverTableName}.server_id={$tableName}.server_id");
			$select->where(array("secret" => $secret));
		});
		/* @var $result \HtAuthentication\Model\ApplicationServer */
		if ($result) {
			return $result;
		}
		return null;
	}
	
	/**
	 * Return return string Application Name if value of $returnString is true
	 * @param array $ids
	 * @param boolean $returnString
	 * @return \Zend\Db\ResultSet\ResultSet | string
	 */
	public function getApplicationName($ids = array(), $returnString = false)
	{
	
		$sm = $this->getServiceManager();
		$application = $sm->get('App');
		/* @var $application Application */
		$appTable = $application->getTableName();
		$table = $this->getTableName();
		$results = $this->getTableGateway->select(function (Select $select) use ($ids, $appTable, $table) {
			$select->join($appTable, "{$table}.application_id = {$appTable}.application_id");
			$select->where(array("{$table}.application_id" => $ids));
		});
		
		if ($returnString) {
			return $results;
		} else {
			$appName = array();
			foreach ($results as $app) {
				$appName[] = $app->getTitle();
			}
			return implode(', ', $appName);
		}
	}
	
	/**
	 * Get list server of an application subtract server has server_id in $notIn variable
	 * @param int $applicationId
	 * @param array $notIn
	 * @return \Zend\Db\ResultSet\ResultSet
	 */
	public function getServerByApplicationId($applicationId, $notIn = array())
	{
		$sm = $this->getServiceManager();
		
		$table = $this->getTableName();
		
		/* @var $server Server */
		$server = $sm->get('Server');
		$serverTable = $server->getTableName();
		
		return $server->getAll(function (Select $select) use ($applicationId, $notIn, $table, $serverTable) {
			$select->join($table, "{$serverTable}.server_id = {$table}.server_id");
			$select->where(array('application_id' => $applicationId));
			
			if (!empty($notIn)) {
				$select->where(new NotIn("{$serverTable}.server_id", $notIn));
			}					
		});
	}
    
    /**
     * @param array | \Closure | Where | string $where
     * @param int $offset
     * @param int $limit
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function getAppServer($where = array(), $offset = null, $limit = null)
    {
        $sm = $this->getServiceManager();
        $table = $this->getTableName();
        
        /* @var $server Server */
        $server = $sm->get('Server');
        $serverTable = $server->getTableName();
        
        return $this->getTableGateway()->select(function (Select $select) use ($table, $serverTable, $where, $offset, $limit) {
            $select->join($serverTable, "{$serverTable}.server_id = {$table}.server_id");
            $select->where($where);
            if ($offset) {
                $select->offset($offset);
            }
            
            if ($limit) {
                $select->limit($limit);
            }
        });
    }
}
