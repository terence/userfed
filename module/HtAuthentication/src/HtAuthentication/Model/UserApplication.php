<?php

/**
 * Manage which application's server, user has access and under which organisation (organisation is optional)
 */

namespace HtAuthentication\Model;

use AppCore\Mvc\Model\Model as AppCoreModel;
use AppCore\Mvc\Model\SoftDeleteableInterface;
use HtAuthentication\Model\Application;
use HtAuthentication\Model\Server;
use HtAuthentication\Model\Organisation;
use HtAuthentication\Model\Token;
use HtUser\Model\User;
use HtUser\Model\Invite;
use Zend\Db\Sql\Select;

class UserApplication extends AppCoreModel implements SoftDeleteableInterface
{

	protected $userApplicationId;
	protected $userId;
	protected $organisationId;
	protected $invitationToken;
	protected $invitationTokenCreation;
	protected $default;
	protected $destinationUrl;
	protected $creationDate;
	protected $lastUpdated;
	protected $applicationId;
	protected $serverId;


	protected $mapping = array(
		array('dbColumn' => 'user_application_id', 'objectProperty' => 'userApplicationId', 'isIdentifier' => true),
		array('dbColumn' => 'user_id', 'objectProperty' => 'userId'),
		array('dbColumn' => 'organisation_id', 'objectProperty' => 'organisationId'),
		array('dbColumn' => 'application_id', 'objectProperty' => 'applicationId'),
		array('dbColumn' => 'server_id', 'objectProperty' => 'serverId'),
		array('dbColumn' => 'invitation_token', 'objectProperty' => 'invitationToken'),
		array('dbColumn' => 'invitation_token_creation', 'objectProperty' => 'invitationTokenCreation'),
		array('dbColumn' => 'default', 'objectProperty' => 'default'),
		array('dbColumn' => 'destination_url', 'objectProperty' => 'destinationUrl'),
		array('dbColumn' => 'creation_date', 'objectProperty' => 'creationDate'),
		array('dbColumn' => 'last_updated', 'objectProperty' => 'lastUpdated'),
	);

	public function getUserApplicationId()
	{
		return $this->userApplicationId;
	}

	public function setUserApplicationId($userApplicationId)
	{
		$this->userApplicationId = $userApplicationId;
		return $this;
	}

	public function getUserId()
	{
		return $this->userId;
	}

	public function setUserId($userId)
	{
		$this->userId = $userId;
		return $this;
	}

	public function getOrganisationId()
	{
		return $this->organisationId;
	}

	public function setOrganisationId($organisationId)
	{
		$this->organisationId = $organisationId;
		return $this;
	}

	public function getApplicationId()
	{
		return $this->applicationId;
	}

	public function getServerId()
	{
		return $this->serverId;
	}

	public function setApplicationId($applicationId)
	{
		$this->applicationId = $applicationId;
		return $this;
	}

	public function setServerId($serverId)
	{
		$this->serverId = $serverId;
		return $this;
	}

	public function getInvitationToken()
	{
		return $this->invitationToken;
	}

	public function setInvitationToken($invitationToken)
	{
		$this->invitationToken = $invitationToken;
		return $this;
	}

	public function getInvitationTokenCreation()
	{
		return $this->invitationTokenCreation;
	}

	public function setInvitationTokenCreation($invitationTokenCreation)
	{
		$this->invitationTokenCreation = $invitationTokenCreation;
		return $this;
	}

	public function getDefault()
	{
		return $this->default;
	}

	public function setDefault($default)
	{
		$this->default = $default;
		return $this;
	}

	public function getDestinationUrl()
	{
		return $this->destinationUrl;
	}

	public function setDestinationUrl($destinationUrl)
	{
		$this->destinationUrl = $destinationUrl;
		return $this;
	}

	public function getCreationDate()
	{
		return $this->creationDate;
	}

	public function setCreationDate($creationDate)
	{
		$this->creationDate = $creationDate;
		return $this;
	}

	public function getLastUpdated()
	{
		return $this->lastUpdated;
	}

	public function setLastUpdated($lastUpdated)
	{
		$this->lastUpdated = $lastUpdated;
		return $this;
	}
	
	/**
	 * @var Application
	 */
	protected $application;

	/**
	 * @return Application
	 * @throws \Exception
	 */
	public function getApplication()
	{
		if (!isset($this->application)) {
			/* @var $app \HtAuthentication\Model\Application */
			$app = $this->getServiceManager()->get('App');
			if (!$app->load($this->getApplicationId())) {
				throw new \Exception("No application id found");
			}
			$this->application = $app;
		}
		return $this->application;
	}

	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @return User
	 * @throws \Exception
	 */
	public function getUser()
	{
		if (!isset($this->user)) {
			/* @var $user \HtUser\Model\User */
			$user = $this->getServiceManager()->get('User');
			if (!$user->load($this->getUserId())) {
				throw new \Exception("No user id found");
			}
			$this->user = $user;
		}
		return $this->user;
	}

	/**
	 * @var Organisation
	 */
	protected $organisation;

	/**
	 * @return Organisation
	 * @throws \Exception
	 */
	public function getOrganisation()
	{
		if (!isset($this->organisation)) {
			/* @var $org \HtAuthentication\Model\Organisation */
			$org = $this->getServiceManager()->get('Org');
			if ($org->load($this->getOrganisationId())) {
//				throw new \Exception("No organisation id found");
				$this->organisation = $org;
			}
		}
		return $this->organisation;
	}

	/**
	 * @var Server
	 */
	protected $server;

	/**
	 * @return Server
	 * @throws \Exception
	 */
	public function getServer()
	{
		if (!isset($this->server)) {
			/* @var $server \HtAuthentication\Model\Server */
			$server = $this->getServiceManager()->get('Server');
			if (!$server->load($this->getServerId())) {
				throw new \Exception(sprintf($this->tranlsate("Not found server with id: %s"), $this->getServerId()));
			}
			$this->server = $server;
		}
		return $this->server;
	}

	public function getByUser(User $user)
	{
		$userAppTable = $this->getServiceManager()->get('App')->getTable()->table;
		$table = $this->getTable()->table;
		return $this->getAll(function(Select $select) use ($user, $userAppTable, $table) {
					$select->join($userAppTable, "{$userAppTable}.application_id = {$table}.application_id")
							->where(array('user_id' => $user->getUserId()));
				});
	}

	/**
	 * Create a default set of server - application for new user.
	 * This method is now used for testing
	 * 
	 * @param User $user
	 */
	public function setDefaultApplicationAndServer(User $user)
	{
		$this->getTable()->insert(array(
			'user_id' => $user->getUserId(),
			'application_id' => 1,
			'server_id' => 1,
		));
	}

	/**
	 * Get all items which match where criteria
	 *
	 * @param Where|\Closure|string|array $where
	 * @param int $offset
	 * @param int $limit
	 * @param string $order
	 * @return ResultSet
	 */
	public function getAll($where = null, $offset = null, $limit = null, $order = null)
	{
		$rowset = parent::getAll($where, $offset, $limit, $order);
		return $this->prepare($rowset);
	}

	/**
	 * Get token by user and organisation
	 * @param User $user
	 * @param Organisation $org
	 * @return Token
	 * @throws \Exception
	 */
	public function getToken(User $user, Organisation $org = null)
	{
		$serverId = $this->getServerId();
		$appId = $this->getApplicationId();
		if (empty($serverId) || empty($appId)) {
			throw new \Exception("Server or application id not found");
		}

		$orgId = null;
		if (isset($org)) {
			$orgId = $org->getOrganisationId();
		}

		/* @var $userApp \HtAuthentication\Model\UserApplication */
		$userApp = $this->getOne(array(
			'user_id' => $user->getUserId(),
			'organisation_id' => $orgId,
			'server_id' => $serverId,
			'application_id' => $appId
		));
		if (!isset($userApp)) {
			throw new \Exception("No authorization data found with provided server and app");
		}
		/* @var $userAppTokenTable \HtAuthentication\Model\UserApplicationTokenTable */
		$userAppTokenTable = $this->getServiceManager()->get('HtAuthentication\Model\UserApplicationTokenTable');
		$userAppToken = $userAppTokenTable->getOne(array('user_application_id' => $userApp->getUserApplicationId()));
		if (isset($userAppToken)) {
			/* @var $token \HtAuthentication\Model\Token */
			$token = $this->getServiceManager()->get('Token');
			if (!$token->load($userAppToken->token_id)) {
				throw new \Exception("No token found");
			}
			return $token;
		}
		return false;
	}

	/**
	 * Generate token for user to access application/server without/without organization info
	 * @param User $user
	 */
	public function generateToken(User $user, Organisation $org = null)
	{
		$serverId = $this->getServerId();
		$appId = $this->getApplicationId();
		if (empty($serverId) || empty($appId)) {
			throw new \Exception("Server or application id not found");
		}
		$orgId = null;
		if (isset($org)) {
			$orgId = $org->getOrganisationId();
		}
		/* @var $userApp \HtAuthentication\Model\UserApplication */
		$userApp = $this->getOne(array(
			'user_id' => $user->getUserId(),
			'organisation_id' => $orgId,
			'server_id' => $serverId,
			'application_id' => $appId
		));
		if (!isset($userApp)) {
			throw new \Exception("No authorization data found with provided server and app");
		}
		/* @var $userAppTokenTable \HtAuthentication\Model\UserApplicationTokenTable */
		$userAppTokenTable = $this->getServiceManager()->get('HtAuthentication\Model\UserApplicationTokenTable');
		$appToken = $userAppTokenTable->getOne(array('user_application_id' => $userApp->getUserApplicationId()));

		/* @var $token \HtAuthentication\Model\Token */
		$token = $this->getServiceManager()->get('Token');
		if (!isset($appToken)) {
			$token->generate();
			$userAppTokenTable->insert(array(
				'token_id' => $token->getTokenId(),
				'user_application_id' => $userApp->getUserApplicationId()
			));
			return $token;
		} else {
			if (!$token->load($appToken->token_id)) {
				throw new \Exception("No token id found");
			}
			$token->generate();
			return $token;
		}
	}

	/**
	 * Init authorization data by token
	 * @param Token $token
	 * @return UserApplication
	 */
	public function loadByToken(Token $token)
	{
		/* @var $userAppTokenTable \HtAuthentication\Model\UserApplicationTokenTable */
		$userAppTokenTable = $this->getServiceManager()->get("HtAuthentication\Model\UserApplicationTokenTable");
		$userAppToken = $userAppTokenTable->getOne(array('token_id' => $token->getTokenId()));
		if (isset($userAppToken)) {
			return $this->load($userAppToken->user_application_id);
		}
		return false;
	}

	/**
	 * 
	 * @param int|Application $app
	 * @param int|Server $server
	 * @return boolean
	 */
	public function hasAccess($app, $server)
	{
		if ($app instanceof Application) {
			$appId = $app->getApplicationId();
		} else {
			$appId = (int) $app;
		}

		if ($server instanceof Server) {
			$serverId = $server->getServerId();
		} else {
			$serverId = (int) $server;
		}

		$count = $this->count(array(
			'user_id' => $this->getUserId(),
			'application_id' => $appId,
			'server_id' => $serverId
		));
		if ($count > 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 
	 * @param Application | int $app
	 * @param Server | int $server
	 * @param Organisation | int $org
	 */
	public function addAccess($app, $server, $org = null)
	{
		if ($app instanceof Application) {
			$appId = $app->getApplicationId();
		} else {
			$appId = (int) $app;
		}

		if ($server instanceof Server) {
			$serverId = $server->getServerId();
		} else {
			$serverId = (int) $server;
		}

		if (empty($this->userId) || empty($serverId) || empty($appId)) {
			throw new \Exception("Invalid permission data");
		}
		$creationDate = date('Y-m-d H:i:s');
		$this->setUserApplicationId(null)
				->setServerId($serverId)
				->setApplicationId($appId)
				->setDefault(0)
				->setCreationDate($creationDate);
		if (isset($org)) {
			if ($org instanceof Organisation) {
				$this->setOrganisationId($org->getOrganisationId());
			} else {
				$this->setOrganisationId($org);
			}
		}
		
		$this->save();
	}
	
	/**
	 * Check if user has access to app under the org.
	 * @param User|int $user
	 * @param Organisation|int $org
	 * @param Application|int $app
	 * @param Server|int $server
	 * 
	 * @return boolean
	 */
	public function hasUserAccess($org, $app, $server = null)
	{
		if (!$this->userId) {
			throw new \Exception('User id cannot be null.');
		}
		
		if ($org instanceof Organisation) {
			$orgId = $org->getOrganisationId();
		} else {
			$orgId = (int) $org;
		}
		
		if ($app instanceof Application) {
			$appId = $app->getApplicationId();
		} else {
			$appId = (int) $app;
		}
		
		$criteria = array(
			'user_id' => $this->getUserId(),
			'organisation_id' => $orgId,
			'application_id' => $appId,
		);
		
		if (isset($server)) {
			if ($server instanceof Server) {
				$serverId = $server->getServerId();
			} else {
				$serverId = (int) $server;
			}
			$criteria['server_id'] = $serverId;
		}
		$count = $this->count($criteria);		
		if ($count == 0) {
			return false;
		} else {
			return true;
		}
	}
	/**
	 * Add user access to application under an organisation.
	 * If no server identified then select one of its server
	 * 
	 * @param \HtAuthentication\Model\Organisation $org
	 * @param \HtAuthentication\Model\Application $app
	 * @param \HtAuthentication\Model\Server $server
	 */
	public function addUserAccess($org, $app, $server = null)
	{
		if (!$this->userId) {
			throw new \Exception('User id cannot be null.');
		}
		
		if ($org instanceof Organisation) {
			$orgId = $org->getOrganisationId();
		} else {
			$orgId = (int) $org;
		}
		
		if ($app instanceof Application) {
			$appId = $app->getApplicationId();
		} else {
			$appId = (int) $app;
		}

		$this->setApplicationId($appId)
			->setDefault(0)
			->setUserId($this->getUserId())
			->setOrganisationId($orgId)
			->setCreationDate(date("Y-m-d H:i:s"));
		
		if (isset($server)) {
			if ($server instanceof Server) {
				$serverId = $server->getServerId();
			} else {
				$serverId = (int) $server;
			}
			$this->setServerId($serverId);
		} else {
			/* @var $appServer \HtAuthentication\Model\ApplicationServer */
			$sl = $this->getServiceManager();
			$appServer = $sl->get('AppServer')
				->getOne(array('application_id' => $appId));
			if (!$appServer) {
				throw new \Exception(sprintf("Application with id %s has no server", $appId));
			}
			$serverId = $appServer->getServerId();
		}
		
		$this->save();
	}
	
	/**
	 * Add all application invited for user.
	 * @param int $inviteId 
	 */
	public function addAccessByInvite($invite)
	{
		if (!$this->getUserId()) {
			throw new \Exception('User id cannot be null.');
		}
		if ($invite instanceof Invite) {
			$inviteId = $invite->getInviteId();
		} else {
			$inviteId = (int) $invite;
		}
		
		$sl = $this->getServiceManager();
		/* @var $applicationInvite \HtUser\Model\ApplicationInvite */
		$applicationInvite = $sl->get('AppInvite');
		$appsInvited = $applicationInvite->getAll(array('invite_id' => $inviteId));
		
		foreach ($appsInvited as $appInvite) {
			if (!$this->hasAccess($appInvite->getApplicationId(), $appInvite->getServerId())) {
				$this->addAccess($appInvite->getApplicationId(), $appInvite->getServerId());
			}
		}
	}	

}
