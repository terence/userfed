<?php

namespace HtAdmin\Controller;

use HtAdmin\Controller\AbstractUserRestfulController;
use Zend\View\Model\JsonModel;

use HtAdmin\Form\CreateUser;
use HtAdmin\Form\EditUser;
use HtApplication\Model\Acl\Role;
use HtApplication\Model\EmailTemplate;

class UserRestController extends AbstractUserRestfulController
{
    /**
     * Create new user.
     * @param mixed $data
     */
    public function create($data)
    {
        $sl = $this->getServiceLocator();
        /* @var $user \HtUser\Model\User */
        $user = $sl->get('User');
        
        $form = new CreateUser();
        $form->setData($data);
        /* @var $authInternal \HtAuthentication\Model\Adapter\Internal */
        $authInternal = $sl->get('AuthAccount\Internal');
        $inputFilter = $authInternal->getFilterForAdmin();
        $form->setInputFilter($inputFilter);
        
        if (!$form->isValid()) {
            return new JsonModel(array(
                'success' => false,
                'message' => $form->getMessages(),
            ));
        }
        
        $user->exchangeArray($data);
        $user->setUserId(null);
        $user->setIsEnabled(true);
        $connection = $this->getConnection();
        
        try {
            $connection->beginTransaction();
            $userId = $user->save();

            $user->addRole(Role::MEMBER_ROLE);
            $password = $authInternal->genaratePassword();
            $data['password'] = $password;
            $data['username'] = $user->getEmail();

            $authInternal->exchangeArray($data);
            $authInternal->register(false);
            $authInternal->linkUser($user);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            return $this->triggerException($e);
        }

        $this->sendMail($user->getEmail(), $password);
        
        return new JsonModel(array(
            'success' => true,
            'user' => $user->toArray(),
            'message' => $this->translate('Created user success. Please add one or more application this user can access.'),
        ));
    }
    
    /**
     * 
     * @param int $id
     * @return \Zend\View\Model\JsonModel
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
            $user->delete();
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            return $this->triggerException($e);
        }
        
        return new JsonModel(array(
            'success' => true,
            'user' => $user,
        ));
    }
    
    /**
     * Update an existing user
     * @param int $id
     * @param mixed $data
     * @return JsonModel
     */
    public function update($id, $data)
    {
        if (isset($data['action']) && $data['action'] == 'generate-password') {
            return $this->generatePassword($id);
        }
        
        $user = $this->getUser($id);
        if ($user instanceof JsonModel) {
            return $user;
        }
        
        /* @var $internal \HtAuthentication\Model\Adapter\Internal */
        $internal = $this->getServiceLocator()->get('AuthAccount\Internal');
        $inputFilter = $internal->getFilterForAdminUpdate();
        $form = new EditUser();
        $form->setData($data);
        $form->setInputFilter($inputFilter);
        if (!$form->isValid()) {
            return new JsonModel(array(
                'success' => false,
                'message' => $form->getMessages()
            ));
        }
        
        try {
            $connection = $this->getConnection();
            $connection->beginTransaction();
            $user->exchangeArray($data);
            $user->save();
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            return $this->triggerException($e);
        }
        
        return new JsonModel(array(
            'success' => true,
            'message' => $this->translate('Update user info successfully.'),
        ));
    }
    
    /**
     * Delete Multiple user.
     * @return \Zend\View\Model\JsonModel
     */
    public function deleteList()
    {
        $data = $this->processBodyContent($this->getRequest());
        
        $ids = isset($data['ids']) ? $data['ids'] : null;
        
        if (empty($ids)) {
            return new JsonModel();
        }
        
        $sl = $this->getServiceLocator();
        /* @var $collection \HtUser\Model\UserCollection */
        $collection = $sl->get('UserCollection');
        //@todo : exclude admin users here
        $items = $collection->getAll(array('user.user_id' => $ids));
        /* @var $user UserModel */
        $user = $sl->get("User");
        $connection = $this->getConnection();
        try {
            $connection->beginTransaction();
            foreach ($items as $item) {
                $user->exchangeArray($item);
                $user->delete();
            }
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            return $this->triggerException($e);
        }
        
        return new JsonModel(array(
            'success' => true,
        ));
    }
    
    /**
     * 
     * @param string $email
     * @param string $password
     * @param string $template Email template code
     */
    protected function sendMail($email, $password, $template = EmailTemplate::ADMIN_CREATE_USER_MAIL_TEMPLATE)
    {
        /* @var $mailModel \HtApplication\Model\Mail */
        $sl = $this->getServiceLocator();
        $mailModel = $sl->get("HtApplication\Model\Mail");
        /* @var $emailTemplateModel \HtApplication\Model\EmailTemplate */
        $emailTemplateModel = $sl->get("HtApplication\Model\EmailTemplate");
        $urlSite = $this->url()->fromRoute('home', array(), array('force_canonical' => true));
        $params = array();
        if ($template == EmailTemplate::ADMIN_CREATE_USER_MAIL_TEMPLATE) {
            $params = array(
                'recipient_name' => $email,
                'login_email' => $email,
                'url_site' => $urlSite,
                'login_password' => $password
            );
        } else if ($template == EmailTemplate::ADMIN_REGENERATE_PASSWORD) {
            $params = array(
                'recipient_name' => $email,
                'new_password' => $password,
                'url_site' => $urlSite
            );
        }
        $mailTemplate = $emailTemplateModel->buildMessage($template, $params);
        $mailModel->send($mailTemplate['subject'], $mailTemplate['body'], $email, $email);
    }
    
    /**
     * generate new password and send notification to user.
     * @param int $userId
     * @return \Zend\View\Model\JsonModel
     */
    protected function generatePassword($userId)
    {
        $user = $this->getUser($userId);
        if ($user instanceof JsonModel) {
            return $user;
        }
        
        try {
            $connection = $this->getConnection();
            $connection->beginTransaction();
            $password = $user->generatePassword();
            
            /**
             * We should change password when we can sent email bring new password to user.
             * So we should place sendMail method in transaction.
             */
            $this->sendMail($user->getEmail(), $password, EmailTemplate::ADMIN_REGENERATE_PASSWORD);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            return $this->triggerException($e);
        }

        return new JsonModel(array(
            'success' => true,
        ));
    }
}
