<?php

/**
 * Manage token for each user. This token will indentify user session with servers
 */

namespace HtAuthentication\Model;

use AppCore\Mvc\Model\Model as AppCoreModel;
use HtUser\Model\User;
use Zend\Db\Sql\Select;

class Token extends AppCoreModel
{

	const TOKEN_LENGTH = 512;

	protected $tokenId;
	protected $token;
	protected $tokenRegistrationDate;
	protected $creationDate;
	protected $lastUpdated;
	protected $mapping = array(
		array('dbColumn' => 'token_id', 'objectProperty' => 'tokenId', 'isIdentifier' => true),
		array('dbColumn' => 'token', 'objectProperty' => 'token'),
		array('dbColumn' => 'token_registration_date', 'objectProperty' => 'tokenRegistrationDate'),
		array('dbColumn' => 'creation_date', 'objectProperty' => 'creationDate'),
		array('dbColumn' => 'last_updated', 'objectProperty' => 'lastUpdated'),
	);

	public function getTokenId()
	{
		return $this->tokenId;
	}

	public function setTokenId($tokenId)
	{
		$this->tokenId = $tokenId;
		return $this;
	}

	public function getToken()
	{
		return $this->token;
	}

	public function setToken($token)
	{
		$this->token = $token;
		return $this;
	}

	public function getTokenRegistrationDate()
	{
		return $this->tokenRegistrationDate;
	}

	public function setTokenRegistrationDate($tokenRegistrationDate)
	{
		$this->tokenRegistrationDate = $tokenRegistrationDate;
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
	 * Generate a new token for a new login session
	 */
	public function generate()
	{
		//generate a random key
		$token = "";
		do {
			$token .= sha1(time() . rand(0, 1000));
		} while(strlen($token) < self::TOKEN_LENGTH);
		
		$token = substr($token, 0, self::TOKEN_LENGTH);
		$now = date('Y-m-d H:i:s');

		$tokenId = $this->getTokenId();
		if (!empty($tokenId)) {
			$this->setToken($token);
			$this->setLastUpdated($now);
		} else {
			$this->exchangeArray(array(
				'token' => $token,
				'token_registration_date' => $now,
				'creation_date' => $now,
				'last_updated' => $now,
			));
		}
		$this->save();
	}

	/**
	 * Load token by token string
	 * 
	 * @param string $tokenString
	 * @return Token | null
	 */
	public function loadByTokenString($tokenString)
	{
		$token = $this->getOne(array('token' => $tokenString));
		if ($token) {
			$this->exchangeArray($token->toArray());
		}
		return $token;
	}

	/**
	 * Delete token and linked php session 
	 */
	public function delete($object = null)
	{
		$tokenWebSessionTable = $this->getTableGateway("token_web_session");
		$webSessionTable = $this->getTableGateway("web_session");
		$tokenId = $this->getTokenId();

		//find all old web session id linked to current token and delete
		$oldLinkedItems = $tokenWebSessionTable->select(array(
			'token_id' => $tokenId
		));

		//delete link token - web session id
		$tokenWebSessionTable->delete(array('token_id' => $tokenId));

		//delete old web session
		$oldWebSessionIds = array();
		foreach ($oldLinkedItems as $item) {
			$oldWebSessionIds[] = $item->session_id;
		}
		if (count($oldWebSessionIds) > 0) {
			$webSessionTable->delete(array('id' => $oldWebSessionIds));
		}

		parent::delete();
	}

	/**
	 * Link token to web session id.
	 * So later we can delete web session with a token
	 * @param string $webSessionId
	 */
	public function linkWebSession($webSessionId)
	{
		$tokenId = $this->getTokenId();
		if (empty($tokenId)) {
			throw new \Exception("No token id found");
		}
		if (empty($webSessionId)) {
			throw new \Exception("Empty web session id");
		}

		$webSessionTable = $this->getTableGateway("web_session", false);
		$tokenWebSessionTable = $this->getTableGateway("token_web_session", false);
		
		//find all old web session id linked to current token and delete
		$oldLinkedItems = $tokenWebSessionTable->select(array(
			'session_id != ?' => $webSessionId,
			'token_id' => $tokenId
		));
		//delete link
		$tokenWebSessionTable->delete(array(
			'session_id != ?' => $webSessionId,
			'token_id' => $tokenId
		));

		//delete old web session
		$oldWebSessionIds = array();
		foreach ($oldLinkedItems as $item) {
			$oldWebSessionIds[] = $item->session_id;
		}
		if (count($oldWebSessionIds) > 0) {
			$webSessionTable->delete(array('id' => $oldWebSessionIds));
		}

		//link to new token 
		$tokenWebSessionTable->insert(array(
			'session_id' => $webSessionId,
			'token_id' => $tokenId
		));
	}

	public function getUserIdBySessionId($sessionId)
	{
		$tokenWebSessionTable = $this->getTableGateway("token_web_session", false);
		$rowset = $tokenWebSessionTable->select(function(Select $select) use($sessionId) {
			$select->join("user_application_token", "user_application_token.token_id = token_web_session.token_id")
					->join("user_application", "user_application.user_application_id = user_application_token.user_application_id", array("user_id"));
			$select->where(array('session_id' => $sessionId));
		});
		$row = $rowset->current();
		if ($row) {
			return $row->user_id;
		} else {
			return false;
		}
	}
	
	/**
	 * Get user who own the token
	 * 
	 * @return null | User
	 */
	public function getUser()
	{
		$tokenId = $this->getTokenId();
		$tableGateway = $this->getTableGateway("token", false);
		$result = $tableGateway->select(function(Select $select) use ($tokenId) {
			$select->join("user_application_token", "user_application_token.token_id = token.token_id")
				->join("user_application", "user_application.user_application_id = user_application_token.user_application_id", array("user_id"));
			$select->where(array('user_application_token.token_id' => $tokenId));
		});
		
		$result = $result->current();
		if ($result) {
			$sl = $this->getServiceManager();
			$user = $sl->get('User');
			if ($user->load($result->user_id)) {
				return $user;
			}
		}
		return null;
	}
	
	/**
	 * Token is expired or not
	 * @return boolean
	 */
	public function isExpired()
	{
		/**
		 * @todo: read 5 minutes from config
                 * @todo: Will change to 5 minutes when re-generation token has been implemented.
                 * For now, we wouldn't like token has been expried, that why it is 2 hours
		 */
		$expiredTime = strtotime($this->getCreationDate() . " +2 years");
		if ($expiredTime >= time()) {
			return false;
		} else {
			//is expired
			return true;
		}		
	}

}
