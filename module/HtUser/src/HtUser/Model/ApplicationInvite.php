<?php
namespace HtUser\Model;

use AppCore\Mvc\Model\Model as AppCoreModel;

class ApplicationInvite extends AppCoreModel
{
	protected $applicationInviteId;
	protected $inviteId;
	protected $applicationId;
	protected $serverId;


	protected $mapping = array(
		array('dbColumn' => 'application_invite_id', 'objectProperty' => 'applicationInviteId', 'isIdentifier' => true),
		array('dbColumn' => 'invite_id', 'objectProperty' => 'inviteId'),
		array('dbColumn' => 'application_id', 'objectProperty' => 'applicationId'),
		array('dbColumn' => 'server_id', 'objectProperty' => 'serverId'),
	);
	
	public function getApplicationInviteId()
	{
		return $this->applicationInviteId;
	}

	public function getInviteId()
	{
		return $this->inviteId;
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

	public function setInviteId($inviteId)
	{
		$this->inviteId = $inviteId;
		return $this;
	}

	public function setApplicationServerId($applicationServerId)
	{
		$this->applicationServerId = $applicationServerId;
		return $this;
	}

	public function isAlreadyInvited()
	{
		
	}
}
