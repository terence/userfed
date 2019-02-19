<?php
namespace HtAuthentication\Model;

use AppCore\Mvc\Model\Model as AppCoreModel;
use AppCore\Mvc\Model\SoftDeleteableInterface;

class ApplicationServer extends AppCoreModel implements SoftDeleteableInterface
{
	protected $applicationServerId;
	protected $applicationId;
	protected $serverId;
	protected $creationDate;
	protected $lastUpdated;
	
	protected $mapping = array(
		array('dbColumn' => 'application_server_id', 'objectProperty' => 'applicationServerId', 'isIdentifier' => true),
		array('dbColumn' => 'application_id', 'objectProperty' => 'applicationId'),
		array('dbColumn' => 'server_id', 'objectProperty' => 'serverId'),
		array('dbColumn' => 'creation_date', 'objectProperty' => 'creationDate'),
		array('dbColumn' => 'last_updated', 'objectProperty' => 'lastUpdated'),
	);
	
	public function getApplicationServerId()
	{
		return $this->applicationServerId;
	}

	public function getApplicationId()
	{
		return $this->applicationId;
	}

	public function getServerId()
	{
		return $this->serverId;
	}

	public function getCreationDate()
	{
		return $this->creationDate;
	}

	public function getLastUpdated()
	{
		return $this->lastUpdated;
	}

	public function setApplicationServerId($applicationServerId)
	{
		$this->applicationServerId = $applicationServerId;
		return $this;
	}

	public function setApplicationId($applicationId)
	{
		$this->applicationId = $applicationId;
		return $this;
	}

	public function setServerId($serverId)
	{
		$this->serverId = $serverId;
		return $this;
	}

	public function setCreationDate($creationDate)
	{
		$this->creationDate = $creationDate;
		return $this;
	}

	public function setLastUpdated($lastUpdated)
	{
		$this->lastUpdated = $lastUpdated;
		return $this;
	}
	
	public function save($object = null)
	{
		//in create new case, set default value for empty property
		if (!isset($this->applicationServerId)) {
			if (empty($this->creationDate)) {
				$this->creationDate = date('Y-m-d H:i:s');
			}
		}
		parent::save($object);
	}
	
    protected function loadSoftDeletedInstance($where = null)
    {
        $where = array('application_id' => $this->getApplicationId(), 'server_id' => $this->getServerId());
        return parent::loadSoftDeletedInstance($where);
    }
    
}