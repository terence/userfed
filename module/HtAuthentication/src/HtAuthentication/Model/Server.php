<?php
/**
 * Manage server information.
 */
namespace HtAuthentication\Model;

use AppCore\Mvc\Model\SoftDeleteableInterface;
use AppCore\Mvc\Model\Model as AppCoreModel;

use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilter;

class Server extends AppCoreModel implements SoftDeleteableInterface, InputFilterAwareInterface
{
	protected $serverId;
	protected $title;
	protected $description;
	protected $ip;
	protected $domain;
	protected $secret;
	protected $location;
	protected $status;
	protected $creationDate;
	protected $lastUpdated;
    
    protected $inputFilter;

    //SERVER STATUS
	const STATUS_ACTIVE_PRODUCT = 1;
	const STATUS_ACTIVE_TEST = 2;
	const STATUS_DECOMMISSIONED = 3;
	
	protected $mapping = array(
		array('dbColumn' => 'server_id', 'objectProperty' => 'serverId', 'isIdentifier' => true),
		array('dbColumn' => 'title', 'objectProperty' => 'title'),
		array('dbColumn' => 'description', 'objectProperty' => 'description'),
		array('dbColumn' => 'ip', 'objectProperty' => 'ip'),
		array('dbColumn' => 'domain', 'objectProperty' => 'domain'),
		array('dbColumn' => 'secret', 'objectProperty' => 'secret'),
		array('dbColumn' => 'location', 'objectProperty' => 'location'),
		array('dbColumn' => 'status', 'objectProperty' => 'status'),
		array('dbColumn' => 'creation_date', 'objectProperty' => 'creationDate'),
		array('dbColumn' => 'last_updated', 'objectProperty' => 'lastUpdated'),
	);
	
	public function getServerId() {
		return $this->serverId;
	}
	
	public function setServerId($serverId) {
		$this->serverId = $serverId;
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
	
	public function getIp() {
		return $this->ip;
	}
	
	public function setIp($ip) {
		$this->ip = $ip;
		return $this;
	}
	
	public function getLocation() {
		return $this->location;
	}
	
	public function setLocation($location) {
		$this->location = $location;
		return $this;
	}
	
	public function getStatus() {
		return $this->status;
	}
	
	public function setStatus($status) {
		$this->status = $status;
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
		
	public function getDomain() {
		return $this->domain;
	}
	
	public function setDomain($domain) {
		$this->domain = $domain;
		return $this;
	}
	
	public function getSecret() {
		return $this->secret;
	}
	
	public function setSecret($secret) {
		$this->secret = $secret;
		return $this;
	}
		
	/**
	 * Get server info by secret
	 * 
	 * @string $secret
	 * @return object | null
	 */
	public function getBySecret($secret)
	{
		return $this->getOne(array('secret' => $secret));
	}
	
	public function save()
	{
        //in create new case, set default value for empty property
		if (!isset($this->serverId)) {
			if (empty($this->creationDate)) {
				$this->creationDate = date('Y-m-d H:i:s');
			}
		}
        if (!$this->secret) {
            $this->secret = $this->generateSecretKey();
        }
		parent::save();
	}

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            
            $inputFilter->add(array(
                'name' => 'title',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim'),
                    array('name' => 'StripTags')
                ),
            ));
            
            $inputFilter->add(array(
                'name' => 'description',
                'required' => false,
                'filters' => array(
                    array('name' => 'StringTrim'),
                    array('name' => 'StripTags')
                ),
            ));
            
            $inputFilter->add(array(
                'name' => 'ip',
                'required' => false,
                'validators' => array(
                    array('name' => 'ip'),
                ),
            ));
            
            $inputFilter->add(array(
                'name' => 'location',
                'required' => false,
                'filters' => array(
                    array('name' => 'StringTrim'),
                    array('name' => 'StripTags')
                ),
            ));
            
            $inputFilter->add(array(
                'name' => 'domain',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim'),
                    array('name' => 'StripTags')
                ),
                'validators' => array(
                    array(
                        'name' => 'uri',
                        'options' => array(
                            'allowRelative' => false
                        ),
                    ),
                ),
            ));
            
            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
        
    }
    
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception('Not use.');
    }
    
    protected function generateSecretKey()
    {
        $creationDate = $this->getCreationDate();
        if (!$creationDate) {
            $creationDate = date('Y-m-d H:i:s');
        }
        do {
            $secretKey = sha1($creationDate . rand(0, 100));
            // We need create a rand number to create another secret key difference with previous secret key
            $count = $this->count(array('secret' => $secretKey));
        } while ($count > 0);
        return $secretKey;
    }
}