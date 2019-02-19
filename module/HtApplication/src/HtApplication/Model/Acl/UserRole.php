<?php
namespace HtApplication\Model\Acl;
use AppCore\Mvc\Model\Model as AppCoreModel;
class UserRole extends AppCoreModel
{
	/**
	 * Get role by user id
	 *
	 * @param int $userId
	 * @return array
	 */
	public function getRoles($userId)
	{
		return $this->getTable()->getRoles($userId);
	}
	
	public function addRoles($userId, array $roles)
	{
		$this->getTable()->addRoles($userId, $roles);
	}
    
    /**
     * Delete role by user id, role id
     * @param int $userId
     * @param int $roleId
     * @return int
     */
    public function deleteRole($userId, $roleId)
    {
        return $this->getTable()->delete(array('user_id' => $userId, 'role_id' => $roleId));
    }
}