<?php

namespace HtAuthentication\Controller;

use HtApplication\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use HtAuthentication\Model\Server;
use HtAuthentication\Model\Token;
use HtUser\Model\User;
use HtAuthentication\Model\Application;
use HtAuthentication\Model\Organisation;
use HtApplication\Model\Log;

abstract class TokenBaseController extends AbstractActionController
{

	/**
	 * Current verified server
	 * @var Server
	 */
	protected $server;

	/**
	 * Current verified token
	 * @var Token
	 */
	protected $token;

	/**
	 * Current checking and allowed application
	 * @var Application
	 */
	protected $application;

	/**
	 * Current organisation under which user access (optional)
	 * @var Organisation
	 */
	protected $organisation;

	/**
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * @return Server
	 */
	public function getServer()
	{
		return $this->server;
	}

	public function setServer($server)
	{
		$this->server = $server;
		return $this;
	}

	/**
	 * @return Token
	 */
	public function getToken()
	{
		return $this->token;
	}

	public function setToken($token)
	{
		$this->token = $token;
		return $this;
	}

	public function getApplication()
	{
		return $this->application;
	}

	public function setApplication(Application $application)
	{
		$this->application = $application;
	}

	public function getOrganisation()
	{
		return $this->organisation;
	}

	public function setOrganisation(Organisation $organisation)
	{
		$this->organisation = $organisation;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function setUser(User $user)
	{
		$this->user = $user;
	}

	protected function verifySecretAndToken()
	{
		$request = $this->getRequest();
		$secret = $request->getQuery("secret");
		$tokenString = $request->getQuery("token");
		$sl = $this->getServiceLocator();
		/* @var $sessionManager \Zend\Session\SessionManager */
		$sessionManager = $sl->get("Zend\Session\SessionManager");
		$sessionId = $sessionManager->getId();

		//check secret valid
		/* @var $server \HtAuthentication\Model\Server */
		$server = $sl->get('Server');
		$serverObject = $server->getBySecret($secret);

		//check token valid
		/* @var $token \HtAuthentication\Model\Token */
		$token = $sl->get('Token');

		$userId = $token->getUserIdBySessionId($sessionId);
		if(empty($secret) || empty($tokenString)) {
			$message = $this->translate('Empty token or secret');
			$this->logError($userId, $message);
			return new JsonModel(array('errorCode' => 1, 'message' => $message));
		}

		if(strlen($tokenString) != Token::TOKEN_LENGTH) {
			$message = sprintf($this->translate('Token length should be %s'), Token::TOKEN_LENGTH);
			$this->logError($userId, $message);
			return new JsonModel(array('errorCode' => 2, 'message' => $message));
		}

		if(!$serverObject) {
			$message = $this->translate('Invalid secret');
			$this->logError($userId, $message);
			return new JsonModel(array('errorCode' => 3, 'message' => $message));
		}

		if(!$token->loadByTokenString($tokenString)) {
			$message = $this->translate('Invalid token');
			$this->logError($userId, $message);
			return new JsonModel(array('errorCode' => 4, 'message' => $message));
		}

		$canNotAccess = true;
		//check user can access server
		/* @var $userApp \HtAuthentication\Model\UserApplication */
		$userApp = $sl->get('UserApp');
		if($userApp->loadByToken($token)) {
			$this->setApplication($userApp->getApplication());
			$this->setUser($userApp->getUser());
			$orgId = $userApp->getOrganisationId();

			if(isset($orgId)) {
				$this->setOrganisation($userApp->getOrganisation());
			}

			if($userApp->getServerId() == $serverObject->getServerId()) {
				$canNotAccess = false;
			}
		}

		if($canNotAccess) {
			$message = $this->translate('User can not access ');
			$this->logError($userId, $message);
			return new JsonModel(array('errorCode' => 4, 'message' => $message));
		}

		$this->setServer($serverObject);
		$this->setToken($token);
	}

	protected function logError($userId, $message)
	{
		$connection = $this->getConnection();
		try {
			$connection->beginTransaction();
			$this->writeLog(Log::TYPE_VERIFY_TOKEN_FAILED, $message, array('user_id' => $userId));
			$connection->commit();
		} catch(\Exception $e) {
			$connection->rollback();
			throw $e;
		}
	}

}
