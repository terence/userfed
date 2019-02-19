<?php
/**
 * Base class for model which base on table.
 * Also have service manager to call other class.
 */
namespace AppCore\Mvc\Model;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql;
use Zend\Db\TableGateway\TableGateway as ZendTableGateway;
use Zend\Db\Sql\Predicate;
use AppCore\Db\TableGateway\TableGateway;
use AppCore\Db\TableGateway\AbstractTableGateway;
use AppCore\Mvc\Model\SoftDeleteableInterface;

class TableBase
{
	protected $serviceManager;
	protected $table;
	
	protected $softDeleteableColumn = 'is_deleted';
	const STATUS_DELETED = 1;
	const STATUS_NONE_DELETED = 0;
	
	/**
	 * Service manager will set table for this object
	 * @return \AppCore\Db\TableGateway\AbstractTableGateway
	 */
	public function getTable()
	{
		return $this->table;
	}
	
	public function setTable($table)
	{
		if (!$table instanceof AbstractTableGateway) {
			throw new \Exception('$table must instantce of AppCore\Db\TableGateway\AbstractTableGateway');
		}
		$this->table = $table;
	}

	/**
	 * @return \Zend\ServiceManager\ServiceManager
	 */
	public function getServiceManager()
	{
		return $this->serviceManager;
	}
	
	/**
	 * @param \Zend\ServiceManager\ServiceManager $value
	 */
	public function setServiceManager($value)
	{
		$this->serviceManager = $value;
	}	
	
	
	protected function getTableName()
	{
		return $this->getTable()->table;
	}
		
	/**
	 * Allow to set a pre-built select
	 * @var \Zend\Db\Sql\Select
	 */
	protected $selectObject;
	
	/**
	 * Allow to set a pre-built select, so we can reuse the select rule between methods
	 * @param \Zend\Db\Sql\Select $selectObject
	 */
	protected function setSelectObject(Sql\Select $selectObject)
	{
		$this->selectObject = $selectObject;
	}
	
	/**
	 * @return \Zend\Db\Sql\Select
	 */
	protected function getSelectObject()
	{
		//if no prebuilt select then always return a new one
		if (!isset($this->selectObject)) {
			return new Sql\Select();
		} else {
			return $this->selectObject;
		}
	}
	
	protected function getDbAdapter()
	{
		return $this->getTable()->getAdapter();
	}	
	
	/**
	 * Apply filter to remove soft-deleted intances
	 * 
	 * @param Where $where
	 */
	protected function filterSoftDeleted($where)
	{
		if ($this instanceof SoftDeleteableInterface) {
			if ($this->getSoftDeleteable()) {
				$where
					->nest()
						->notEqualTo($this->getTableName() . '.' . $this->softDeleteableColumn, self::STATUS_DELETED)
						->OR
						->isNull($this->getTableName() . '.' . $this->softDeleteableColumn)
					->unnest();
			}
		}		
	}
	
	/**
	 * Get all items which match where criteria
	 *
	 * @param Where|\Closure|string|array $where
	 * @param int $offset
	 * @param int $limit
	 * @param string $order
	 * @return ResultSet
	 */
	public function getAll($where = null, $offset = null, $limit = null, $order = null)
	{
		$select = $this->getSelectObject();
		$select->from($this->getTableName());
		if (isset($offset)) {
			$select->offset($offset);
		}
		if (isset($limit)) {
			$select->limit($limit);
		}
        
        if (!$order) {
            $order = $this->getTableName() . '.last_updated DESC';
        }
        
        $select->order($order);
		
		$this->filterSoftDeleted($select->where);
	
		if ($where instanceof \Closure) {
			$where($select);
		} elseif ($where !== null) {
			$select->where($where);
		}
	
		return $this->getTable()->selectWith($select);
	}
	
	/**
	 * Fetch only one record
	 * @param Where|\Closure|string|array $where
	 * @return \AppCore\Mvc\Model\Model | null
	 */
	public function getOne($where)
	{
		$select = $this->getSelectObject();
		$select->from($this->getTableName());
		
		$this->filterSoftDeleted($select->where);
		
		if ($where instanceof \Closure) {
			$where($select);
		} elseif ($where !== null) {
			$select->where($where);
		}
	
		$resultSet = $this->getTable()->selectWith($select);
		if ($resultSet->count() == 1) {
			/* @var $result \AppCore\Mvc\Model\Model */
			$result = $resultSet->current();
			$result->setTable($this->getTable());
			$result->setServiceManager($this->getServiceManager());
			return $result;
		} else {
			return null;
		}
	}

	/**
	 * Count items which match criteria
	 * 
	 * @param Where|\Closure|string|array $where
	 * @return int
	 */
	public function count($where = null)
	{
		// select count(*)
		$tableName = $this->getTableName();
		$sql = new Sql\Sql($this->getDbAdapter());
		$select = $sql->select();
		$select->from($tableName)
			->columns(array('count' => new Sql\Expression('COUNT(*)')));
		
		
		if ($where instanceof \Closure) {
			$where($select);
		} elseif (isset($where)) {
			$select->where($where);
		}
		
		$this->filterSoftDeleted($select->where);
		
		$statement = $sql->prepareStatementForSqlObject($select);
		$results = $statement->execute();
		$row = $results->current();
		return $row['count'];
	}
	
	/**
	 * Create TableGateway instance.
	 * If the calling object support softdelete then 
	 * @param string $tableName
	 * @return \Zend\Db\TableGateway\TableGateway
	 */
	protected function getTableGateway($tableName = "", $supportSoftDelete = true)
	{
		//if create TableGateway which use the same table as current model
		//TableGateway will inherit softDelete support from the model
		if (empty($tableName)) {
			if ($this instanceof SoftDeleteableInterface) {
				$supportSoftDelete = true;
			} else {
				$supportSoftDelete = false;
			}
		}
		
		if (!$tableName) {
			$tableName = $this->getTableName();
		}
		
		if ($supportSoftDelete) {
			$tableGateway =  new TableGateway($tableName, $this->getDbAdapter());
			
			//enable/disable soft delete based on model status
			if ($this instanceof SoftDeleteableInterface) {
				if ($this->getSoftDeleteable()) {
					$tableGateway->enableSoftDelete();
				} else {
					$tableGateway->disableSoftDelete();
				}
			} 
			//enable soft delete by default
			else {
				$tableGateway->enableSoftDelete();
			}
		} else {
			$tableGateway =  new ZendTableGateway($tableName, $this->getDbAdapter());
		}
		
		return$tableGateway;
	}
		
	/**
	 * Set service manager and load depedant object.
	 * We need to inject service manager here because the objects are created through TableGateway by
	 * cloning a Prototype object so no dependancy is injected yet.
	 *
	 * @param ResultSet $rowset
	 * @param array $loadDependant list of dependant object which also need to load
	 * @return array
	 */
	protected function prepare(ResultSet $rowset)
	{
		//set service manager
		$items = array();
		foreach ($rowset as $row) {
			$row->setServiceManager($this->getServiceManager());
			$items[] = $row;
		}
		return $items;
	}
}