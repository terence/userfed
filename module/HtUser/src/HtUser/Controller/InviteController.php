<?php

namespace HtUser\Controller;

use HtApplication\Model\Log;
use Zend\Validator\EmailAddress;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

use HtAuthentication\Model\Adapter\OAuth;
use HtAuthentication\Model\AuthenticationAccount;
use ReverseOAuth2\OAuth2HttpClient;

use HtUser\Form\InviteActivate as ActivateForm;
use HtUser\Form\InviteActivateFilter;
use HtUser\Model\User;

use Zend\Db\ResultSet\ResultSet;

use HtApplication\Model\EmailTemplate;
use HtApplication\Model\Mail;

use HtApplication\Controller\OAuthBaseController;

class InviteController extends OAuthBaseController
{
	const INVITE_EMAIL_TEMPLATE = 'invite_email_template';
	public function inviteAction()
	{
		//validate server's token and invite email
		$email = $this->params()->fromQuery('email');
		$validator = new EmailAddress();
		if (!$validator->isValid($email)) {
			return new JsonModel(array(
				'errorCode' => 1,
				'errorMessage' => $this->translate('Email is invalid.')
			));
		}
		$secret = $this->params()->fromQuery('secret');
		
		$sl = $this->getServiceLocator();
		$appServerCollection = $sl->get('AppServerCollection');
		/* @var $appServerCollection \HtAuthentication\Model\ApplicationServerCollection */
		$appServer = $appServerCollection->getBySeverSecret($secret); // check secret of server
		/* @var $appServer \HtAuthentication\Model\ApplicationServer */
		$invitationCode = null;
		if (!$appServer) {
			return new JsonModel(array(
				'errorCode' => 1,
				'errorMessage' => $this->translate('Access denied.')
			));
		}
		
		$appId = $appServer->getApplicationId();
		$serverId = $appServer->getServerId();
		
		/* If user with invited email exists then add invited application*/
		/* @var $user \HtUser\Model\User */
		$user = $sl->get('User')
					->getOne(array('email' => $email));
		if ($user) {
			/* @var $userApp \HtAuthentication\Model\UserApplication */
			$userApp = $sl->get('UserApp');
			$userApp->setUserId($user->getUserId());
			
			$connection = $this->getConnection();
			try {
				$connection->beginTransaction();

				if ($userApp->hasAccess($appId, $serverId) > 0) {
					$connection->rollback();
					return new JsonModel(array(
						'errorCode' => 1,
						'errorMessage' => $this->translate('User with this email already had access permission.')
					));
				}
				$userApp->addAccess($appId, $serverId);			
				$connection->commit();
				return new JsonModel(array(
					'errorCode' => 0,
				));
			} catch (\Exception $e) {
				$connection->rollback();
				
				//@todo: write log file, send email log
				return new JsonModel(array(
					'errorCode' => 1,
					'errorMessage' => $this->translate('System error.'),
				));
			}

		}
		
		/* Should send invite case: new invited app, old invited app with expired invite time */
		try {
			$connection = $this->getConnection();
			$connection->beginTransaction();
			//No user found with email
			/* @var $invite \HtUser\Model\Invite */
			$invite = $sl->get('Invite');
			/* Create invitation if not existi*/
			if (!$invite->loadByEmail($email)) {
				$invite->createByEmail($email);			
			} else {
				/* There is existing invitation but not expired yet*/
				if ($invite->isAlreadyInviteBy($appId, $serverId) && !$invite->isInvitationExpired()) {
					/* this application invited an email existed (not expired.) */
					return new JsonModel(array(
						'errorCode' => 1,
						'errorMessage' => $this->translate('This email is already invited.')
					));
				}			
			}

			if ($invite->invite($appId, $serverId)) {
				/* @var $application \HtAuthentication\Model\Application */
				$application = $sl->get('App');
				$application->load($appServer->getApplicationId());
				$applicationName = $application->getTitle();

				$invitationCode = $invite->getCode();
				$this->sendMail($email, $applicationName, $invitationCode);
			}
			$connection->commit();
		} catch(\Exception $e) {
			$connection->rollback();
			/**
			* @todo Using Trigger an event.
			*/
			throw $e;
		}

		return new JsonModel(array(
			'errorCode' => 0,
			'invitationCode' => $invitationCode
		));
	}
	
	/**
	 * Send invited mail for user.
	 * @param string $email
	 * @param string $applicationName
	 * @param string $activateCode
	 */
	protected function sendMail($email, $applicationName, $activateCode)
	{
		$sl = $this->getServiceLocator();
		$mail = $sl->get('HtApplication\Model\Mail');
		/* @var $mail \HtApplication\Model\Mail */
		$emailTemplate = $sl->get('HtApplication\Model\EmailTemplate');
		$linkActivate = $this->url()->fromRoute(
                        'invite-user',
                        array('action' => 'register-with-invitation-code'),
                        array('query' => array('code' => $activateCode), 'force_canonical' => true)
                        );
		/* @var $emailTemplate \HtApplication\Model\EmailTemplate */
		$emailMessage = $emailTemplate->buildMessage(self::INVITE_EMAIL_TEMPLATE, array(
			'recipient_name' => $email,
			'application_name' => $applicationName,
			'link_activate' => $linkActivate
		));
		$mail->send($emailMessage['subject'], $emailMessage['body'], $email, $email);
	}
	
	/**
	 * User's account is created through API from client app.
	 * Client app send invitation code to user's email.
	 * User clicks invitation code in email to enable the account
	 * 
	 * @return \Zend\View\Model\ViewModel
	 */
	public function activateAction()
	{
		$code = $this->params()->fromQuery('code');
		
		$invite = $this->checkInvitedCode($code);
		if ($invite instanceof ViewModel) {
			return $invite;
		}
		
		//enable user and user's internal login account of this invitation
		//if there is user linked to email		
		$sl = $this->getServiceLocator();
		/* @var $user \HtUser\Model\User */
		$user = $sl->get('User');
		if ($user->loadByEmail($invite->getEmail())) {
			if (!$user->getIsEnabled()) {
				try {
					$connection = $this->getConnection();
					$connection->beginTransaction();
					$user->setIsEnabled(true);
					$user->save();
					/* @var $internal \HtAuthentication\Model\Adapter\Internal */
					$internal = $sl->get('AuthAccountInternal');
					if ($internal->loadByUser($user)) {
						$internal->setIsActivated(true);
						$internal->save();
					}
					$this->logIn($user);
					$connection->commit();
					return $this->redirect()->toRoute('profile');
				} catch (\Exception $e) {
					$connection->rollback();
					throw $e;
				}
			} else {
				$errorMessage = $this->translate('You already registered an account with this invitation code.');
				$helpText = $this->translate('Please login using your registered account <br />'
						. '<strong>OR</strong>'
						. '<br />Contact administrator for support.');
				return $this->showErrorActivate($errorMessage, $helpText);
			}
		}
	}
	
	protected function showErrorActivate($errorMessage, $helpText, $code = '', $email = '')
	{
		$viewModel = new ViewModel(array(
			'errorActivateCode' => true,
			'errorMessage' => $errorMessage,
			'helpText' => $helpText,
			'email' => $email,
			'code' => $code
		));
		$viewModel->setTemplate('ht-user/invite/activate');
		return $viewModel;
	}
	
	/**
	 * Client app call API to send invitation to user's email.
	 * User clicks invitation link in email to create account.
	 * 
	 * @return type
	 * @throws \HtUser\Controller\Exception
	 */
	public function registerWithInvitationCodeAction()
	{
		$code = $this->params()->fromQuery('code');
		
		$invite = $this->checkInvitedCode($code);
		if ($invite instanceof ViewModel) {
			$invite->setTemplate('ht-user/invite/activate');
			return $invite;
		}
		
		$sl = $this->getServiceLocator();
		/* @var $user \HtUser\Model\User */
		$user = $sl->get('User');
		if ($user->loadByEmail($invite->getEmail())) {
			$errorMessage = $this->translate('You already registered an account with this invitation code.');
				$helpText = $this->translate('Please login using your registered account <br />'
						. '<strong>OR</strong>'
						. '<br />Contact administrator for support.');
				return $this->showErrorActivate($errorMessage, $helpText);
		}
		
		//there is no user linked with email
		/* @var $invite \HtUser\Model\Invite */
		$form = new ActivateForm();
		$email = $invite->getEmail();
		$errorCode = 0;
		$successMessage = '';
		$erroMessage = '';
		
		$request = $this->getRequest();
		if ($request->isPost()) {
			$data = $request->getPost();
			$form->setData($data);
			$inviteActivateFilter = new InviteActivateFilter();
			$form->setInputFilter($inviteActivateFilter->getInputFilter());
			if ($form->isValid()) {
				$data = $form->getData();
				$connection = $this->getConnection();
				try {
					$connection->beginTransaction();
					$requiredActivation = false;
					
					$data['email'] = $email;
					$user->exchangeArray($data);
					$user->registerMemberAccount($requiredActivation);
					
					$authInternal = $sl->get('AuthAccountInternal');
					/* @var $authInternal \HtAuthentication\Model\Adapter\Internal */
					$data['username'] = $email;
					$authInternal->exchangeArray($data);
					$authInternal->register($requiredActivation);
					$authInternal->linkUser($user);
					
					/* @var $userApp \HtAuthentication\Model\UserApplication */
					$userApp = $sl->get('UserApp');
					$userApp->setUserId($user->getUserId());
					$userApp->addAccessByInvite($invite->getInviteId());

					$this->logIn($user);
					$connection->commit();
					$successMessage = $this->translate('Activation successfully.');
				} catch(\Exception $e) {
					$connection->rollback();
					/**
					* @todo Using Trigger an event.
					*/
					throw $e;
				}

				if ($errorCode == 0) {
					$this->flashMessenger()->addSuccessMessage($successMessage);
					return $this->redirect()->toRoute('profile');
				}
			}

		}

		$invitedApplications = $invite->getInvitedApplications();
		$applicationsTitle = array();
			foreach ($invitedApplications as $application) {
				$applicationsTitle[] = $application->getTitle();
			}
		$applicationsName = implode(', ', $applicationsTitle);
		
		$view = new ViewModel(array(
			'form' => $form,
			'email' => $email,
			'invitedAppication' => $applicationsName,
			'code' => $code,
			'successMessage' => $successMessage,
			'errorMessage' => $erroMessage
		));
		
		$view->setTemplate('ht-user/invite/activate');
		return $view;
	}
	
	protected function logIn($user)
	{
		$identity = $user->toStdClass();
		$identity->authenticationProvider = AuthenticationAccount::TYPE_INTERNAL;
		$this->logUserIn($identity);
		$this->writeLog(
				Log::TYPE_LOGIN_SUCCESS,
				$this->translate('Activate account with invitation code.'),
				array('user_id' => $user->getUserId())
		);
	}
	
	/**
	 * Check invited code : not expired, existed.
	 * Return ViewModel if there is error.
	 * Return Invite object is success.
	 * 
	 * @param string $code
	 * @return ViewModel | Invite
	 */
	protected function checkInvitedCode($code)
	{
		$helpText = '';
		if ($this->hasIdentity()) {
			$errorMessage = $this->translate('You are already registered.');
			$helpText = $this->translate('If you believe this is not your account then please logout'
					. ' and click invitation link again to register an account.');
			return $this->showErrorActivate($errorMessage, $helpText);
			
		}
		$sl = $this->getServiceLocator();
		/* @var $invite \HtUser\Model\Invite */
		$invite = $sl->get('Invite');
		if (!$invite->loadByCode($code)) {
			$errorMessage = $this->translate('Invalid activation key.');
			return $this->showErrorActivate($errorMessage, $helpText);
		}
		if ($invite->isInvitationExpired()) {
			$errorMessage = $this->translate('Invitation code activation time is over. You can contact administrator to resend new code.');
			return $this->showErrorActivate($errorMessage, $helpText);
		} else {
			return $invite;
		}
	}

	public function activateOAuthAction()
	{
		$provider = $this->params()->fromRoute('provider', '');
		$code = $this->params()->fromQuery('invite-code');
		
		/* @var $invite \HtUser\Model\Invite */
		$invite = $this->checkInvitedCode($code);
		if ($invite instanceof ViewModel) {
			$invite->setTemplate('ht-user/invite/activate');
			return $invite;
		}
		$sl = $this->getServiceLocator();
		/* @var $sessionManager \Zend\Session\SessionManager */
		$sessionManager = $sl->get('Zend\Session\SessionManager');
		
		/* @var $client \ReverseOAuth2\OAuth2HttpClient */
		$client = $this->getOAuthClient($provider,
                $this->url()->fromRoute('invite-user',
                        array('action' => 'activate-oauth-callback'),
                        array('force_canonical' => true))
                );
		$this->resetOAuthClient($client);
		
		$sessionManager->getStorage()->setMetadata('invite-code', $code);
		$url = $client->getUrl();
		return $this->redirect()->toUrl($url);

	}
	
	/**
	 * Register account with invitation code
	 * 
	 * @return \Zend\View\Model\ViewModel
	 * @throws \Exception
	 * @throws \HtUser\Controller\Exception
	 */
	public function activateOAuthCallbackAction()
	{
		$provider = $this->params()->fromRoute('provider', '');
		$sl = $this->getServiceLocator();
		$sessionManager = $sl->get('Zend\Session\SessionManager');
		
		//get invite code from session as facebook doesn't keep that code when redirect back
		//also validation the code
		$code = $sessionManager->getStorage()->getMetadata('invite-code');
		$invite = $this->checkInvitedCode($code);
		if ($invite instanceof ViewModel) {
			return $invite;
		}
		
		/* @var $oauthAdapter OAuth */
		$oauthAdapter = $sl->get('AuthAccount\OAuth');
		$oauthClient = $this->getOAuthClient($provider, $this->url()->fromRoute('invite-user',
                        array('action' => 'activate-oauth-callback'),
                        array('force_canonical' => true)));
		$oauthAdapter->setOAuth2Client($oauthClient)
			->setRequest($this->getRequest());
		$result = $oauthAdapter->authenticate();
			
		if (!$result->isValid()) {
			return array(
				'errorCode' => 1,
				'errorMessage' => $result->getMessages(),
			);			
		}
		
		$oauthIdentity = $result->getIdentity();

		/* @var $userApp \HtAuthentication\Model\UserApplication */
		try {
			$connection = $this->getConnection();
			$connection->beginTransaction();
			
			$oauthAdapter->setProvider($provider)
					->setProviderUserId($oauthIdentity->id);
			//create user AND add oauth authentication account
            if ($oauthAdapter->loadByProviderId()) {
                $user = $oauthAdapter->getUser();
            } else {
				//check user exist with oauth's email
				$foundUser = false;
				/* @var $user \HtUser\Model\User */
								
				if (property_exists($oauthIdentity, 'email')) {
					$user = $sl->get('User')
								->getOne(array('email' => $oauthIdentity->email));
					/* @var $user \HtUser\Model\User */
					if ($user) {
						$foundUser = true;
						if (!$oauthAdapter->isAdded($oauthIdentity->id, $provider)) {
							/* Email external and email internal is one email. */
							$oauthAdapter->linkUser($user);
						}
					}
				}
                //create new user
				if (!$foundUser) {
					$requiredActivate = false;
					$user = $sl->get('User')
								->exchangeProperties($oauthIdentity, $provider)
								->registerMemberAccount($requiredActivate);
					$oauthAdapter->linkUser($user);					
				}
            }
            
			$userId = $user->getUserId();
			
			/* @var $userApp \HtAuthentication\Model\UserApplication */
			$userApp = $sl->get('UserApp');
			$userApp->setUserId($userId);
			$userApp->addAccessByInvite($invite->getInviteId());
				
			$sessionManager->getStorage()->setMetadata('invite-code', null);
			$this->logUserIn($oauthAdapter->getIdentityForLogin($user));
			$connection->commit();
			
			$this->flashMessenger()->addSuccessMessage($this->translate('Activation successfully.'));
			
			return $this->redirect()->toRoute('profile');
		} catch(\Exception $e) {
			$connection->rollback();
			throw $e;
			/**
			* @todo Using Trigger an event.
			*/
		}
	}
}
