<?php
namespace HtApplication\Model\Acl;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use AppCore\Db\TableGateway\AbstractTableGateway;

class UserRoleTable extends AbstractTableGateway
{
    protected $table = 'permission_user_role';
	  
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
    
    /**
     * Get role by user id
     *
     * @param int $userId
     * @return array
     */
    public function getRoles($userId)
    {
    	$userRoles = array();
    	$rowset = $this->select(array(
    		'user_id' => $userId,
    	));
    	if ($rowset->count()) {
    		foreach ($rowset as $row) {
    			$userRoles[] = $row['role_id'];
    		}
    	}
    	return $userRoles;
    }

    public function addRoles($userId, array $roles)
    {
    	foreach ($roles as $role) {
    		$this->insert(array(
    			'user_id' => $userId,
    			'role_id' => $role
    		));
    	}
    }
}