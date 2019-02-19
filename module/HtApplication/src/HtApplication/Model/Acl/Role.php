<?php
/**
 * A role in this application is independency with other roles.
 * We don't implement role inheritance so we don't need storage parent value
 */

namespace HtApplication\Model\Acl;

use AppCore\Mvc\Model\Model as AppCoreModel;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\Factory;

use Zend\Validator\Db\NoRecordExists;
use Zend\Validator\Regex;

class Role extends AppCoreModel implements InputFilterAwareInterface
{
    protected $roleId;
	protected $description;
    
    const ADMIN_ROLE = 'admin';
	const MEMBER_ROLE = 'member';
	const GUEST_ROLE = 'guest';
    
    protected $mapping = array(
		array('dbColumn' => 'role_id', 'objectProperty' => 'roleId', 'isIdentifier' => true),
		array('dbColumn' => 'description', 'objectProperty' => 'description'),
    );
    
    public function getRoleId()
    {
        return $this->roleId;
    }


    public function getDescription()
    {
        return $this->description;
    }

    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;
        return $this;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }
    
    protected $inputFilter;
    
    public function getInputFilter()
    {
        if ($this->inputFilter) {
            return $this->inputFilter;
        }
        
        $factory = new Factory;
        $inputFilter = new InputFilter();
        
        $inputFilter->add($factory->createInput(array(
            'name' => 'role_id',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'Zend\Validator\Db\NoRecordExists',
                    'options' => array(
                        'adapter' => $this->getDbAdapter(),
                        'table' => $this->getTableName(),
                        'field' => 'role_id',
                        'messages' => array(
                            NoRecordExists::ERROR_RECORD_FOUND => 'This role_id is already used.',
                        ),
                    ),
                ),
                array(
                    'name' => 'Regex',
                    'options' => array(
                        'pattern' => '/^[a-zA-Z0-9_-]*$/',
                        'messages' => array(
                            Regex::NOT_MATCH => 'No space is allow : only character, number, dash and underscore.'
                        ),
                    ),
                ),
            ),
        )));
        
        $inputFilter->add($factory->createInput(array(
            'name' => 'description',
            'required' => false,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        )));
        
        $this->inputFilter = $inputFilter;
        
        return $this->inputFilter;
    }
    
    public function getInputFilterForUpdate()
    {
        $inputFilter = $this->getInputFilter();
        $inputFilter->remove('role_id');
        
        return $inputFilter;
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception('Not use.');
    }
    
    /**
     * Check if a role is allow to edit in Role's permission editor or not
     * @param string $roleId
     * @return boolean
     */
    public static function isDisabledEditPermission($roleId)
    {
        if ($roleId == self::ADMIN_ROLE || $roleId == self::GUEST_ROLE) {
            return true;
        }
        return false;
    }
    
    /**
     * Check if a role is allowed delete or not
     * @param string $roleId
     * @return boolean
     */
    public static function isAllowedDelele($roleId)
    {
        if (static::isDisabledEditPermission($roleId)) {
            return false;
        }
        
        if ($roleId == self::MEMBER_ROLE) {
            return false;
        }
        
        return true;
    }
}
