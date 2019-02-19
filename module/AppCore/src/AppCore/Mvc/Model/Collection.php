<?php
/**
 * A base class to work with collection.
 * ModelCollection will use a table object to fetch data.
 * Help to separate fetching methods out of Model class
 */
namespace AppCore\Mvc\Model;

use Zend\Db\TableGateway\TableGateway;

class Collection extends TableBase
{
	
	/*** Soft-deleteable implementation ***/
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
	
	public function hardDelete()
	{
		//not applied
	}
	
	public function restore()
	{
		//not applied
	}
	
	public function loadSoftDeletedInstance()
	{
		//not applied
		return null;
	}
	/*** End Soft-deleteable implementation ***/
}