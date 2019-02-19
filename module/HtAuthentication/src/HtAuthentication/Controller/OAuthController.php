<?php

namespace HtAuthentication\Controller;

use HtApplication\Controller\OAuthBaseController;
use HtAuthentication\Form\Register as RegisterForm;
use HtAuthentication\Form\Login as LoginForm;
use HtAuthentication\Model\Adapter\OAuth;
use ReverseOAuth2\OAuth2HttpClient;
use HtApplication\Model\Log;
use HtUser\Model\User;

use Zend\View\Model\ViewModel;

class OAuthController extends OAuthBaseController
{

	public function indexAction()
	{
		
	}

	public function loginCallbackAction()
	{
		$oauthProvider = $this->params()->fromRoute('provider', '');
		$sl = $this->getServiceLocator();
		
		/* @var $client \ReverseOAuth2\Client\AbstractOAuth2Client */
		$client = $this->getOAuthClient($oauthProvider, 'login-callback');
		
		/* @var $oauthAdapter \HtAuthentication\Model\Adapter\OAuth */
		$oauthAdapter = $sl->get('AuthAccount\OAuth');
		$oauthAdapter->setOAuth2Client($client);
		$oauthAdapter->setRequest($this->getRequest());
		                                                                                                           
		/**
		 * Not use AuthenticateService as this is just authenticate of External OAuth Provider.
		 * If we use AuthenticateService, it will automaticaly store OAuth User Identiy in the session
		 *	which is not what we want
		 */
		$result = $oauthAdapter->authenticate();
		
		$view = new ViewModel(array(
			'form' => new LoginForm(),
		));
		$view->setTemplate('ht-authentication/internal/login');
		$this->layout('layout/layout-plain');
		if(!$result->isValid()) {
			return $view->setVariable('errorMessage', $result->getMessages());
		} else {
			
			$oauthIdentity = $result->getIdentity();
			$oauthAdapter
					->setProvider($oauthProvider)
					->setProviderUserId($oauthIdentity->id);

			if (!$oauthAdapter->isAdded()) {
				return $this->forward()->dispatch('HtAuthentication\Controller\OAuth', array(
					'controller' => 'HtAuthentication\Controller\OAuth',
					'action' => 'register-callback',
					'provider' => $oauthProvider
				));
			}
			
			$oauthAdapter->loadByProviderId();
			$user = $oauthAdapter->getUser();
			
            if (!$user) {
                return $view->setVariable('errorMessage',
                        sprintf($this->translate('Your %s account is existed in our system but you can not login with that account. '
                                . 'Please contact administrator for support.'), ucfirst($oauthProvider)));
            }
            
			if (!$user->getIsEnabled()) {
				return $view->setVariable('errorMessage', $this->translate('This user is not enabled.'));
			}
			
			
			$connection = $this->getConnection();
			try {
				$connection->beginTransaction();
				/**
				 * We do store identity in the session here
				 */
				$identity = $oauthAdapter->getIdentityForLogin($user);
				$this->logUserIn($identity);
				$this->writeLog(
					Log::TYPE_LOGIN_SUCCESS, 
					sprintf($this->translate('Login via %s account successfully.'), ucfirst($oauthProvider))
				);
				$connection->commit();
			} catch (\Exception $e) {
				$connection->rollback();
				throw $e;
			}
			return $this->redirect()->toRoute('profile');
		}
	}
	
	public function loginAction()
	{
		$provider = $this->params()->fromRoute('provider', '');
		$client = $this->getOAuthClient($provider, 'login-callback');
		$this->resetOAuthClient($client);
		$url = $client->getUrl();
		return $this->redirect()->toUrl($url);
	}
	
	/**
	 * Delete an oauth 
	 */
	public function deleteLoginAction()
	{		
		/**
		 * in route /oauth[:/action[/:provider]]
		 * this route /oauth/delete-login/id
		 */
		$id = $this->params()->fromRoute('provider');
		$sl = $this->getServiceLocator();
		
		$authAccount = $sl->get('AuthAccount');
		/* @var $authAccount \HtAuthentication\Model\AuthenticationAccount */
		$count = $authAccount->count(array('user_id' => $this->getLoggedInUserId()));
		
		$route = 'login-management';
		
		if ($count == 1) {
			$errorMessage = $this->translate('You are not allowed to delete this login because this is your last remaining one.');
			$this->flashMessenger()->addErrorMessage($errorMessage);
			return $this->redirect()->toRoute($route);
		}
		
		$oauth = $sl->get('AuthAccount\OAuth');
		/* @var $oauth \HtAuthentication\Model\Adapter\OAuth */
		if (!$oauth->load($id)) {
			$this->flashMessenger()->addErrorMessage(sprintf($this->translate('Not found oauth login with id: %s'), $id));
			return $this->redirect()->toRoute($route);
		}
		$identity = $this->getAuthenticationService()->getIdentity();
		if ($identity->authenticationProvider == $oauth->getProvider()) {
			if ($identity->oauthProviderId == $oauth->getProviderUserId()) {
				$this->flashMessenger()->addErrorMessage($this->translate('You cannot delete currently login.'));
				return $this->redirect()->toRoute($route);
			}
		}
		
		if (!$authAccount->load($oauth->getAuthenticationAccountId())) {
			$this->flashMessenger()->addErrorMessage(sprintf($this->translate('Not found authentication account with id: %s'), $id));
			return $this->redirect()->toRoute($route);
		}
		
		$provider = ucfirst($oauth->getProvider());
		$connection = $this->getConnection();
		try {
			$connection->beginTransaction();
			$oauth->delete();
			$authAccount->delete();
			$this->writeLog(
				Log::TYPE_DELETE_LOGIN_SUCCESS, 
				sprintf(
					$this->translate('Delete oauth login of provider %s, provider user id %s successfully.'), 
					$oauth->getProvider(), 
					$oauth->getProviderUserId()
				)
			);
			$connection->commit();
			$this->flashMessenger()->addSuccessMessage(sprintf($this->translate('Delete %s login successfully.'),$provider));
			return $this->redirect()->toRoute($route);
		} catch(\Exception $e) {
			$connection->rollback();
			throw $e;
		}
	}
	
	public function registerCallbackAction()
	{
		$provider = $this->params()->fromRoute('provider', '');
		
		$sl = $this->getServiceLocator();

		/* @var $client \ReverseOAuth2\Client\AbstractOAuth2Client */		
		$client = $this->getOAuthClient($provider, 'register-callback');
		
		/* @var $oauthAdapter \HtAuthentication\Model\Adapter\OAuth */
		$oauthAdapter = $sl->get('AuthAccount\OAuth');
		$oauthAdapter->setOAuth2Client($client);
		$oauthAdapter->setRequest($this->getRequest());
		
		/**
		 * Not use AuthenticateService as this is just authenticate of External OAuth Provider.
		 * If we use AuthenticateService, it will automaticaly store OAuth User Identiy in the session
		 *	which is not what we want
		 */
		$result = $oauthAdapter->authenticate();
		$view = new ViewModel();
		$view->setTemplate('ht-authentication/internal/register.phtml');
		$this->layout('layout/layout-plain');
		$view->setVariable('form', new RegisterForm());
		if (!$result->isValid()) {
			$view->setVariables(array(
				'errorMessage' => implode('<br />', $result->getMessages())
			));
			return $view;
		}
		
		$userInfo = $result->getIdentity();
		
		$oauthAdapter->setProvider($provider)
					->setProviderUserId($userInfo->id);
		
		if($oauthAdapter->isAdded()) {
			return $this->forward()->dispatch('HtAuthentication\Controller\OAuth', array(
				'controller' => 'HtAuthentication\Controller\OAuth',
				'action' => 'login-callback',
				'provider' => $provider,
			));
		}

		//check if authencation provider has email info, if so check if that email already register
		/* @var $user \HtUser\Model\User */
		$user = $sl->get('User');
		if (!empty($userInfo->email)) {
            $user->disableSoftDelete();
            if ($user->loadByEmail($userInfo->email)) {
                if ($user->getIsDeleted() || !$user->getIsEnabled()) {
                    $errorMessage = $this->translate(
                        'Email is existed in our system but you can not login with  that email.' .
                        'Please contact administrator for support.'
                    );
                    
                    $view->setVariables(array(
                        'errorMessage' => $errorMessage
                    ));
                    return $view;
                }
                
                $errorMessage = sprintf(
                $this->translate(
                    "Your email '%s', already registered in our website, try login using that email." .
                    "Please contact administrator for support if you think this message is incorrect"
                    ),
                    $userInfo->email
                );
                $view->setVariables(array(
                    'errorMessage' => $errorMessage
                ));
                return $view;
            }
		}

		// Insert user into database
		$connection = $this->getConnection();
		$connection->beginTransaction();
		try {
			$user->setIsEnabled(true);

			$user->exchangeProperties($userInfo, $provider);
			$requiredActivate = false;
			$user->registerMemberAccount($requiredActivate);

			//save role for new user
			$oauthAdapter->save();
			$oauthAdapter->linkUser($user);

			/* @var $userApp \HtAuthentication\Model\UserApplication */
			$userApp = $sl->get('UserApp');
			$userApp->setDefaultApplicationAndServer($user);

			//auto login after register
			$identity = $oauthAdapter->getIdentityForLogin($user);
			
			$this->logUserIn($identity);
			$this->writeLog(
					Log::TYPE_REGISTER_SUCCESS, 
					sprintf($this->translate('Register success via %s account.'), ucfirst($provider))
			);
			$connection->commit();
			$this->flashMessenger()->addSuccessMessage(sprintf($this->translate("Registration using %s successfully."), ucfirst($provider)));
			return $this->redirect()->toRoute('profile');
		} catch(\Exception $ex) {
			$connection->rollback();
			throw $ex;
		}
	}
	
	public function registerAction()
	{
		$provider = $this->params()->fromRoute('provider', '');
		$client = $this->getOAuthClient($provider, 'register-callback');
		$this->resetOAuthClient($client);
		$url = $client->getUrl();
		return $this->redirect()->toUrl($url);
	}
	
	public function addLoginCallbackAction()
	{
		
		$provider = $this->params()->fromRoute('provider', '');
		$sl = $this->getServiceLocator();

		/* @var $client \ReverseOAuth2\Client\AbstractOAuth2Client */		
		$client = $this->getOAuthClient($provider, 'add-login-callback');
		
		/* @var $oauthAdapter \HtAuthentication\Model\Adapter\OAuth */
		$oauthAdapter = $sl->get('AuthAccount\OAuth');
		$oauthAdapter->setOAuth2Client($client);
		$oauthAdapter->setRequest($this->getRequest());
		
		/**
		 *  Call adapter directly and not use AuthenticateService
		 *  as we don't need to store identity in the session storage
		 */
		$result = $oauthAdapter->authenticate();
		
		if (!$result->isValid()) {
			$this->flashMessenger()->addErrorMessage(implode('<br />', $result->getMessages()));
			return $this->redirect()->toRoute('login-management');
		}
		
		$userInfo = $result->getIdentity();
		$oauthAdapter->setProvider($provider)
					->setProviderUserId($userInfo->id);
		
		if($oauthAdapter->isAdded()) {
			$this->flashMessenger()->addErrorMessage(sprintf($this->translate('Your %s id already linked with an existed account'), $provider));
			return $this->redirect()->toRoute('login-management');
		}

		$connection = $this->getConnection();
		$connection->beginTransaction();
		try {
			$userId = $this->getLoggedInUserId();
			$oauthAdapter->save();
			$oauthAdapter->linkUser($userId);
			$this->writeLog(
				Log::TYPE_ADD_LOGIN_SUCCESS, 
				sprintf(
					$this->translate('Add oauth login of provider %s, provider user id %s successfully.'), 
					$oauthAdapter->getProvider(), 
					$oauthAdapter->getProviderUserId()
				)
			);
			$connection->commit();
			$this->flashMessenger()->addSuccessMessage(sprintf($this->translate("Add login using %s successfully"), $provider));
			return $this->redirect()->toRoute('login-management');
		} catch(\Exception $ex) {
			$connection->rollback();
			throw $ex;
		}
	}
	
	public function addLoginAction()
	{
		$provider = $this->params()->fromRoute('provider', '');
		$client = $this->getOAuthClient($provider, 'add-login-callback');
		$this->resetOAuthClient($client);
		$url = $client->getUrl();
		return $this->redirect()->toUrl($url);
	}
	
}