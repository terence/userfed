<?php

namespace HtApplication\Model;
use Zend\Log\Logger;
use Zend\Log\Writer\Db;
use AppCore\Mvc\Model\Model as AppCoreModel;

class Log extends AppCoreModel
{
	protected $logId;
	protected $userId;
	protected $timestamp;
	protected $type;
	protected $message;
	protected $ipAddress;
    protected $logType;

    protected $mapping = array(
		array('dbColumn' => 'log_id', 'objectProperty' => 'logId', 'isIdentifier' => true),
		array('dbColumn' => 'user_id', 'objectProperty' => 'userId'),
		array('dbColumn' => 'ip_address', 'objectProperty' => 'ipAddress'),
		array('dbColumn' => 'timestamp', 'objectProperty' => 'timestamp'),
		array('dbColumn' => 'type', 'objectProperty' => 'type'),
		array('dbColumn' => 'log_type', 'objectProperty' => 'logType'),
		array('dbColumn' => 'message', 'objectProperty' => 'message'),
	);
	
	const TYPE_REGISTER_SUCCESS					= 'register_success';
	const TYPE_REGISTER_FAILED					= 'register_failed';
	
	const TYPE_LOGIN_SUCCESS					= 'login_success';
	const TYPE_LOGIN_FAILED						= 'login_failed';
	
	const TYPE_ADD_LOGIN_SUCCESS				= 'add_login_success';
	const TYPE_ADD_LOGIN_FAILED					= 'add_login_failed';	
	
	const TYPE_DELETE_LOGIN_SUCCESS				= 'delete_login_success';
	const TYPE_DELETE_LOGIN_FAILED				= 'delete_login_failed';	
	
	const TYPE_EXTERNAL_AUTHENTICATION_SUCCESS	= 'external_authentication_success';
	const TYPE_EXTERNAL_AUTHENTICATION_FAILED	= 'external_authentication_failed';
	
	const TYPE_LOGOUT							= 'logout';
	
	const TYPE_VERIFY_TOKEN_SUCCESS				= 'verify_token_success';
	const TYPE_VERIFY_TOKEN_FAILED				= 'verify_token_failed';
	
	const TYPE_CHANGE_PASSWORD_SUCCESS			= 'change_password_success';
	const TYPE_CHANGE_PASSWORD_FAILED			= 'change_password_failed';
    
    const LOG_TYPE_USER = 1;


	protected $typeName = array(
		self::TYPE_REGISTER_SUCCESS => 'Register success',
		self::TYPE_REGISTER_FAILED => 'Register failed',
		self::TYPE_LOGIN_SUCCESS => 'Login success',
		self::TYPE_LOGIN_FAILED => 'Login failed',
		self::TYPE_ADD_LOGIN_SUCCESS => 'Add login success',
		self::TYPE_ADD_LOGIN_FAILED => 'Add login failed',
		self::TYPE_EXTERNAL_AUTHENTICATION_SUCCESS => 'External authentication success',
		self::TYPE_EXTERNAL_AUTHENTICATION_FAILED => 'External authentication failed',
		self::TYPE_VERIFY_TOKEN_SUCCESS => 'Verify token success.',
		self::TYPE_VERIFY_TOKEN_FAILED => 'Verify token failed.',
		self::TYPE_CHANGE_PASSWORD_SUCCESS => 'Change password success',
		self::TYPE_CHANGE_PASSWORD_FAILED => 'Change password failed',
		self::TYPE_DELETE_LOGIN_FAILED =>  'Delete login failed',
		self::TYPE_DELETE_LOGIN_SUCCESS => 'Delete login success',
		self::TYPE_LOGOUT => 'logout',
	);
	
	public function getLogId()
	{
		return $this->logId;
	}

	public function getUserId()
	{
		return $this->userId;
	}

	public function getTimestamp()
	{
		return $this->timestamp;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getMessage()
	{
		return $this->message;
	}

	public function setLogId($logId)
	{
		$this->logId = $logId;
		return $this;
	}

	public function setUserId($userId)
	{
		$this->userId = $userId;
		return $this;
	}

	public function setTimestamp($timestamp)
	{
		$this->timestamp = $timestamp;
		return $this;
	}

	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	public function setMessage($message)
	{
		$this->message = $message;
		return $this;
	}
	public function getIpAddress()
	{
		return $this->ipAddress;
	}

	public function setIpAddress($ipAddress)
	{
		$this->ipAddress = $ipAddress;
		return $this;
	}
	
	public function getTypeName($type = null)
	{
        if (!$type) {
            $type = $this->type;
        }
		return $this->typeName[$type];
	}
    
    /**
     * return logType (convert to string if $toString = true)
     * @param boolean $toString
     * @return string | int
     */
    public function getLogType($toString = false)
    {
        if (!$toString) {
            return $this->logType;
        }
        
        if ($this->logType == self::LOG_TYPE_USER) {
            return 'user';
        }
    }

    public function setLogType($logType)
    {
        $this->logType = $logType;
        return $this;
    }

    /**
     * Check a log is error log or not.
     * @param string $type
     * @return boolean
     */
    public function isTypeError($type = null)
    {
        if (!$type) {
            $type = $this->getType();
        }
        
        switch ($type) {
            case self::TYPE_REGISTER_SUCCESS:
            case self::TYPE_LOGIN_SUCCESS:
            case self::TYPE_ADD_LOGIN_SUCCESS:
            case self::TYPE_DELETE_LOGIN_SUCCESS:
            case self::TYPE_EXTERNAL_AUTHENTICATION_SUCCESS:
            case self::TYPE_VERIFY_TOKEN_SUCCESS:
            case self::TYPE_CHANGE_PASSWORD_SUCCESS:
            case self::TYPE_LOGOUT:
                $isErrorType =  false;
            break;
            default:
                $isErrorType = true;
            break;
        }
        
        return $isErrorType;
    }
    
    /**
     * get time with format.
     * @param int $timestamp
     * @return string
     */
    public function getTime($timestamp = null)
    {
        if (!$timestamp) {
            $timestamp = $this->getTimestamp();
        }
        $time = date('Y-m-d H:i:s', $timestamp);
        return $time;
    }
}
