<?php

namespace HtAdminTest\Controller;

use HtAdmin\Test\AbstractHttpControllerTestCase;
use HtUser\Model\User;

abstract class AbstractUserRestControllerTestCase extends AbstractHttpControllerTestCase
{
    protected $baseUrl;
    protected $classController;
    
    /**
     * @return User
     */
    abstract protected function getUserModel();

    public function testGetListUser()
    {
        $this->_testAclPermission($this->baseUrl);
        $this->reset();
        
        $this->loginAdmin();
        $this->dispatch($this->baseUrl);
        $this->assertControllerName($this->classController);
        $this->assertActionName('getList');
        $this->assertResponseStatusCode(200);
        $this->assertResponseIsJson();
    }
    
    protected function getUserTestCase(User $user = null)
    {
        $this->_testAclPermission($this->baseUrl);
        $this->reset();
        
        if ($user === null) {
            $user = $this->createUserWithInternalAuth();
        }
        
        $this->loginAdmin();
        $this->dispatch($this->baseUrl . '/' . $user->getUserId());
        $this->assertControllerName($this->classController);
        $this->assertActionName('get');
        $this->assertResponseStatusCode(200);
        $this->assertResponseIsJson();
        
        $responseObj = $this->getResponseObjectFromJson();
        $this->assertEquals($responseObj->user->user_id, $user->getUserId());
    }
    
    public function testDeleteUser()
    {
        /* @var $request \Zend\Http\PhpEnvironment\Request */
        $this->loginAdmin();
        $request = $this->getRequest();
        $request->setMethod('DELETE');
        $user = $this->createUserWithInternalAuth();
        $this->dispatch($this->baseUrl . '/' . $user->getUserId());
        $this->assertControllerName($this->classController);
        $this->assertActionName('delete');
        $this->assertResponseStatusCode(200);
        $this->assertResponseIsJson();
        
        $userModel = $this->getUserModel();
        $this->assertFalse($userModel->load($user->getUserId()));
    }
}
