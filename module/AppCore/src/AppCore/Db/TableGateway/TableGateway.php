<?php
/**
 * Some times we need an easy way to access other tables in Collection class.
 * That's why we create this one.
 * 
 * A TableGateway class which is soft-delete aware.
 */
namespace AppCore\Db\TableGateway;

use Zend\Db\TableGateway\TableGateway as ZendTableGateway;
use AppCore\Mvc\Model\TableBase;

class TableGateway extends ZendTableGateway
{
	protected $softDeleteableColumn = 'is_deleted';
	
	protected $softDeleteable = true;
	
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

	public function select($where = null)
	{
		if (!$this->isInitialized) {
            $this->initialize();
        }

        $select = $this->sql->select();
		if ($this->getSoftDeleteable()) {
			$select->where
					->nest()
						->notEqualTo($this->getTable() . "." . $this->softDeleteableColumn, TableBase::STATUS_DELETED)
						->OR
						->isNull($this->getTable() . "." . $this->softDeleteableColumn)
					->unnest();
		}
		
        if ($where instanceof \Closure) {
            $where($select);
        } elseif ($where !== null) {
            $select->where($where);
        }
		
        return $this->selectWith($select);
		
	}
}
