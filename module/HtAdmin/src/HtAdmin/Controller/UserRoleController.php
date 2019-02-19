<?php

namespace HtAdmin\Controller;

use HtApplication\Controller\AbstractActionController;
use HtApplication\Model\Acl\Role;

use Zend\Db\Sql\Predicate\NotIn;
use Zend\Db\Sql\Where;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Null as PaginatorNull;
use Zend\View\Model\JsonModel;

class UserRoleController extends AbstractActionController
{
    /**
     * view user's role
     * @return type
     */
	public function userAction()
	{
		$userId = $this->params()->fromRoute('id');
		$sl = $this->getServiceLocator();
		
		/* @var $user \HtUser\Model\User */
		$user = $sl->get('User');
        $flashMessager = $this->flashMessenger();
		if (!$user->load($userId)) {
            $flashMessager->addErrorMessage($this->translate('No user found.'));
            return $this->redirect()->toRoute('admin/user');
		}
		/* @var $role \HtApplication\Model\Acl\UserRole */
		$userRole = $sl->get('HtApplication\Model\Acl\UserRole');
		$rowset = $userRole->getAll(array('user_id' => $userId));
        
        $userRoleIds = array();
        foreach ($rowset as $row) {
            $userRoleIds[] = $row->role_id;
        }
        
        /* @var $role \HtApplication\Model\Acl\Role */
        $role = $sl->get('HtApplication\Model\Acl\Role');
        $userRoles = array();
        if ($userRoleIds) {
            $userRoles = $role->getAll(array('role_id' => $userRoleIds));
        }
        
        /**
         * Prepare data for select option role.
         * We only need roles user doesn't has and It isn't guest role.
         * So that's why we need add guest role to user role as this user already had it.
         */
        $userRoleIds[] = Role::GUEST_ROLE;
        $roles = $role->getAll(new NotIn('role_id', $userRoleIds));
        
        $errorMessage = '';
        if ($flashMessager->hasErrorMessages()) {
            $errorMessage = implode('<br />', $flashMessager->getErrorMessages());
        }
        
        $successMessage = '';
        if ($flashMessager->hasSuccessMessages()) {
            $successMessage = implode('<br />', $flashMessager->getSuccessMessages());
        }
        
		return array(
			'user' => $user,
			'userRoles' => $userRoles,
            'roles' => $roles,
            'successMessage' => $successMessage,
            'errorMessage' => $errorMessage,
		);
	}
    
    public function addRoleAction()
    {
        $userId = $this->params()->fromRoute('id');
        $sl = $this->getServiceLocator();
        
        /* @var $user \HtUser\Model\User */
        $user = $sl->get('User');
        if (!$user->load($userId)) {
            $this->flashMessenger()->addErrorMessage($this->translate('No user found.'));
            return $this->redirect()->toRoute('admin/user');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $role = $request->getPost('role_id');
            if ($role) {
                if ($role == Role::ADMIN_ROLE && !$this->isAdmin()) {
                    $this->flashMessenger()->addErrorMessage('You must have admin role to assign admin role to another user.');
                    return $this->redirect()->toRoute('admin/user/role', array('id' => $userId));
                }
                
                try {
                    $connection = $this->getConnection();
                    $connection->beginTransaction();
                    $user->addRole(array($role));
                    $connection->commit();
                    $this->flashMessenger()->addSuccessMessage($this->translate('Add role successfully.'));
                    return $this->redirect()->toRoute('admin/user/role', array('id' => $userId));
                } catch (\Exception $e) {
                    $connection->rollback();
                    throw $e;
                }
            }
        }
    }
    
    /**
     * Delete user's role
     * @return type
     * @throws \HtAdmin\Controller\Exception
     */
    public function deleteAction()
    {
        $userId = $this->params()->fromRoute('id');
        $sl = $this->getServiceLocator();
        
        /* @var $user \HtUser\Model\User */
        $user = $sl->get('User');
        
        if (!$user->load($userId)) {
            $this->flashMessenger()->addErrorMessage($this->translate('No user found.'));
            return $this->redirect()->toRoute('admin/user');
        }
        
        $roleId = $this->params()->fromRoute('roleId');
        /* check affected user has admin permission */
        if ($user->isAdmin() && !$this->isAdmin()) {
            $this->flashMessenger()->addErrorMessage($this->translate('You must have admin role to change role of another admin user.'));
            return $this->redirect()->toRoute('admin/user/role', array('id' => $userId));
        }
        
        try {
            $connection = $this->getConnection();
            $connection->beginTransaction();
            /* @var $userRole \HtApplication\Model\Acl\UserRole */
            $userRole = $sl->get('HtApplication\Model\Acl\UserRole');
            $userRole->deleteRole($userId, $roleId);
            $connection->commit();
            $this->flashMessenger()->addSuccessMessage($this->translate('Delete user\'s role successfully.'));
            return $this->redirect()->toRoute('admin/user/role', array('id' => $userId));
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }
    
    /**
     * 
     * View role's User
     * @return array | ViewModel
     */
    public function roleAction()
    {
        $roleId = $this->params()->fromRoute('id');
        
        $sl = $this->getServiceLocator();
        /* @var $role Role */
        $role = $sl->get('HtApplication\Model\Acl\Role');
        if (!$role->load($roleId)) {
            $this->flashMessenger()->addErrorMessage($this->translate('No role found.'));
            return $this->redirect()->toRoute('admin/role');
        }
        
        /* @var $userRole \HtApplication\Model\Acl\UserRole */
        $userRole = $sl->get('HtApplication\Model\Acl\UserRole');
        
        $where = array('role_id' => $roleId);
        
        $count = $userRole->count($where);
        $pagingParams = $this->pagingParams()->get($count);
        $rowset = $userRole->getAll($where, $pagingParams['offset'], $pagingParams['limit']);
        $userIds = array();
        foreach ($rowset as $row) {
            $userIds[] = $row->user_id;
        }
        
        $users = array();
        if ($userIds) {
            /* @var $user \HtUser\Model\User */
            $user = $sl->get('User');
            $users = $user->getAll(array('user_id' => $userIds));
        }
        
        $paginator = new Paginator(new PaginatorNull($count));
        $paginator->setCurrentPageNumber($pagingParams['page']);
        $paginator->setItemCountPerPage($pagingParams['limit']);
        
        $errorMessage = '';
        $successMessage = '';
        if ($this->flashMessenger()->hasErrorMessages()) {
            $errorMessage = implode('<br />', $this->flashMessenger()->getErrorMessages());
        }
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $successMessage = implode('<br />', $this->flashMessenger()->getSuccessMessages());
        }
        
        return array(
            'users' => $users,
            'count' => $count,
            'role' => $role,
            'paginator' => $paginator,
            'errorMessage' => $errorMessage,
            'successMessage' => $successMessage,
        );
    }
    
    /**
     * Delete role's user.
     */
    public function deleteUserAction()
    {
        $roleId = $this->params()->fromRoute('id');
        $sl = $this->getServiceLocator();
        
        /* @var $role Role */
        $role = $sl->get('HtApplication\Model\Acl\Role');
        if (!$role->load($roleId)) {
            $this->flashMessenger()->addErrorMessage($this->translate('No role found.'));
            return $this->redirect()->roRoute('admin/role');
        }
        
        /* @var $user \HtUser\Model\User */
        $user = $sl->get('User');
        
        $userId = $this->params()->fromRoute('userId');
        if (!$user->load($userId)) {
            $this->flashMessenger()->addErrorMessage($this->translate('No user found.'));
            return $this->redirect()->toRoute('admin/role/user', array('id' => $roleId));
        }
        
        /* check affected user has admin permission */
        if ($user->isAdmin() && !$this->isAdmin()) {
            $this->flashMessenger()->addErrorMessage($this->translate('You must have admin role to change role of another admin user.'));
            return $this->redirect()->toRoute('admin/role/user', array('id' => $roleId));
        }
        
        try {
            $connection = $this->getConnection();
            $connection->beginTransaction();
            /* @var $userRole \HtApplication\Model\Acl\UserRole */
            $userRole = $sl->get('HtApplication\Model\Acl\UserRole');
            $userRole->deleteRole($userId, $roleId);
            $connection->commit();
            $this->flashMessenger()->addSuccessMessage($this->translate('Delete user successfully.'));
            return $this->redirect()->toRoute('admin/role/user', array('id' => $roleId));
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }
    
    /**
     * Add user to role
     */
    public function addUserAction()
    {
        $roleId = $this->params()->fromRoute('id');
        $sl = $this->getServiceLocator();
        /* @var $role Role */
        $role = $sl->get('HtApplication\Model\Acl\Role');
        if (!$role->load($roleId)) {
            $this->flashMessenger()->addErrorMessage($this->translate('No role found.'));
            return $this->redirect()->toRoute('admin/role');
        }
        
        if ($role == Role::ADMIN_ROLE && !$this->isAdmin()) {
            $this->flashMessenger()->addErrorMessage('You must have admin role to assign admin role to another user.');
            return $this->redirect()->toRoute('admin/role/user', array('id' => $roleId));
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $userId = $request->getPost('user_id');
            $user = $sl->get('User');
            if (!$user->load($userId)) {
                $this->flashMessenger()->addErrorMessage($this->translate('No user found.'));
                return $this->redirect()->toRoute('admin/role/user', array('id' => $roleId));
            }
            
            try {
                $connection = $this->getConnection();
                $connection->beginTransaction();
                $user->addRole(array($roleId));
                $connection->commit();
                $this->flashMessenger()->addSuccessMessage($this->translate('Add role successfully.'));
                return $this->redirect()->toRoute('admin/role/user', array('id' => $roleId));
            } catch (\Exception $e) {
                $connection->rollback();
                throw $e;
            }
        }
    }
    
    /**
     * provide users for addUserAction
     * This method will filter user who has role_id
     * route: admin/role/user/id(roleId)/get-user?keyword=
     * @return JsonModel
     */
    public function getUserAction()
    {
        $roleId = $this->params('id');
        $keyword = $this->params()->fromQuery('keyword');
        
        $sl = $this->getServiceLocator();
        /* @var $userRoleCollection \HtApplication\Model\Acl\UserRoleCollection */
        $userRoleCollection = $sl->get('HtApplication\Model\Acl\UserRoleCollection');
        
        $where = $userRoleCollection->buildQueryJoinWithUserTable($roleId, $keyword);
        
        $user = $sl->get('User');
        $count = $user->count($where);
        $pagingParams = $this->pagingParams()->get($count);
        $users = $user->getAll($where, $pagingParams['offset'], $pagingParams['limit'])->toArray();
        
        return new JsonModel(array(
            'users' => $users,
            'total' => $count,
            'itemPerPage' => $pagingParams['limit'],
        ));
    }
    
}