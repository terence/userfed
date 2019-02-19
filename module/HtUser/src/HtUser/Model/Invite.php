<?php
namespace HtUser\Model;
use AppCore\Mvc\Model\Model as AppCoreModel;
use HtAuthentication\Model\Application;
use HtAuthentication\Model\Server;

class Invite extends AppCoreModel
{
	protected $inviteId;
	protected $email;
	protected $code;
	protected $invitedDate;
	
	protected $mapping = array(
		array('dbColumn' => 'invite_id', 'objectProperty' => 'inviteId', 'isIdentifier' => true),
		array('dbColumn' => 'email', 'objectProperty' => 'email'),
		array('dbColumn' => 'code', 'objectProperty' => 'code'),
		array('dbColumn' => 'invited_date', 'objectProperty' => 'invitedDate'),
	);
	
	public function getInviteId()
	{
		return $this->inviteId;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getCode()
	{
		return $this->code;
	}

	public function getInvitedDate()
	{
		return $this->invitedDate;
	}

	public function setInviteId($inviteId)
	{
		$this->inviteId = $inviteId;
		return $this;
	}

	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}

	public function setCode($code)
	{
		$this->code = $code;
		return $this;
	}

	public function setInvitedDate($invitedDate)
	{
		$this->invitedDate = $invitedDate;
		return $this;
	}
	
	public function loadByEmail($email)
	{
		/* @var $invitedEmail \HtUser\Model\Invite */		
		$found = $this->getOne(array(
			'email' => $email
		));		
		if ($found) {
			$this->exchangeArray($found->toArray());
			return true;
		} else {
			return false;
		}
	}
	
	public function loadByCode($code)
	{
		$found = $this->getOne(array(
			'code' => $code
		));
		
		if ($found) {
			$this->exchangeArray($found->toArray());
			return true;
		}
		return false;
	}
	
	/**
	 * Create an invite by email
	 * @param string $email
	 * @return \HtUser\Model\Invite
	 */
	public function createByEmail($email, $invitationCode = null)
	{
		if (empty($invitationCode)) {
			$invitationCode = $this->generationCode($email);
		}
		
		$this->setEmail($email)
				->setInvitedDate(date('Y-m-d H:i:s'))
				->setCode($invitationCode);
		$this->save();		
		return $this;
	}
	
	/**
	 * Check if this email already invited by application+server
	 * 
	 * @param int|Application $app
	 * @param int|Server $server
	 * @return boolean
	 */
	public function isAlreadyInviteBy($app, $server)
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
		$sm = $this->getServiceManager();
		/* @var $appInvite ApplicationInvite */
		$applicationInvite = $sm->get('AppInvite');
		
		/* @var $app \HtUser\Model\ApplicationInvite  */
		$count = $applicationInvite->count(array('application_id' => $appId, 'server_id' => $serverId, 'invite_id' => $this->getInviteId()));
		if ($count > 0) {
			return true;
		}
		return false;
	}
	
	/**
	 * Check if invitation is expired
	 * @return boolean
	 */
	public function isInvitationExpired()
	{
		//@todo: read 14 from config in database
		$invitedTime = strtotime($this->getInvitedDate() . " +14 days");
		/* should get time from a configuration storaged in database. */
		$currentTime = time();
		if ($invitedTime >= $currentTime) {
			return false;
		} else {
			//is expired
			return true;
		}
	}
	
	/**
	 * 
	 * @param \HtAuthentication\Model\Application | int $app
	 * @param \HtAuthentication\Model\Server | int$server
	 * @return boolean
	 */
	public function invite($app, $server)
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
		//@todo: read 14 from config in database
//		$invitedTime = strtotime($invite->getInvitedDate() . " +14 days");
		/* should get time from a configuration storaged in database. */
		$currentTime = time();
		$sm = $this->getServiceManager();
		if ($this->isAlreadyInviteBy($appId, $serverId)) {
			if (!$this->isInvitationExpired()) {
				/* this application invited an email existed (not expired.) */
				$this->throwPublicException($this->translate('This email is already invited.'));
			} else {
				/* this application invited this email (expired activation date)*/
				/* set new code and reset time. */
				$invitationCode = $this->generationCode($this->getEmail());
				$this->setCode($invitationCode)
						->setInvitedDate(date('Y-m-d H:i:s', $currentTime))
						->save();
				return true;
			}
		} else {
			// the app-server combination hasn't invited user yet
			if ($this->isInvitationExpired()) {
				// Another application invite AND Activate account time is expired
				$this->setCode($this->generationCode($this->getEmail()));
			}
			$this->setInvitedDate(date('Y-m-d H:i:s', $currentTime));
			$this->save();
			
			/* @var $appInvite \HtUser\Model\ApplicationInvite  */
			$appInvite = $sm->get('AppInvite');
			$appInvite
					->setApplicationId($appId)
					->setServerId($serverId)
					->setInviteId($this->getInviteId());
			$appInvite->save();
			return true;
		}
		return false;
	}
	
	/**
	 * Generation Invited code.
	 * @param string $email
	 * @return string
	 */
	public function generationCode($email = '')
	{
		do {
			$time = time() . microtime();
			$code = hash('sha512', $email . $time);
			$count = $this->count(array('code' => $code));
		} while($count > 0);
		
		return $code;
	}
	
	/**
	 * 
	 * @return array of Application
	 */
	public function getInvitedApplications()
	{
		$sm = $this->getServiceManager();
		/* get All application invited name */
		$applicationInvite = $sm->get('AppInvite');
		/* @var $applicationInvite \HtUser\Model\ApplicationInvite */
		$appsInvited = $applicationInvite->getAll(array('invite_id' => $this->getInviteId()));
		$applicationIds = array();
		foreach ($appsInvited as $appInvited) {
			$applicationIds[] = $appInvited->getApplicationId();
		}

		/* @var $application \HtAuthentication\Model\ApplicationServerCollection */
		$application = $sm->get('App');
		return $application->getAll(array('application_id' => $applicationIds));
	}
}
