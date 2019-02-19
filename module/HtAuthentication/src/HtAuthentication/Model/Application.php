<?php
/**
 * Application logic. Application : e.g. Payroll, HR ...
 */
namespace HtAuthentication\Model;

use AppCore\Mvc\Model\Model as AppCoreModel;
use AppCore\Mvc\Model\SoftDeleteableInterface;
use HtUser\Model\User;
use HtAuthentication\Model\Server;

use Zend\Db\Sql\Select;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\Factory;

class Application extends AppCoreModel implements InputFilterAwareInterface, SoftDeleteableInterface
{
	protected $applicationId;
	protected $title;
	protected $description;
	protected $creationDate;
	protected $lastUpdated;
	protected $inputFilter;


	protected $mapping = array(
		array('dbColumn' => 'application_id', 'objectProperty' => 'applicationId', 'isIdentifier' => true),
		array('dbColumn' => 'title', 'objectProperty' => 'title'),
		array('dbColumn' => 'description', 'objectProperty' => 'description'),
		array('dbColumn' => 'creation_date', 'objectProperty' => 'creationDate'),
		array('dbColumn' => 'last_updated', 'objectProperty' => 'lastUpdated'),
	);
	
	public function getApplicationId() {
		return $this->applicationId;
	}
	
	public function setApplicationId($applicationId) {
		$this->applicationId = $applicationId;
		return $this;
	}
	
	public function getTitle() {
		return $this->title;
	}
	
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}
	
	public function getDescription() {
		return $this->description;
	}
	
	public function setDescription($description) {
		$this->description = $description;
		return $this;
	}
	
	public function getCreationDate() {
		return $this->creationDate;
	}
	
	public function setCreationDate($creationDate) {
		$this->creationDate = $creationDate;
		return $this;
	}
	
	public function getLastUpdated() {
		return $this->lastUpdated;
	}
	
	public function setLastUpdated($lastUpdated) {
		$this->lastUpdated = $lastUpdated;
		return $this;
	}

	/**
	 * Get application which user can access
	 * @param User $user
	 */
	public function getByUser(User $user)
	{
		$userAppTable = $this->getServiceManager()->get('UserApp')->getTable()->table;
		$table = $this->getTable()->table;
		return $this->getAll(function(Select $select) use ($user, $userAppTable, $table) {
			$select->join($userAppTable, "{$userAppTable}.application_id = {$table}.application_id")
				->where(array('user_id' => $user->getUserId()));
		});
	}
	
	/**
	 * @SuppressWarnings(PHPMD)
	 */
	public function setInputFilter(InputFilterInterface $inputFilter)
	{
		throw new \Exception('Not use.');
	}
	
	public function getInputFilter()
	{
		if (!$this->inputFilter) {
			$inputFilter = new InputFilter();
			$factory = new Factory();
			
			$inputFilter->add($factory->createInput(array(
				'name' => 'title',
				'required' => true,
				'filters' => array(
					array('name' => 'StringTrim'),
					array('name' => 'StripTags'),
				),
			)));
			
			$inputFilter->add($factory->createInput(array(
				'name' => 'description',
				'required' => false,
				'filters' => array(
					array('name' => 'StringTrim'),
					array('name' => 'StripTags'),
				),
			)));
			
			$this->inputFilter = $inputFilter;
		}
		return  $this->inputFilter;
	}
	
	/**
	 * Check if application include the server or as any server
	 * @param Server $server 
	 * If provide check if this server belong to application. 
	 * If server is null then check if application has any server
	 * 
	 * @return boolean
	 * @throws \Exception
	 */
	public function hasServer($server = null)
	{
		if (!$this->getApplicationId()) {
			throw new \Exception('ApplicationId cannot be empty.');
		}
		if ($server instanceof Server) {
			$serverId = $server->getServerId();
		} else if (isset($server)) {
			$serverId = (int) $server;
		} else {
			$serverId = null;
		}
		
		$sm = $this->getServiceManager();
		$appServer = $sm->get('AppServer');
		
		if (isset($serverId)) {
			$count = $appServer->count(array(
				'application_id' => $this->getApplicationId(),
				'server_id' => $serverId,
			));
		} else {
			$count = $appServer->count(array(
				'application_id' => $this->getApplicationId(),
			));
		}
		
		if ($count > 0) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Allow organisation access to server of this application
	 * 
	 * @param int|Organisation $org
	 * @param int|Server $server
	 */
	public function addAccess($org, $server)
	{
		if ($org instanceof Organisation) {
			$org = $org->getOrganisationId();
		}
		if ($server instanceof Server) {
			$server = $server->getServerId();
		}
		
		if (!isset($this->applicationId) || !($this->applicationId > 0)) {
			throw new \Exception("Invalid application id");
		}
		$sl = $this->getServiceManager();
		/* @var $appServerOrg \HtAuthentication\Model\ApplicationServerOrganisation */
		$appServerOrg = $sl->get('AppServerOrg');
		
		$appServerOrg
			->setApplicationId($this->applicationId)
			->setOrganisationId($org)
			->setServerId($server)
			->save();
	}
	
	/**
	 * Add server to application
	 * 
	 * @param int | Server $server
	 */
	public function addServer($server)
	{
		if ($server instanceof Server) {
			$server = $server->getServerId();
		}
		
		if (!isset($this->applicationId) || !($this->applicationId > 0)) {
			throw new \Exception("Invalid application id");
		}
		
		$sl = $this->getServiceManager();
		/* @var $appServer \HtAuthentication\Model\ApplicationServer */
		$appServer = $sl->get('AppServer');
		
		$appServer->setApplicationId($this->applicationId)
			->setServerId($server)
			->save();
	}
	
	/**
	 * @SuppressWarnings(PHPMD)
	 */
	protected function setDefault($object = null)
	{
		//in create new case, set default value for empty property
		if (!isset($this->applicationId)) {
			if (empty($this->creationDate)) {
				$this->creationDate = date('Y-m-d H:i:s');
			}
		}		
	}
	
	public function save($object = null)
	{
		$this->setDefault($object);
		parent::save($object);
	}
	
}