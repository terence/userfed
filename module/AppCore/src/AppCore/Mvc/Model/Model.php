<?php
/**
 * Provide simple ORM functionality.
 * Provide premise for SoftDeleteableInterface.
 * 
 * @todo check if we can separate SoftDeleteable logic in another class as plugin.
 */
namespace AppCore\Mvc\Model;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use AppCore\Exception;
use AppCore\Mvc\Model\TableBase;

use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\Select;

class Model extends TableBase
{
	/*
	 * Mapping of table field name, object property, translation, id field
	 */
	protected $mapping = array(
// 			array('dbColumn' => 'company_id', 'objectProperty' => 'companyId', 'translate' => true, 'isIdentifier' => true)
	);
	
	protected $softDeleteable = true;
	protected $updatedTimeColumn = 'last_updated';
	protected $createdTimeColumn = 'creation_date';
	
	public function getSoftDeleteable()
	{
		return $this->softDeleteable;
	}
	
    public function enableSoftDelete()
    {
        $this->softDeleteable = true;
        return $this;
    }

    public function disableSoftDelete()
    {
        $this->softDeleteable = false;
        return $this;
    }

    //having an explicit hardDelete method
    public function hardDelete()
    {
        $this->disableSoftDelete();
        $this->delete();
    }
	
	public function getArrayCopy()
	{
		return get_object_vars($this);
	}
	
	/**
	 * Fill object with array data
	 * @param array $data
	 * @param array $exchangeProperties only properties in the list are exchanged, if empty all properties will be exchange 
	 */
	public function exchangeArray($data, $exchangeProperties = array())
	{
		$reflect = new \ReflectionObject($this);
		$publicProperties = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC);
		
		foreach ($this->mapping as $element) {
			//only exchange column which exist in data 
			if (!array_key_exists($element['dbColumn'], $data)) {
				continue;
			}
			
			$propertyValue = (isset($data[$element['dbColumn']]) ? $data[$element['dbColumn']] : null);
			
			//not in exchange properties list
			if (!empty($exchangeProperties)) {
				if (!in_array($element['objectProperty'], $exchangeProperties)) {
					continue;
				}
			}
			
			//check if setPropertyName exist
			$setMethodName = "set" . ucfirst($element['objectProperty']);
			if (method_exists($this, $setMethodName)) {
				$this->$setMethodName($propertyValue);
			}
			
			//check if property is public
			$isPublicProperty = false;
			foreach ($publicProperties as $prop) {
				if ($prop->getName() == $element['objectProperty']) {
					$isPublicProperty = true;
					break;
				}
			}
			if ($isPublicProperty) {
				$this->$element['objectProperty'] = $propertyValue;
			}
		}
		return $this;
	}
	
	/**
	 * convert to array of data
	 *
	 * @param string $keyType can be columnName or propertyName
	 * @return array
	 */
	
	protected function toArrayByKeyType($keyType = 'columnName')
	{
		$reflect = new \ReflectionObject($this);
		$publicProperties = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC);
		
		$arr = array();
		foreach ($this->mapping as $element) {
			$value = null;
			//check if getPropertyName exist
			$getMethodName = "get" . ucfirst($element['objectProperty']);
			if (method_exists($this, $getMethodName)) {
				$value = $this->$getMethodName();
			}
			
			//check if property is public
			$isPublicProperty = false;
			foreach ($publicProperties as $prop) {
				if ($prop->getName() == $element['objectProperty']) {
					$isPublicProperty = true;
					break;
				}
			}
			if ($isPublicProperty) {
				$value = $this->$element['objectProperty'];
			}
						
			if ($keyType == 'columnName') {
				$arr[$element['dbColumn']] = $value;
			} else if ($keyType == 'propertyName') {
				$arr[$element['objectProperty']] = $value;
			}
			
		}
		return $arr;
	}
	
	/**
	 * 
	 * @param string $keyType
	 * @return array
	 */
	public function toArray($keyType = 'columnName')
	{
		return $this->toArrayByKeyType($keyType);
	}
	
	protected function getIdentifierFieldName()
	{
		$foundIdentifier = false;
		foreach ($this->mapping as $field) {
			if (array_key_exists('isIdentifier', $field) && $field['isIdentifier']) {
				$foundIdentifier = true;
				return $field['dbColumn'];
			}
		}
		if (!$foundIdentifier) {
			throw new \Exception("Can not find isIdentifier field in mapping config.");
		}
	}
	
	protected function getIdentifierObjectProperty()
	{
		$foundIdentifier = false;
		foreach ($this->mapping as $field) {
			if (array_key_exists('isIdentifier', $field) && $field['isIdentifier']) {
				$foundIdentifier = true;
				return $field['objectProperty'];
			}
		}
		if (!$foundIdentifier) {
			throw new \Exception("Can not find isIdentifier field in mapping config.");
		}
	}
	
	/**
	 * get an item by id
	 *
	 * @param int $id 
	 * @return null|object null or object
	 */
	public function get($id)
	{		
		$tableName = $this->getTableName();
		$identifier = $this->getIdentifierFieldName();
		
		$where = new Where();
		$where->equalTo("$tableName.$identifier" , $id);
		
		$this->filterSoftDeleted($where);
		
		/* @var $rowset \Zend\Db\ResultSet\ResultSet */
		$rowset = $this->getTable()->select($where);
		
		$row = $rowset->current();
		
		if (!$row) {
			return null;
		}
		//get fetch by id also set fetched object state to the current object state
		$this->exchangeArray($row->toArray());
		
		/* @var $row Model */
		$row->setServiceManager($this->getServiceManager());
		$row->setTable($this->getTable());
		
		return $row;
	}

	/**
	 * Alias of get.
	 * For clear meaning of init state of an object
	 * @param mixed $id
	 * @return boolean
	 */
	public function load($id)
	{
		$result = $this->get($id);
		if ($result === null) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Save object to database
	 * 
	 * @return int object id
	 */
	public function save()
	{
		//restore the soft-deleted version
		if ($this instanceof SoftDeleteableInterface) {
			if ($this->getSoftDeleteable()) {
				$softDeletedInstance = $this->loadSoftDeletedInstance();
				if ($softDeletedInstance) {
					$softDeletedInstance->restore();
					$getMethodName = "get" . ucfirst($this->getIdentifierObjectProperty());
					$setMethodName = "set" . ucfirst($this->getIdentifierObjectProperty());
					if (method_exists($this, $setMethodName) && method_exists($softDeletedInstance, $getMethodName)) {
						$this->$setMethodName($softDeletedInstance->$getMethodName());
						return $softDeletedInstance->$getMethodName();
					}
					//stop here as we already restore
					return null;
				}
			}
		}
		
		//create or update 
		$data = $this->toArray();
	
		$identifierFieldName = $this->getIdentifierFieldName();
		
		$now = date('Y-m-d H:i:s');
		if (array_key_exists($identifierFieldName, $data) && !empty($data[$identifierFieldName])) {
			$id = $data[$identifierFieldName];
			//check if object exist then update
			$foundItem = $this->getOne(array($identifierFieldName => $id));
			if ($foundItem !== null) {
				$this->getTable()->update(
					array_merge(array($this->updatedTimeColumn => $now),$data), 
					array($identifierFieldName => $id)
				);
				return $id;
			} else {
				//not exist then insert with id (not use auto increment id)
				$this->getTable()->insert(
					array_merge(array(
							$this->createdTimeColumn => $now,
							$this->updatedTimeColumn => $now
						),
						$data
					)
				);
				return $id;
			}
		} else {
			$this->getTable()->insert(
				array_merge(
					array(
						$this->createdTimeColumn => $now,
						$this->updatedTimeColumn => $now
					),
					$data
				)
			);
			$id = $this->getTable()->getLastInsertValue();
			
			$setMethodName = "set" . ucfirst($this->getIdentifierObjectProperty());
			
			if (method_exists($this, $setMethodName)) {
				$this->$setMethodName($id);
			}
			return $id;
		}
	}
	
	/**
	 * Delete object itself
	 */
	public function delete()
	{
		$tableName = $this->getTableName();
		$identifier = $this->getIdentifierFieldName();
		$data = $this->toArray();
		$id = $data[$identifier];
		// maybe detecting foreign keys goes here
		
		$table = $this->getTable();
		if ($this instanceof SoftDeleteableInterface) {
			if ($this->getSoftDeleteable()) {
				//soft delete
				$table->update(
					array(
						$this->updatedTimeColumn => date('Y-m-d H:i:s'),
						$this->softDeleteableColumn => self::STATUS_DELETED
					), 
					array("$tableName.$identifier" => $id)
				);
				return;
			}
		}
		
		// hard delete the record
		$table->delete(array(
			"$tableName.$identifier" => $id,
		));
	}
	
	/**
	 * Restore soft delete instance
	 */
	public function restore()
	{
		$tableName = $this->getTableName();
		$identifier = $this->getIdentifierFieldName();
		$data = $this->toArray();
		$id = $data[$identifier];
		// maybe detecting foreign keys goes here
		
		$table = $this->getTable();
		if ($this instanceof SoftDeleteableInterface) {
            //restore deleted item
            $table->update(
                array(
                    $this->updatedTimeColumn => date('Y-m-d H:i:s'),
                    $this->softDeleteableColumn => self::STATUS_NONE_DELETED,
                ), 
                array("$tableName.$identifier" => $id )
            );
		}
        return $this;
	}
	
	/**
	 * Load soft deleted instance.
	 * When create new instance, we need to check if the exact same instance is soft-deleted so we can restore it
	 * 
	 * @param Where|\Closure|string|array $where
	 * @return \AppCore\Mvc\Model\Model | null
	 */	
	protected function loadSoftDeletedInstance($where = null)
	{
		if (!($this instanceof SoftDeleteableInterface && $this->getSoftDeleteable())) {
			return null;	
		}
		
		//if a custom $where provide then use it to load
		if (!isset($where)) {
			//if id is set, then use it to load soft-deleted instance
			$data = $this->toArray();
			$identifierFieldName = $this->getIdentifierFieldName();
			if (!empty($data[$identifierFieldName])) {
				$tableName = $this->getTableName();
				$where = array("$tableName.$identifierFieldName" => $data[$identifierFieldName]);
			}
		}
		
		if (!isset($where)) {
			return null;
		}
		
		$predicate = new Predicate();
		$predicate->equalTo($this->getTableName() . '.' . $this->softDeleteableColumn, self::STATUS_DELETED);
		
		/* @var $row \Zend\Db\ResultSet\ResultSet */
		$row = $this->getTable()->select(function (Select $select) use ($where, $predicate) {
			$select->where($where);
			$select->where->addPredicate($predicate);
		})->current();
		
		if (!$row) {
			return null;
		}
		
		$row->setTable($this->getTable());
		$row->setServiceManager($this->getServiceManager());
		return $row;
	}
	
	/**
	 * Load a deleted item
	 * @param int $id
	 * @return boolean
	 */
	public function loadSoftDeletedItem($id)
	{
		$this->disableSoftDelete();
		$result = $this->load($id);
		$this->enableSoftDelete();
		return $result;
	}
	
	/**
	 * @todo need inject translator here
	 * 
	 * @param string $string
	 * @return string
	 */
	public static function translate($string)
	{
		return $string;
	}
	
	/**
	 * Throw out an exception with a specific code. We will capture the message of exceptions with this code and show user it in an our custom error notification.
	 * Other exceptions (system error with professional message) will be thrown or only show user an general message like "System error" or something like that.
	 *
	 * @param string $msg
	 * @throws \Exception
	 */
	protected function throwPublicException($message)
	{
		throw new Exception($message, Exception::PUBLIC_VIEWABLE_ERROR_CODE);
	}
	
	/**
	 * Create a json string represent object state
	 * @return string
	 */
	public function toJson($additionalData = array())
	{
		$objectData = $this->toArrayByKeyType('propertyName');
		$objectData = array_merge($additionalData, $objectData);
		return json_encode($objectData);
	}
	
	/**
	 * Create a stdClass version
	 * @return object
	 */
	public function toStdClass()
	{
		$data = $this->toArrayByKeyType('propertyName');
		$ob = new \stdClass();
		foreach ($data as $key => $value) {
			$ob->{$key} = $value;
		}
		return $ob;
	}
}