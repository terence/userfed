<?php

namespace HtAdminTest\Controller;

use HtAdminTest\Controller\AbstractUserRestControllerTestCase;

class DeletedUserRestControllerTest extends AbstractUserRestControllerTestCase
{

    protected $baseUrl = '/rest-api/deleted-user';
    protected $classController = 'HtAdmin\Controller\DeletedUserRest';

    public function testGetUser()
    {
        $user = $this->createUserWithInternalAuth();
        $user->delete();
        /* Ensure user has been deleted. */
        $this->assertFalse($user->load($user->getUserId()));
        $this->getUserTestCase($user);
    }

    /**
     * Return user model with feature Soft-delete has been disabled.
     * @return \HtUser\Model\User
     */
    protected function getUserModel()
    {
        /* @var $model \HtUser\Model\User */
        $model = $this->getApplicationServiceLocator()->get('User');
        $model->disableSoftDelete();
        return $model;
    }
    
    public function testUpdateFailed()
    {
        $this->loginAdmin();
        $user = $this->createUserWithInternalAuth();
        $user->delete();
        $this->assertFalse($user->load($user->getUserId()));
        $this->dispatch($this->baseUrl . '/' . $user->getUserId(), 'PUT', array('firstname' => 'Update deleted user'));
        $responseObj = $this->getResponseObjectFromJson();
        $this->assertFalse($responseObj->success);
    }
    
    /**
     * restore soft-deleted user
     */
    public function testUpdateSuccess()
    {
        /* test db exception */
        $this->loginAdmin();
        $user = $this->createUserWithInternalAuth();
        $user->delete();
        /* ensure user has been deleted */
        $this->assertFalse($user->load($user->getUserId()));
        $this->dispatch($this->baseUrl . '/' . $user->getUserId(), 'PUT', array('restore' => 'true'));
        $this->assertResponseIsJson();
        $responseObj = $this->getResponseObjectFromJson();
        
        $this->assertTrue($responseObj->success);
        $this->assertTrue($user->load($user->getUserId()));
    }
}
