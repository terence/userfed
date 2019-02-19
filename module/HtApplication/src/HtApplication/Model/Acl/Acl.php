<?php
namespace HtApplication\Model\Acl;

use AppCore\Mvc\Model\Model as AppCoreModel;
use Zend\Permissions\Acl\Acl as ZendAcl;
use Zend\Permissions\Acl\Role\GenericRole;
use Zend\Permissions\Acl\Resource\GenericResource;
use HtApplication\Model\Acl\Navigation as NavigationAcl;

class Acl extends AppCoreModel
{
    /**
     * @var ZendAcl
     */
	protected $acl;
    
    /**
     *
     * @var array
     */
    protected $roles = array();
    
    /**
     *
     * @var array
     */
    protected $resources = array();
    
    /**
     * Acl for navigation
     * @var ZendAcl 
     */
    protected $navAcl;
    
    /**
     * We should convert to array for better performance.
     * @return array
     */
    protected function getPermissions()
	{
        /* @var $permissionAcl PermissionAcl */
        $permissionAcl = $this->getServiceManager()->get('HtApplication\Model\Acl\PermissionAcl');
        $rowset = $permissionAcl->getAll(array(), null, null, 'sort_order');
        $permissionAcls = array();
        foreach ($rowset as $row) {
            $permissionAcls[] = $row;
        }
        return $permissionAcls;
	}
	
    /**
     * We should convert to array for better performance.
     * @return array
     * @throws \Exception
     */
	protected function getRoles()
	{
        if (empty($this->roles)) {
            $model = $this->getServiceManager()->get('HtApplication\Model\Acl\Role');
            $rowset = $model->getAll();
            $roles = array();
            foreach ($rowset as $row) {
                $roles[] = $row;
            }
            
            if (empty($roles)) {
                throw new \Exception("No role found");
            }
            $this->roles = $roles;
        }
        return $this->roles;
	}
	
    /**
     * We should convert to array for better performance.
     * @return array
     * @throws \Exception
     */
	protected function getResources()
	{
        if (empty($this->resources)) {
            $resource = $this->getServiceManager()->get('HtApplication\Model\Acl\Resource');
            $rowset = $resource->getAll();
            $resources = array();
            foreach ($rowset as $row) {
                $resources[] = $row;
            }
            if (empty($resources)) {
                throw new \Exception("No resource found");
            }
            $this->resources = $resources;
        }
        
        return $this->resources;
	}
	
    public function init()
	{
		if (!isset($this->acl)) {
			$acl = new ZendAcl();
			
			foreach ($this->getRoles() as $role) {
                $acl->addRole(new GenericRole($role->getRoleId()));
			}
			
			foreach ($this->getResources() as $resource) {
				if ($resource->getParent()) {
					$acl->addResource(new GenericResource($resource->getResourceId()), $resource->getParent());
				} else {
					$acl->addResource(new GenericResource($resource->getResourceId()));
				}
			}
            
			foreach ($this->getPermissions() as $permission) {
				$assertion = null;
				$assertionClass = $permission->getAssertionClass();
				if (!empty($assertionClass)) {
					$assertion = new $assertionClass();
				}
				$priviledges = $permission->getPriviledges();
				if (!empty($priviledges)) {
					$priviledges = explode(',', $priviledges);
				}
				
				if ($permission->getAccess() == 1) {
					$acl->allow($permission->getRoleId(), $permission->getResourceId(), $priviledges, $assertion);
				} else {
					$acl->deny($permission->getRoleId(), $permission->getResourceId(), $priviledges, $assertion);
				}
			}
			
			$this->acl = $acl;
            $navAcl = new NavigationAcl($this->getRoles(), $this->getResources(), $this->getPermissions());
            $navAcl->init();
            $this->navAcl = $navAcl->getAcl();
		}
	}
    
    /**
     * 
     * @return ZendAcl
     */
    public function getAcl()
    {
        return $this->acl;
    }
	
    /**
     * Acl for navigation
     * 
     * @return ZendAcl
     */
    public function getNavigationAcl()
    {
        return $this->navAcl;
    }
    
	/**
	 * 
     * @param  Role\RoleInterface|string            $role
     * @param  Resource\ResourceInterface|string    $resource
     * @param  string                               $privilege
     * @return boolean
     */	
	public function isAllow($role, $resource, $privilege = null)
	{
        $this->init();
		return $this->acl->isAllowed($role, $resource, $privilege);
	}
}