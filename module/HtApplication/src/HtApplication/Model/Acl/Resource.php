<?php

namespace HtApplication\Model\Acl;

use AppCore\Mvc\Model\Model as AppCoreModel;

class Resource extends AppCoreModel
{
    protected $resourceId;
    protected $parent;
    protected $description;
    protected $hideInPermissionEditor;
    protected $sortOrder;

    protected $mapping = array(
		array('dbColumn' => 'resource_id', 'objectProperty' => 'resourceId', 'isIdentifier' => true),
		array('dbColumn' => 'parent', 'objectProperty' => 'parent'),
		array('dbColumn' => 'description', 'objectProperty' => 'description'),
		array('dbColumn' => 'hide_in_permission_editor', 'objectProperty' => 'hideInPermissionEditor'),
    );
    
    public function getResourceId()
    {
        return $this->resourceId;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getHideInPermissionEditor()
    {
        return $this->hideInPermissionEditor;
    }

    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;
        return $this;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function setHideInPermissionEditor($hideInPermissionEditor)
    {
        $this->hideInPermissionEditor = $hideInPermissionEditor;
        return $this;
    }
    
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function getAll($where = null, $offset = null, $limit = null, $order = null)
    {
        if (!$order) {
            $order = 'sort_order';
        }
        return parent::getAll($where, $offset, $limit, $order);
    }
    
    protected $hiddenResources;
    /**
     * Return array hidden resources with key is resource_id
     * @return array
     */
    public function getHiddenResources()
    {
        if ($this->hiddenResources) {
            return $this->hiddenResources;
        }
        $rowset = $this->getAll(array('hide_in_permission_editor' => true));
        $hiddenResources = array();
        foreach ($rowset as $row) {
            $hiddenResources[$row->getResourceId()] = $row;
        }
        $this->hiddenResources = $hiddenResources;
        return $this->hiddenResources;
    }
}
