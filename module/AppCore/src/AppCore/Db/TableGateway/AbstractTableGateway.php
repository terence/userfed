<?php
namespace AppCore\Db\TableGateway;

use Zend\Db\TableGateway\AbstractTableGateway as ZendAbstractTableGateway;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql;
use Zend\Db\ResultSet;
use AppCore\Mvc\Model\TableBase;
class AbstractTableGateway extends ZendAbstractTableGateway
{
	/**
	 * Child class should define its own table name
	 * @var string
	 */
	protected $table = null;
	
	/**
	 * Get count of items which match $where
	 * 
	 * @param Where|\Closure|string|array $where
	 * @return int
	 */
	public function getAllCount($where, $like=array())
	{
		//need to create new table gateway as we don't want OrderItem but int
		$table = new TableGateway($this->table, $this->getAdapter());
		$select = $this->getSelectObject();
		$select
		->from($this->table)
		->columns(
			array(
				'count' => new Sql\Expression('COUNT(*)')
			)
		);
		
		if ($where instanceof \Closure) {
			$where($select);
		} elseif ($where !== null) {
			$select->where($where);
		}
		
		foreach($like as $key => $val)
			$select->where(new Sql\Predicate\Like($key,$val));
		
		$rowset = $table->selectWith($select);
		$row = $rowset->current();
		return $row['count'];
	}
	
	/**
	 * Fetch only one record
	 * @param Where|\Closure|string|array $where
	 * @return \AppCore\Mvc\Model\Model | null 
	 */
	public function getOne($where)
	{
		$select = $this->getSelectObject();
		$select->from($this->table);
		
		if ($where instanceof \Closure) {
			$where($select);
		} elseif ($where !== null) {
			$select->where($where);
		}
		
		$resultSet = $this->selectWith($select);
		if ($resultSet->count() == 1) {
			return $resultSet->current();
		} else {
			return null;
		}	
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
	public function setSelectObject(Sql\Select $selectObject)
	{
		$this->selectObject = $selectObject;
	}
	
	/**
	 * @return \Zend\Db\Sql\Select
	 */
	public function getSelectObject()
	{
		//if no prebuilt select then always return a new one
		if (!isset($this->selectObject)) {
			return new Sql\Select();
		} else {
			return $this->selectObject;
		}
	}
	
	public function unsetSelectObject()
	{
		unset($this->selectObject);
	}
}