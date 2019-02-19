<?php

namespace HtAdmin\Controller;

use HtApplication\Controller\AbstractActionController;
use HtAdmin\Form\Role as RoleForm;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Null as PaginantorNull;

use HtApplication\Model\Acl\Role;

class RoleController extends AbstractActionController
{
    public function editAction()
    {
        $roleId = $this->params()->fromRoute('id');
        $roleForm = new RoleForm();
        $form = $roleForm->getUpdateForm();
        $request = $this->getRequest();
        
        $sl = $this->getServiceLocator();
        /* @var $role \HtApplication\Model\Acl\Role */
        $role = $sl->get('HtApplication\Model\Acl\Role');
        if (!$role->load($roleId)) {
            $this->flashMessenger()->addErrorMessage(sprintf($this->translate('Role %s not found.'), $roleId));
            return $this->redirect()->toRoute('admin/role');
        }
        
        $successMessage = '';
        if ($request->isPost()) {
            $inputFilter = $role->getInputFilterForUpdate();
            $postData = $request->getPost();
            $form->setData($postData);
            $form->setInputFilter($inputFilter);
            if ($form->isValid()) {
                $data = $form->getData();
                try {
                    $connection = $this->getConnection();
                    $connection->beginTransaction();
                    $role->exchangeArray($data);
                    $role->save();
                    $connection->commit();
                    $successMessage = $this->translate('Update role successfully.');
                } catch (\Exception $e) {
                    $connection->rollback();
                    throw $e;
                }
            }
        } else {
            $form->setData($role->toArray());
        }
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $successMessage = implode('<br />', $this->flashMessenger()->getSuccessMessages());
        }
        
        return array(
            'form' => $form,
            'successMessage' => $successMessage,
            'role' => $role,
        );
    }
    
    public function editPermissionAction()
    {
        $sl = $this->getServiceLocator();
        
        $roleId = $this->params()->fromRoute('id');
        
        if (Role::isDisabledEditPermission($roleId)) {
            $this->flashMessenger()->addErrorMessage(
                    sprintf($this->translate('You do not have enough permission to edit %s role.'), $roleId)
                    );
            return $this->redirect()->toRoute('admin/role');
        }
        
        /* @var $role \HtApplication\Model\Acl\Role */
        $role = $sl->get('HtApplication\Model\Acl\Role');
        
        if (!$role->load($roleId)) {
            $this->flashMessenger()->addErrorMessage($this->translate('Role not found.'));
            return $this->redirect()->toRoute('admin/role');
        }
        
        /* @var $permissionAcl \HtApplication\Model\Acl\PermissionAcl */
        $permissionAcl = $sl->get('HtApplication\Model\Acl\PermissionAcl');
        $rowset = $permissionAcl->getAll(array('role_id' => $roleId, 'access' => true));
        $rolePermissions = array();
        /* get current permission of role. */
        foreach ($rowset as $row) {
            $rolePermissions[$row->getResourceId()] = $row->getAclId();
        }
        
        $request = $this->getRequest();
        $successMessage = "";
        if ($request->isPost()) {
            try {
                $connection = $this->getConnection();
                $connection->beginTransaction();
                $actions = $request->getPost('permissions');
                if (!$actions) {
                    $actions = array();
                }
                
                $permissionAcl->setRoleId($roleId);
                foreach ($actions as $action) {
                    if (!isset($rolePermissions[$action])) {
                        /* insert new permission for role */
                        $permissionAcl->addPermission($action);
                    } else {
                        /* remove form role permission */
                        unset($rolePermissions[$action]);
                    }
                }
                
                if ($rolePermissions) {
                    /* @var $resourceModel \HtApplication\Model\Acl\Resource */
                    $resourceModel = $this->getServiceLocator()->get('HtApplication\Model\Acl\Resource');
                    $hiddenResources = $resourceModel->getHiddenResources();
                    foreach ($rolePermissions as $resourceId => $aclId) {
                        /** 
                         * Find & remove permission unchecked
                         * We only remove resources not hidden (can add again).
                         */
                        if (!isset($hiddenResources[$resourceId])) {
                            $permissionAcl->removePermission($resourceId);
                        }
                    }
                }
                
                $connection->commit();
            } catch (\Exception $e) {
                $connection->rollback();
                throw $e;
            }
            
            $rowset = $permissionAcl->getAll(array('role_id' => $roleId, 'access' => true));
            $rolePermissions = array();
            /* update current permission of role. */
            foreach ($rowset as $row) {
                $rolePermissions[$row->getResourceId()] = $row->getAclId();
            }
            $successMessage = $this->translate("Update permission successfully!");
        }
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $successMessage = implode('<br />', $this->flashMessenger()->getSuccessMessages());
        }
        
        $optionGroups = $this->buildOptionGroups($rolePermissions);
        
        return array(
            'role' => $role,
            'optionGroups' => $optionGroups,
            'successMessage' => $successMessage,
        );
    }
    
    /**
     * Build data for select control in module/HtAdmin/view/ht-admin/role/edit-permission.phtml
     * return array(
     *     array(
     *          'label' => 'Group label',
     *          'options' => array(
     *              'resource_id' => array(
     *                  'text' => 'Option Description',
     *                  'value' => 'Option value',
     *                  'selected' => 'true if role can access to resource (option)',
     *              ),
     *          ),
     *     ),
     * );
     * @param array $rolePermissions
     * @return array
     */
    protected function buildOptionGroups($rolePermissions)
    {
        $optionGroups = array();
        $resources = $this->getTreeResource();
        
        
        foreach ($resources as $resource) {
            $pDescription = $resource['description'];
            $parentAccessable = false;
            if (isset($rolePermissions[$resource['resource_id']])) {
                $parentAccessable = true;
            }
            if (empty($resource['children'])) {
                
                $optionGroups[] = array(
                    'label' => $pDescription,
                        'options' => array(
                            $resource['resource_id'] => array(
                                'text' => $pDescription,
                                'value' => $resource['resource_id'],
                                'selected' => $parentAccessable
                            ),
                        ),
                    );
                continue;
            }

            $childResources = $resource['children'];
            $group = array(
                   'label' => $pDescription,
                   'options' => array(),
                );
            foreach ($childResources as $cResource) {
                $accessable = isset($rolePermissions[$cResource['resource_id']]);
                if (!$accessable) {
                    $accessable = $parentAccessable;
                }
                $cDescription = $cResource['description'];
                $group['options'][$cResource['resource_id']] = array(
                    'text' => "$pDescription | $cDescription",
                    'value' => $cResource['resource_id'],
                    'selected' => $accessable,
                );
            }
            $optionGroups[] = $group;
        }
        
        return $optionGroups;
    }

    public function createAction()
    {
        $form = new RoleForm();
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            /* @var $role \HtApplication\Model\Acl\Role */
            $role = $sl->get('HtApplication\Model\Acl\Role');
            $inputFilter = $role->getInputFilter();
            $postData = $request->getPost();
            $form->setData($postData);
            $form->setInputFilter($inputFilter);
            if ($form->isValid()) {
                $data = $form->getData();
                try {
                    $connection = $this->getConnection();
                    $connection->beginTransaction();
                    $role->exchangeArray($data);
                    $role->save();
                    $connection->commit();
                    $this->flashMessenger()->addSuccessMessage('Create role successfully. You can add permission for this role now.');
                    return $this->redirect()->toRoute('admin/role', array('action' => 'edit-permission', 'id' => $role->getRoleId()));
                } catch (\Exception $e) {
                    $connection->rollback();
                    throw $e;
                }
            }
        }
        
        return array(
            'form' => $form,
        );
    }
    
    public function indexAction()
    {
        $sl = $this->getServiceLocator();
        
        /* @var $role \HtApplication\Model\Acl\Role */
        $role = $sl->get('HtApplication\Model\Acl\Role');
        
        $count = $role->count();
        $pagingParams = $this->pagingParams()->get($count);
        $roles = $role->getAll(array(), $pagingParams['offset'], $pagingParams['limit'])->toArray();
        
        $roleIds = array();
        foreach ($roles as &$role) {
            $role['disabled_edit_permission'] = Role::isDisabledEditPermission($role['role_id']);
            $role['allowed_delete'] = Role::isAllowedDelele($role['role_id']);
            if ($role['role_id'] == Role::GUEST_ROLE) {
                $role['hidden_number_user'] = true;
            }
            $roleIds[] = $role['role_id'];
        }
        
        $countUsers = array();
        if ($roleIds) {
            /* @var $userRoleCollection \HtApplication\Model\Acl\UserRoleCollection */
            $userRoleCollection = $sl->get('HtApplication\Model\Acl\UserRoleCollection');
            
            $rowset = $userRoleCollection->countUser($roleIds);
            foreach ($rowset as $row) {
                $countUsers[$row->role_id] = $row->count_users;
            }
        }
        
        $paginator = new Paginator(new PaginantorNull($count));
        $paginator->setItemCountPerPage($pagingParams['limit']);
        $paginator->setCurrentPageNumber($pagingParams['page']);
        
        $successMessage = '';
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $successMessage = implode('<br />', $this->flashMessenger()->getSuccessMessages());
        }
        
        $errorMessage = '';
        if ($this->flashMessenger()->hasErrorMessages()) {
            $errorMessage = implode('<br />', $this->flashMessenger()->getErrorMessages());
        }
        
        return array(
            'roles' => $roles,
            'paginator' => $paginator,
            'successMessage' => $successMessage,
            'errorMessage' => $errorMessage,
            'countUsers' => $countUsers,
        );
    }
    
    protected function gotoIndex($message = null, $error = true)
    {
        if ($message) {
            if ($error) {
                $this->flashMessenger()->addErrorMessage($message);
            } else {
                $this->flashMessenger()->addSuccessMessage($message);
            }
        }
        return $this->redirect()->toRoute('admin/role');
    }
    
    /**
     * Build tree resources
     * 
     * @return array
     */
    protected function getTreeResource()
    {
        /* @var $resource \HtApplication\Model\Acl\Resource */
        $resourceModel = $this->getServiceLocator()->get('HtApplication\Model\Acl\Resource');
        $resources = $resourceModel->getAll(array('hide_in_permission_editor' => false))->toArray();
        
        $tree = array();
        $hashTable = array();
        foreach ($resources as $resource) {
            $hashTable[$resource['resource_id']] = $resource;
        }
        
        foreach ($resources as $resource) {
            if (!isset($hashTable[$resource['parent']])) {
                $tree[$resource['resource_id']] = $resource;
            } else {
                $tree[$resource['parent']]['children'][] = $resource;
            }
        }
        return $tree;
    }
    
    public function deleteAction()
    {
        $roleId = $this->params()->fromRoute('id');
        
        if (!Role::isAllowedDelele($roleId)) {
            $this->flashMessenger()
                    ->addErrorMessage(sprintf($this->translate("You could not delete this role."), $roleId));
        }
        
        $sl = $this->getServiceLocator();
        
        /* @var $role Role */
        $role = $sl->get('HtApplication\Model\Acl\Role');
        
        if (!$role->load($roleId)) {
            $this->flashMessenger()->addErrorMessage($this->translate('Role not found.'));
            return $this->redirect()->toRoute('admin/role');
        }
        
        /* @var $userRole \HtApplication\Model\Acl\UserRole */
        $userRole = $sl->get('HtApplication\Model\Acl\UserRole');
        /**
         * Not allow delete if still any user has this role
         */
        if ($userRole->count(array('role_id' => $roleId)) > 0) {
            $this->flashMessenger()->addErrorMessage($this->translate('You can not delete this role. There is user belong to this role.'));
            return $this->redirect()->toRoute('admin/role');
        }
        
        $connection = $this->getConnection();
        try {
            $connection->beginTransaction();
            $role->delete();
            $connection->commit();
            $this->flashMessenger()->addSuccessMessage($this->translate('Delete role successfully.'));
            return $this->redirect()->toRoute('admin/role');
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }
}
