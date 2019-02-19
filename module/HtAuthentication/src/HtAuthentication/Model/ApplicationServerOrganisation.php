<?php
namespace HtAuthentication\Model;

use AppCore\Mvc\Model\Model;
use AppCore\Mvc\Model\SoftDeleteableInterface;

class ApplicationServerOrganisation extends Model implements SoftDeleteableInterface
{
	protected $applicationServerOrganisationId;
	protected $applicationId;
	protected $organisationId;
	protected $serverId;
	
	protected $mapping = array(
		array('dbColumn' => 'application_server_organisation_id', 'objectProperty' => 'applicationServerOrganisationId', 'isIdentifier' => true),
		array('dbColumn' => 'application_id', 'objectProperty' => 'applicationId'),
		array('dbColumn' => 'organisation_id', 'objectProperty' => 'organisationId'),
		array('dbColumn' => 'server_id', 'objectProperty' => 'serverId'),
	);
	
	public function getApplicationServerOrganisationId()
	{
		return $this->applicationServerOrganisationId;
	}

	public function getApplicationId()
	{
		return $this->applicationId;
	}

	public function getOrganisationId()
	{
		return $this->organisationId;
	}

	public function getServerId()
	{
		return $this->serverId;
	}
	
	public function setApplicationServerOrganisationId($applicationServerOrganisationId)
	{
		$this->applicationServerOrganisationId = $applicationServerOrganisationId;
		return $this;
	}

	public function setApplicationId($applicationId)
	{
		$this->applicationId = $applicationId;
		return $this;
	}

	public function setOrganisationId($organisationId)
	{
		$this->organisationId = $organisationId;
		return $this;
	}

	public function setServerId($serverId)
	{
		$this->serverId = $serverId;
		return $this;
	}
}
