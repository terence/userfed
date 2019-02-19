<?php

namespace HtAdminTest\Controller;

use HtAdminTest\Controller\AbstractUserRestControllerTestCase;
use Zend\Stdlib\Parameters;
use HtApplication\Model\EmailTemplate;
use HtApplication\Model\Password;

class UserRestControllerTest extends AbstractUserRestControllerTestCase
{

    protected $baseUrl = '/rest-api/user';
    protected $classController = 'HtAdmin\Controller\UserRest';

    public function testCreateSuccess()
    {
        $this->reset();
        $this->loginAdmin();
        $id = $this->createRandomNumber();
        $email = $this->getUniqueEmail();

        $postData = new Parameters(array(
            'firstname' => 'Test user REST Api' . $id,
            'lastname' => 'Lastname ' . $id,
            'email' => $email,
            'email_confirm' => $email,
        ));

        $this->getRequest()->setMethod('POST')->setPost($postData);
        $this->dispatch($this->baseUrl);
        $responseObj = $this->getResponseObjectFromJson();
        $this->assertTrue($responseObj->success);
    }
    
    public function testCreateFailed()
    {
        $this->_testAclPermission($this->baseUrl);
        $this->reset();

        /* test controllerClass, action, validation */
        $this->loginAdmin();
        $request = $this->getRequest();
        $request->setMethod('POST');
        $this->dispatch($this->baseUrl);
        $this->assertControllerName($this->classController);
        $this->assertActionName('create');
        $this->assertResponseIsJson();
        $responseObj = $this->getResponseObjectFromJson();
        $this->assertFalse($responseObj->success);
    }

    public function testGetUser()
    {
        $this->getUserTestCase();
    }

    /**
     * 
     * @return \HtUser\Model\User
     */
    protected function getUserModel()
    {
        return $this->getApplicationServiceLocator()->get('User');
    }
    
    public function testUpdateFailed()
    {
        $this->_testAclPermission($this->baseUrl);
        $this->reset();
        
        /* test action, server-side validation */
        $user = $this->createUserWithInternalAuth();
        $this->loginAdmin();
        $request = $this->getRequest();
        $request->setMethod('PUT');
        $this->dispatch($this->baseUrl . '/' . $user->getUserId());
        $this->assertControllerName($this->classController);
        $this->assertActionName('update');
        $responseObj = $this->getResponseObjectFromJson();
        $this->assertFalse($responseObj->success);
    }
    
    public function testUpdateSuccess()
    {
        $this->loginAdmin();
        /* @var $request \Zend\Http\PhpEnvironment\Request */
        $user = $this->createUserWithInternalAuth();
        $updateData = new Parameters(array(
            'firstname' => sprintf('Update user %s by REST API', $user->getUserId()),
            'lastname' => sprintf('lastname %s', $user->getUserId()),
            'is_enabled' => 0
        ));
        
        $request = $this->getRequest();
        $request->setMethod('PUT')
                ->setContent($updateData->toString());
        $this->dispatch($this->baseUrl . '/' . $user->getUserId());
        $responseObj = $this->getResponseObjectFromJson();
        $this->assertTrue($responseObj->success);
    }
    
    public function testGeneratePassword()
    {
        $this->loginAdmin();
        $user = $this->createUserWithInternalAuth();
        
        $this->assertSendMailGeneratePassword($user->getEmail());
        $url = $this->fromRoute('rest-api/user', array('id' => $user->getUserId()));
        
        $this->dispatch($url, 'PUT', array(
            'action' => 'generate-password'
        ));
    }
    
    /**
     * Assert send mail
     * @param string $email Email will receive notification.
     * @param string $password Password will set for user.
     */
    protected function assertSendMailGeneratePassword($email, $password = null)
    {
        if (!$password) {
            $passwordModel = new Password();
            $password = $passwordModel->generatePassword(10);
        }
        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);
        
        /**
         * Mock user model to return a foreknow password when call generatePassword method.
         */
        $user = $sl->get('User');
        $mockUser = $this->getMock(get_class($user), array('generatePassword'));
        $mockUser->expects($this->any())
                ->method('generatePassword')
                ->willReturn($password);
        
        $factory = function ($sm) use ($mockUser) {
            $mockUser->setTable($sm->get('HtUser\Model\UserTable'));
            $mockUser->setServiceManager($sm);
            return $mockUser;
        };
        
        $sl->setFactory('User', $factory);
        
        /**
         * Mock mail model
         * Ensure method send will be called.
         */
        $emailTemplateModel = $sl->get("HtApplication\Model\EmailTemplate");
        $mailTemplate = $emailTemplateModel->buildMessage(EmailTemplate::ADMIN_REGENERATE_PASSWORD, array(
            'recipient_name' => $email,
            'new_password' => $password,
            'url_site' => $this->fromRoute('home')
        ));
        /* @var $mail \HtApplication\Model\Mail */
        $mail = $sl->get('HtApplication\Model\Mail');
        $mockMail = $this->getMock(get_class($mail), array('send'));
        
        $mockMail->expects($this->once())
                ->method('send')
                ->with($mailTemplate['subject'], $mailTemplate['body'], $email, $email);
        
        $sl->setService('HtApplication\Model\Mail', $mockMail);
    }
}
