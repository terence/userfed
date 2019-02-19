<?php

namespace HtApplication\Model\Acl;

use AppCore\Mvc\Model\Model as AppCoreModel;

class PermissionAcl extends AppCoreModel
{
    
    protected $aclId;
    protected $resourceId;
    protected $access;
    protected $priviledges;
    protected $assertionClass;
    protected $roleId;
    protected $sortOrder;

    protected $mapping = array(
		array('dbColumn' => 'acl_id', 'objectProperty' => 'aclId', 'isIdentifier' => true),
		array('dbColumn' => 'resource_id', 'objectProperty' => 'resourceId'),
		array('dbColumn' => 'access', 'objectProperty' => 'access'),
		array('dbColumn' => 'priviledges', 'objectProperty' => 'priviledges'),
		array('dbColumn' => 'assertion_class', 'objectProperty' => 'assertionClass'),
		array('dbColumn' => 'role_id', 'objectProperty' => 'roleId'),
		array('dbColumn' => 'sort_order', 'objectProperty' => 'sortOrder'),
    );
    
    public function getAclId()
    {
        return $this->aclId;
    }

    public function getResourceId()
    {
        return $this->resourceId;
    }

    public function getAccess()
    {
        return $this->access;
    }

    public function getPriviledges()
    {
        return $this->priviledges;
    }

    public function getAssertionClass()
    {
        return $this->assertionClass;
    }

    public function getRoleId()
    {
        return $this->roleId;
    }

    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    public function setAclId($aclId)
    {
        $this->aclId = $aclId;
        return $this;
    }

    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;
        return $this;
    }

    public function setAccess($access)
    {
        $this->access = $access;
        return $this;
    }

    public function setPriviledges($priviledges)
    {
        $this->priviledges = $priviledges;
        return $this;
    }

    public function setAssertionClass($assertionClass)
    {
        $this->assertionClass = $assertionClass;
        return $this;
    }

    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;
        return $this;
    }

    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }
    
    public function getParentSortOrder($resoureId = null)
    {
        if (!$resoureId) {
            $resoureId = $this->getResourceId();
        }
        $params = explode(':', $resoureId);
        $namespace = array_shift($params);
        $controller = array_shift($params);
        $parentResourceId = $namespace;
        if ($controller) {
            $parentResourceId .= ':' . $controller;
        }
        
        $resourceTableGateway = $this->getTableGateway('permission_resource', false);
        /* @var $resource Resource */
        $resource = $this->getServiceManager()->get('HtApplication\Model\Acl\Resource');
        $rowset = $resource->getAll(array('resource_id' => $parentResourceId));
        $row = $rowset->current();
        
        if ($row) {
            return $row->getSortOrder();
        }
        
        return null;
    }
    
    public function addPermission($resourceId = null, $role = null, $sortOrder = null)
    {
        /* We need set null ensure alway insert a new record */
        $this->setAclId(null);
        
        if ($resourceId) {
            $this->setResourceId($resourceId);
        }
        
        if ($role) {
            $this->setRoleId($role);
        }
        
        if (!$sortOrder) {
            $sortOrder = $this->getParentSortOrder();
        }
        
        $this->setSortOrder($sortOrder)
                ->setAccess(true)
                ->save();
    }
    
    /**
     * @param string $resourceId
     * @param string $roleId
     */
    public function removePermission($resourceId = null, $roleId = null)
    {
        if ($roleId === null) {
            $roleId = $this->getRoleId();
        }
        
        if ($resourceId === null) {
            $resourceId = $this->getResourceId();
        }
        
        $permissionRole = $this->getOne(array('resource_id' => $resourceId, 'role_id' => $roleId));
        if ($permissionRole) {
            $permissionRole->delete();
        }
    }
}
