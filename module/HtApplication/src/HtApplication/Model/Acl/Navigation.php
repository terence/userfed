<?php
/**
 * Warning: this ACL model is not the same as Zend\ACL propose usaged in example. It's different.
 * It is food for navigation only.
 * 
 * With resources structure:
 * Resource 1
 * Resource 1.1 ( parent = Resource 1)
 * Resource 1.1.1 ( parent = Resource 1.1)
 * 
 * It allows case like:
 * - Allow Resource 1.1.1 -> allow Resource 1.1, Resource 1. So user can see menu Resource 1 > Resource 1.1 > Resource 1.1.1
 * - Allow Resource 1 -> Resource 1.1, Resource 1.1.1. So user can see menu : Resource 1 > Resource 1.1 > Resource 1.1.1
 */
namespace HtApplication\Model\Acl;

use Zend\Permissions\Acl\Acl as ZendAcl;
use Zend\Permissions\Acl\Role\GenericRole;
use Zend\Permissions\Acl\Resource\GenericResource;

class Navigation
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
     *
     * @var array
     */
    protected $permissions = array();
    
    public function __construct($roles, $resources, $permissions)
    {
        $this->roles = $roles;
        $this->resources = $resources;
        $this->permissions = $permissions;
    }
    
    /**
     * @return ZendAcl
     */
    public function getAcl()
    {
        return $this->acl;
    }
    
    /**
     * Build rule
     */
    public function init()
    {
        if (isset($this->acl)) {
            return;
        }
        
        $acl = $this->acl = new ZendAcl();  
        foreach ($this->roles as $role) {
            $acl->addRole(new GenericRole($role->getRoleId()));
        }
         
        //We do not allow zend/resource inheritance rule to explicit identify rule for each resource
        foreach ($this->resources as $resource) {
//          if (isset($resource->parent)) {
//				$acl->addResource(new Resource($resource->resource_id), $resource->parent);
//			} else {
                $acl->addResource(new GenericResource($resource->getResourceId()));
//			}
        }
        
        $resourceTree = $this->buildResourceTree();
        
        foreach ($this->permissions as $permission) {
            $assertion = null;
            $assertionClass = $permission->getAssertionClass();
            if (!empty($assertionClass)) {
                $assertion = new $assertionClass();
            }
            $priviledges = $permission->getPriviledges();
            if (!empty($priviledges)) {
                $priviledges = explode(',', $priviledges);
            }

            $role = $permission->getRoleId();
            $resource = $permission->getResourceId();
            if ($permission->getAccess() == 1) {
                $acl->allow($role, $resource, $priviledges, $assertion);
                $childResources = $this->getChildrenOnTree($resource, $resourceTree);
                if (!empty($childResources)) {
                    $this->allowChildResource($role, $childResources, $priviledges, $assertion);
                }
                $this->allowParentResource($resourceTree, $role, $resource, $priviledges, $assertion);
            } else {
                $acl->deny($role, $resource, $priviledges, $assertion);
            }
        }
    }
    
    /**
     * Get children node of a node
     * 
     * @param string $node
     * @param array $tree
     * @return array
     */
    protected function getChildrenOnTree($node, $tree)
    {
        foreach($tree as $tNode => $tNodeValue) {
            $children = $tNodeValue['children'];
            if ($node == $tNode) {
                if (!empty($children)) {
                    return $children;
                } else {
                    return array();
                }
            }
            if (!empty($children)) {
                $found = $this->getChildrenOnTree($node, $children);
                if (!empty($found)) {
                    return $found;
                }
            }
        }
        return array();
    }
    
    /**
     * Build a tree structure of resources
     * 
     * @param array $resources
     * @return array $resourceTree
     */
    protected function buildResourceTree(array $resources = null)
    {
        //convert from array of object to array of array
        if ($resources === null) {
            $resources = array();
            foreach ($this->resources as $resource) {
                $resources[$resource->getResourceId()] = array('parent' => $resource->getParent(), 'children' => array());
            }
        }
        
        //build tree with for
        $resourceTree = array();
        foreach ($resources as $id => &$resArrItem) {
            //level 1 resource
            if (!$resArrItem['parent']) {
                $resourceTree[$id] = &$resArrItem;
            } else {
                $parentIndex = $resArrItem['parent'];
                $parent = &$resources[$parentIndex];
                $parent['children'][$id] = &$resArrItem;
            }
        }
        return $resourceTree;
    }
    
    /**
     * Add allow rule for resources's children
     * 
     * @param string $role
     * @param array $resources
     * @param string $priviledges
     * @param string $assertion
     */
    protected function allowChildResource($role, $resources, $priviledges, $assertion)
    {
        foreach ($resources as $resource => $resourceValue) {
            $children = $resourceValue['children'];
            $this->acl->allow($role, $resource, $priviledges, $assertion);
            if (!empty($children)) {
                $this->allowChildResource($role, $children, $priviledges, $assertion);            
            }
        }
    }
    
    /**
     * Add allow rule for resource's parent
     * 
     * @param array $resourceTree
     * @param string $role
     * @param string $resource
     * @param string $priviledges
     * @param string $assertion
     * @return boolean
     */
    protected function allowParentResource($resourceTree, $role, $resource, $priviledges, $assertion)
    {
        foreach ($resourceTree as $parentResource => $parentResourceValue) {
            $children = $parentResourceValue['children'];
            if (isset($children[$resource])) {
                $this->acl->allow($role, $parentResource, $priviledges, $assertion);
                return true;
            }
            if ($this->allowParentResource($children, $role, $resource, $priviledges, $assertion)) {
                $this->acl->allow($role, $parentResource, $priviledges, $assertion);
                return true;
            }
        }
    }
}
