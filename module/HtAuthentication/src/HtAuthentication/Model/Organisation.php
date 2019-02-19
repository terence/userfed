<?php
namespace HtAuthentication\Model;

use AppCore\Mvc\Model\Model;
use AppCore\Mvc\Model\SoftDeleteableInterface;
use HtUser\Model\User;
use HtAuthentication\Service\Model\UserOrganisationFactory;
use HtAuthentication\Model\Application;
use HtAuthentication\Model\Server;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\Factory;
use Zend\Db\Sql\Select;

class Organisation extends Model implements InputFilterAwareInterface, SoftDeleteableInterface
{
	protected $organisationId;
	protected $title;
	protected $description;
	protected $domain;
	protected $creationDate;
	protected $lastUpdated;
	
	protected $inputFilter;
	
	public function getOrganisationId()
	{
		return $this->organisationId;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getDomain()
	{
		return $this->domain;
	}

	public function getCreationDate()
	{
		return $this->creationDate;
	}

	public function getLastUpdated()
	{
		return $this->lastUpdated;
	}

	public function setOrganisationId($organisationId)
	{
		$this->organisationId = $organisationId;
		return $this;
	}

	public function setTitle($title)
	{
		$this->title = $title;
		return $this;
	}

	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	public function setDomain($domain)
	{
		$this->domain = $domain;
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
			
			$inputFilter->add($factory->createInput(array(
				'name' => 'domain',
				'required' => false,
				'filters' => array(
					array('name' => 'StringTrim'),
					array('name' => 'StripTags'),
				),
			)));
			
			$this->inputFilter = $inputFilter;
		}
		return $this->inputFilter;
	}

	/**
	 * @SuppressWarnings(PHPMD)
	 */
	public function setInputFilter(InputFilterInterface $inputFilter)
	{
		throw new \Eception('Not use.');
	}
	
	protected $mapping = array(
		array('dbColumn' => 'organisation_id', 'objectProperty' => 'organisationId', 'isIdentifier' => true),
		array('dbColumn' => 'title', 'objectProperty' => 'title'),
		array('dbColumn' => 'description', 'objectProperty' => 'description'),
		array('dbColumn' => 'domain', 'objectProperty' => 'domain'),
		array('dbColumn' => 'creation_date', 'objectProperty' => 'creationDate'),
		array('dbColumn' => 'last_updated', 'objectProperty' => 'lastUpdated'),
	);
	
	public function save($object = null)
	{
		//in create new case, set default value for empty property
		if (!isset($this->organisationId)) {
			if (empty($this->creationDate)) {
				$this->creationDate = date('Y-m-d H:i:s');
			}
		}
		parent::save($object);
	}
	
	/**
	 * Add user to organisation
	 * 
	 * @param int|User $user
	 */
	public function addUser($user)
	{
		if ($user instanceof User) {
			$user = $user->getUserId();
		}
		if (!isset($this->organisationId) || !($this->organisationId > 0)) {
			throw new \Exception("Organization id is invalid");
		}
		
		$sl = $this->getServiceManager();
		
		/* @var $userOrganisation \HtAuthentication\Model\UserOrganisation */
		$userOrgFactory = new UserOrganisationFactory();
		$userOrganisation = $userOrgFactory->createService($sl);
		$userOrganisation->setUserId($user);
		$userOrganisation->setOrganisationId($this->organisationId);
		$userOrganisation->save();
	}
	
	/**
	 * Check if org has the user
	 * @param User|int $user
	 * @return boolean
	 */
	public function hasUser($user)
	{
		$sl = $this->getServiceManager();
		/* @var $userOrganisation \HtAuthentication\Model\UserOrganisation */
		$userOrganisation = $sl->get('UserOrg');
		
		if ($user instanceof User) {
			$user = $user->getUserId();
		}
		
		if (empty($this->organisationId)) {
			throw new \Exception("No organisation id found");
		}
		$count = $userOrganisation->count(array('user_id' => $user, 'organisation_id' => $this->organisationId));
		if ($count > 0 ) {
			return true;
		}
		return false;
		
	}
	
	/**
	 * 
	 * @param \HtAuthentication\Model\Application | int $application
	 * @param \HtAuthentication\Model\Server | int $server
	 * @return boolean
	 * @throws \Exception
	 */
	public function hasAccess($application, $server)
	{
		if (!$this->getOrganisationId()) {
			throw new \Exception('Organisation cannot be null.');
		}
		
		if ($application instanceof Application) {
			$applicationId = $application->getApplicationId();
		} else {
			$applicationId = (int) $application;
		}
		
		if ($server instanceof Server) {
			$serverId = $server->getServerId();
		} else {
			$serverId = (int) $server;
		}
		
		$sm = $this->getServiceManager();
		/* @var $appServerOrg ApplicationServerOrganisation */
		$appServerOrg = $sm->get('AppServerOrg');
		
		$count = $appServerOrg->count(array(
			'application_id' => $applicationId,
			'organisation_id' => $this->organisationId,
			'server_id' => $serverId
		));
		
		if ($count > 0) {
			return true;
		}
		return false;
	}
        
        public function getUsers()
        {
            $sm = $this->getServiceManager();
            
            $user = $sm->get('User');
            $select = new Select();
            $userOrg = $sm->get('UserOrg');
            $table = $userOrg->getTable()->getTable();
//            $table = $this->getTableName();
            $userTable = $user->getTable()->getTable();
            $select->from($userTable)
                    ->join($table, "$userTable.user_id = $table.user_id", [])
                    ->where(["$table.organisation_id" => $this->getOrganisationId()]);
            
            return $user->getTableGateway()->selectWith($select);
        }
}
