<?php

namespace HtAdmin\Controller;

use HtAdmin\Controller\AbstractUserRestfulController;
use Zend\View\Model\JsonModel;

class DeletedUserRestController extends AbstractUserRestfulController
{
    /**
     * Permanently delete a user.
     * @param int $id
     * @return JsonModel
     */
    public function delete($id)
    {
        $user = $this->getUser($id);
        if ($user instanceof JsonModel) {
            return $user;
        }
        
        try {
            $connection = $this->getConnection();
            $connection->beginTransaction();
            $user->hardDelete();
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            return $this->triggerException($e);
        }
        
        return new JsonModel(array(
            'success' => true,
            'message' => $this->translate('User has been permanently deleted.'),
            'user' => $user->toArray(),
        ));
    }
    
    /**
     * Get list deleted user.
     * @return JsonModel
     */
    public function getList()
    {
        /* @var $request \Zend\Http\PhpEnvironment\Request */
        $this->getRequest()->getQuery()->set('status', 'deleted');
        return parent::getList();
    }
    
    /**
     * restore a soft-deleted user.
     * @param int $id
     * @param mixed $data
     * @return \Zend\View\Model\JsonModel
     */
    public function update($id, $data)
    {
        $user = $this->getUser($id);
        if ($user instanceof JsonModel) {
            return $user;
        }
        
        if (!isset($data['restore'])) {
            return new JsonModel(array(
                'success' => false,
                'message' => $this->translate('Unsupported parameter.')
            ));
        } elseif ($data['restore'] != 'true') { /* data[restore] send by client is a string 'true', not is boolean*/
            return new JsonModel(array(
                'success' => false,
            ));
        }
        
        try {
            $connection = $this->getConnection();
            $connection->beginTransaction();
            $user->restore();
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            return $this->triggerException($e);
        }
        
        return new JsonModel(array(
            'success' => true,
            'message' => $this->translate('Restore user successfully.'),
            'user' => $user->toArray(),
        ));
    }
    
    /**
     * Return user model with feature Soft-delete has been disabled.
     * @return \HtUser\Model\User
     */
    protected function getUserModel()
    {
        $model = parent::getUserModel();
        $model->disableSoftDelete();
        return $model;
    }
}
