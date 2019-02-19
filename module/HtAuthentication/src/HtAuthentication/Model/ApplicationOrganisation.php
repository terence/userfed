<?php
namespace HtAuthentication\Model;

use AppCore\Mvc\Model\Model;
use AppCore\Mvc\Model\SoftDeleteableInterface;
class ApplicationOrganisation extends Model implements SoftDeleteableInterface
{
	protected $applicationOrganisationId;
	protected $organisationId;
	protected $applicationId;
	
	protected $mapping = array(
		array('dbColumn' => 'application_organisation_id', 'objectProperty' => 'applicationOrganisationId', 'isIdentifier' => true),
		array('dbColumn' => 'organisation_id', 'objectProperty' => 'organisationId'),
		array('dbColumn' => 'application_id', 'objectProperty' => 'applicationId'),
	);
	
	public function getApplicationOrganisationId()
	{
		return $this->applicationOrganisationId;
	}

	public function getOrganisationId()
	{
		return $this->organisationId;
	}

	public function getApplicationId()
	{
		return $this->applicationId;
	}

	public function setApplicationOrganisationId($applicationOrganisationId)
	{
		$this->applicationOrganisationId = $applicationOrganisationId;
		return $this;
	}

	public function setOrganisationId($organisationId)
	{
		$this->organisationId = $organisationId;
		return $this;
	}

	public function setApplicationId($applicationId)
	{
		$this->applicationId = $applicationId;
		return $this;
	}
	
}
