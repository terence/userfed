<?php
/**
 *  Manage internal login using email and password
 */
namespace HtAuthentication\Controller;

use HtApplication\Controller\AbstractActionController;
use HtUser\Model\User;
use HtAuthentication\Form\Register as RegisterForm;
use HtApplication\Model\EmailTemplate;
use HtApplication\Model\Log;
use HtAuthentication\Model\AuthenticationAccount;
use HtAuthentication\Form\Login;
use Zend\Filter\StringTrim;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use HtAuthentication\Validator\CheckUniqueModelProperty;

class InternalController extends AbstractActionController
{
	/** Test git diff */
	public function loginAction()
	{
        if ($this->hasIdentity()) {
			return $this->redirect()->toUrl('/');
		}
        
		$errorMessage = "";
		$form = new Login();
		$request = $this->getRequest();
		if ($request->isPost()) {
			$sl = $this->getServiceLocator();
			$data = $request->getPost()->toArray();
			$filter = new StringTrim();
			$email = $filter->filter($data['email']);
			$password = $data['password'];

			//Set up the authentication adapter
			$auth = $this->getAuthenticationService();
			/* @var $authAdapter \HtAuthentication\Model\Adapter\Internal*/
			$authAdapter = $this->getServiceLocator()->get('AuthAccountInternal');
			$authAdapter->setPassword($password);
			$authAdapter->setUsername($email);
			$auth->setAdapter($authAdapter);
			$result = $auth->authenticate();
			if (!$result->isValid()) {
				$errorMessage = implode("<br/>", $result->getMessages());
				$user = $sl->get('User');
				/* @var $user \HtUser\Model\User */
				$result = $user->getOne(array('email' => $email));
				if ($result) {
					$this->writeLog(Log::TYPE_LOGIN_FAILED, $this->translate('Password invalid.'), array('user_id' => $result->getUserId()));
				}
                return new JsonModel(array(
                    'error' => true,
                    'message' => $errorMessage,
                ));
			} else {
				/* @var $sessionManager \Zend\Session\SessionManager */
				$sessionManager = $sl->get("Zend\Session\SessionManager");
				//Check if remember me checkbox is checked
				if(array_key_exists('remember', $data) && $data['remember'] === 1) {
					$sessionManager->rememberMe(2 * 3600);//2 hours
				} else {
					$sessionManager->forgetMe();
				}
				$this->logUserIn($result->getIdentity(), $auth);
				$this->writeLog(Log::TYPE_LOGIN_SUCCESS, $this->translate('Successful Login attempts.'));
				return new JsonModel(array(
                    'error' => false,
                    'redirectTo' => '/access',
                ));
			}
		}
		
		$this->layout('layout/layout-plain');
		if (empty($errorMessage) && $this->flashMessenger()->hasErrorMessages()) {
			$errorMessage = implode('<br />', $this->flashMessenger()->getErrorMessages());
		}
		
		return array(
            'form' => $form,
            'errorMessage' => $errorMessage
		);
	}
	
	public function registerAction()
	{
		if ($this->hasIdentity()) {
			return $this->redirect()->toUrl('/');
		}
		
		$form = new RegisterForm();
		$request = $this->getRequest();
		if ($request->isPost()) {
			$sl = $this->getServiceLocator();
			/* @var $authInternal \HtAuthentication\Model\Adapter\Internal */
			$authInternal = $sl->get('AuthAccountInternal');
			$form->setInputFilter($authInternal->getInputFilter());
			$form->setData($request->getPost());
			if ($form->isValid()) {
				$connection = $this->getConnection();
				$data = $form->getData();
				try {
					$connection->beginTransaction();
					//create user
					/* @var $user \HtUser\Model\User */
					$user = $sl->get('User');
					$user->exchangeArray($data);
					$user->registerMemberAccount();

					$authInternal->exchangeArray(array(
						'username' => $data['email'],
						'password' => $data['password']
					));
					$authInternal->register();
					
					$authInternal->linkUser($user);

					//send activation mail
					$name = $data['firstname'] . " " . $data['lastname'];

					$this->sendActivationMail($name, $data['email'], $authInternal->getActivationKey());
					$this->writeLog(
                        Log::TYPE_REGISTER_SUCCESS,
                        $this->translate('Successful Registrations.'),
                        array(
                            'user_id' => $user->getUserId()
                        )
                    );
					$connection->commit();
                    return new JsonModel(array(
                        'error' => false,
                        'message' => $this->translate('An email with an activation link has been sent to your email address. Please check your email and click the link to complete your registration.'),
                    ));
				} catch (\Exception $e) {
					$connection->rollback();
                    return new JsonModel(array(
                        'error' => true,
                        'message' => $this->translate('Error occurred. Please try again later.'),
                    ));
				}
            } else {
                $errors = $form->getMessages();
                $message = "";
                foreach ($errors as $error) {
                    if (is_array($error)) {
                        $message .= implode("<br>",$error);
                    } else {
                        $message .= "$error<br>";
                    }
                }
                return new JsonModel(array(
                    'error' => true,
                    'message' => $message,
                ));
            }
		}
		
		$this->layout('layout/layout-plain');
		
		return array(
            'form' => $form,
		);
	}
	
	/**
	 * @param string $recipientName
	 * @param string $email
	 * @param string $key
	 */
	protected function sendActivationMail($recipientName, $email, $key)
	{
		/* @var $mailModel \HtApplication\Model\Mail */
		$sl = $this->getServiceLocator();
		$mailModel = $sl->get("HtApplication\Model\Mail");
		/* @var $emailTemplateModel \HtApplication\Model\EmailTemplate */
		$emailTemplateModel = $sl->get("HtApplication\Model\EmailTemplate");
		$recipentAddress = $email;
		$activationLink = $this->url()->fromRoute(
                'internal',
                array('action' => 'activate'),
                array('query' => array('key' => $key),'force_canonical' => true));
	
		$mailTemplate = $emailTemplateModel->buildMessage(EmailTemplate::ACTIVATION_MAIL_TEMPLATE,
			array(
				'recipient_name' => $recipientName,
				'activation_link' => $activationLink
			)
		);
		$mailModel->send($mailTemplate['subject'], $mailTemplate['body'], $recipentAddress, $recipientName);
	}
	
	/**
	 * Activation an internal account.
	 */
	public function activateAction()
	{
		if ($this->hasIdentity()) {
			return $this->redirect()->toUrl('/');
		}
		
		$sl = $this->getServiceLocator();
		$key = $this->getRequest()->getQuery('key', false);
		/* @var $userCollection \HtUser\Model\UserCollection */
		$authInternal = $sl->get('AuthAccountInternal');
		/* @var $authInternal \HtAuthentication\Model\Adapter\Internal */
		$internalLogin = $authInternal->getOne(array('activation_key' => $key));

		if (!$internalLogin || !$key) {
			return array('errorMessage' => $this->translate('Invalid activation key.'));
		}

		if ($internalLogin->getIsActivated()) {
			return array('errorMessage' => $this->translate('User is already activated.'));
		}

		$authAccount = $sl->get('AuthAccount');
		/* @var $authAccount \HtAuthentication\Model\AuthenticationAccount */

		if (!$authAccount->load($internalLogin->getAuthenticationAccountId())) {
			return array('errorMessage' => $this->translate('Not found authentication account.'));
		}

		/* @var $user \HtUser\Model\User */
		$user = $sl->get('User');

		if (!$user->load($authAccount->getUserId())) {
			return array('errorMessage' => $this->translate('Not found user.'));
		}

		$connection = $this->getConnection();
		try {
			$connection->beginTransaction();
			$user->setIsEnabled(true);
			$user->save();
			$internalLogin->setIsActivated(true);
			$internalLogin->save();

			/* @var $userApp \HtAuthentication\Model\UserApplication */
			$userApp = $sl->get('UserApp');
			$userApp->setDefaultApplicationAndServer($user);

			//@todo: may make a separate method for this and move Register and Login controller to HtAuthentication
			//auto log user in
			//Set up the authentication adapter
			$identity = $user->toStdClass();
			$identity->authenticationProvider = AuthenticationAccount::TYPE_INTERNAL;

			$this->logUserIn($identity);

			$this->writeLog(Log::TYPE_LOGIN_SUCCESS, $this->translate('Activated internal login success.'));
			$connection->commit();
		} catch(\Exception $e) {
			$connection->rollback();
			throw $e;
		}

		$this->flashMessenger()->addSuccessMessage($this->translate('Activate successfully! Please start create profile'));
		return $this->redirect()->toRoute('profile');
	}
	
	/**
	 * Add internal login
	 */
	public function addAction()
	{		
		$sl = $this->getServiceLocator();
		$registerForm = new RegisterForm();
		$form = $registerForm->getAddLogin();
		$request = $this->getRequest();
		
		$authAccount = $sl->get('AuthAccount');
		/* @var $authAccount \HtAuthentication\Model\AuthenticationAccount */
		$count = $authAccount->count(array('user_id' => $this->getLoggedInUserId(), 'type' => AuthenticationAccount::TYPE_INTERNAL));
		if ($count > 0) {
			return $this->gotoLoginManagementPage($this->translate('Your internal login already created.'));
		}
		
		if (!$request->isPost()) {
			return array(
				'form' => $form,
			);
		}
		
		$form->setData($request->getPost());

		/* @var $authInternal \HtAuthentication\Model\Adapter\Internal */
		$authInternal = $sl->get('AuthAccountInternal');

		$inputFilter = $authInternal->getFitlerForAddLogin();
		$form->setInputFilter($inputFilter);

		if (!$form->isValid()) {
			return array(
				'form' => $form,
			);
		}
		
		$connection = $this->getConnection();
		$data = $form->getData();
		$data['username'] = $data['email'];
		if ($authInternal->count(array('username' => $data['email'])) > 0) {
			return array(
				'form' => $form,
				'errorMessage' => $this->translate('This email address was already used by another.'),
			);
		} else {
			try {
				$connection->beginTransaction();
				$authInternal->exchangeArray($data)
						->save();
				$authInternal->linkUser($this->getLoggedInUserId());
				$authInternal->register(false);

				$message = $this->translate('Add internal login successfully.');
				$this->writeLog(Log::TYPE_ADD_LOGIN_SUCCESS, $message);

				$connection->commit();
				return $this->gotoLoginManagementPage($message, false);
			} catch(\Exception $e) {
				$connection->rollback();
				throw $e;
			}
		}
	}
	
	/**
	 * Check duplicate email for XmlHttpRequest
	 *
	 * @return \Zend\View\Model\ViewModel
	 */
	public function validateUniqueFieldAction()
	{
		$fields	= array('username');
		$field = $this->getRequest()->getQuery('field', false);
		
		if(!in_array($field, $fields)) {
			return new JsonModel(array(
					'error' => true,
					'msg'	=> 'This operation is not allowed',
			));
		}
		$value = $this->getRequest()->getQuery('value', false);
		$sl = $this->getServiceLocator();
		
		/* @var $authenticationInternal \HtAuthentication\Model\Adapter\Internal */
		$authenticationInternal = $sl->get('AuthAccountInternal');
		$validator = new CheckUniqueModelProperty();
		$validator->setModel($authenticationInternal)
				->setProperty($field);
		$model = new JsonModel();
		if (!$validator->isValid($value)) {
			$model->setVariable("unique", false);
            return $model;
		}
        
        /**
         * Add login action we just check internal table.
         */
        if ($this->hasIdentity() && $field == 'username') {
            $model->setVariable("unique", true);
            return $model;
        }
        
        /**
        * With user create by oauth adapter like Google, we have email in user
        * that email doesn't use for authentication so don't exist in Internal Adapter
        * That's why we need to check here.
        */
        /* @var $user User */
        $user = $sl->get('User');
        $user->disableSoftDelete();
        $validator->setModel($user);
        $validator->setProperty('email');
        if (!$validator->isValid($value)) {
            $model->setVariable("unique", false);
        } else {
            $model->setVariable("unique", true);
        }
        
		return $model;
	}
	
	public function deleteAction()
	{
		$sl = $this->getServiceLocator();
		$identity = $this->getAuthenticationService()->getIdentity();
		
		if ($identity->authenticationProvider == AuthenticationAccount::TYPE_INTERNAL) {
			return $this->gotoLoginManagementPage($this->translate('You cannot delete currently login.'));
		}
		
		/* @var $authAccout \HtAuthentication\Model\AuthenticationAccount */
		$authAccout = $sl->get('AuthAccount');
		$userId = $identity->userId;
		
		$authAccout->setUserId($userId);
		$internal = $authAccout->getInternalLogin();
		if (!$internal) {
			return $this->gotoLoginManagementPage($this->translate('You do not have internal login.'));
		}
		
		$connection = $this->getConnection();
		try {
			$connection->beginTransaction();
			$internal->delete();
			$authAccout->delete();
			$message = $this->translate('Delete your internal login successfully.');
			$this->writeLog(Log::TYPE_DELETE_LOGIN_SUCCESS, $message);
			$connection->commit();
			return $this->gotoLoginManagementPage($message, false);
		} catch(\Exception $e) {
			$connection->rollback();
			throw $e;
		}
	}
	
	/**
	 * Add error messages to flashMessager and redirect to
	 * login management page
	 * @param string $errorMessage
	 * @return \Zend\Http\Response
	 */
	protected function gotoLoginManagementPage($message, $error = true)
	{
		if ($error) {
			$this->flashMessenger()->addErrorMessage($message);
		} else {
			$this->flashMessenger()->addSuccessMessage($message);
		}
		return $this->redirect()->toRoute('login-management');
	}
	
	public function updateAction()
	{
		$sl = $this->getServiceLocator();
		$errorMessage = '';
		$successMessage = '';
		$form = new RegisterForm();
		$form = $form->getUpdateLogin();
		
		/* @var $authAccount \HtAuthentication\Model\AuthenticationAccount */
		$authAccount = $sl->get('AuthAccount');
		
		$authAccount->setUserId($this->getLoggedInUserId());
		$authInternal = $authAccount->getInternalLogin();
		if (!$authInternal) {
			return $this->gotoLoginManagementPage($this->translate('You do not have internal login.'));
		}
		
		$request = $this->getRequest();
		
		if ($request->isPost()) {
			$data = $request->getPost();
			$inputFilter = $authInternal->getFilterForUpdate();
			$form->setData($data);
			$form->setInputFilter($inputFilter);
			if ($form->isValid()) {				
				$connection = $this->getConnection();
				try {
					$connection->beginTransaction();
					$data = $form->getData();
					/* check current password */
					$currentPassword = $authInternal->createHashPassword($data['current_password']);
					if ($currentPassword != $authInternal->getPassword()) {
						$errorMessage = $this->translate('Wrong current passworld.');
						$this->writeLog(Log::TYPE_CHANGE_PASSWORD_FAILED, $errorMessage);
					} else {
						$authInternal->setPassword($authInternal->createHashPassword($data['password']));
						$authInternal->save();
						$successMessage = $this->translate('Change password success.');
						$this->writeLog(Log::TYPE_CHANGE_PASSWORD_SUCCESS, $successMessage);
					}
					$connection->commit();
				} catch (\Exception $e) {
					$connection->rollback();
					throw $e;
				}
			}
		}
		
		return array(
			'form' => $form,
			'errorMessage' => $errorMessage,
			'successMessage' => $successMessage
		);
	}
}
