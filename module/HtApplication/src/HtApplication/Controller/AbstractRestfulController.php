<?php

namespace HtApplication\Controller;

use Zend\Mvc\Controller\AbstractRestfulController as ZendAbstractRestful;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;

/**
 * @method \AppCore\Mvc\Controller\Plugin\Permission permission()
 */
abstract class AbstractRestfulController extends ZendAbstractRestful
{
    public function setEventManager(EventManagerInterface $events)
    {
        parent::setEventManager($events);
        $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'isAllowed'), 100); // execute before executing action logic
    }
    
    /**
     * Check permission acl of user.
     * @param \Zend\Mvc\MvcEvent $e
     * @return type
     */
    public function isAllowed(MvcEvent $e)
    {
        if (!$this->permission()->isAllow($e)) {
            return $this->permission()->handleFailedAuthorize($e);
        }
    }
    
    /**
	 * @return \Zend\Db\Adapter\Driver\ConnectionInterface
	 */
	public function getConnection()
	{
		/* @var $adapter \Zend\Db\Adapter\Adapter */
		$adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
		$connection = $adapter->getDriver()->getConnection();
		return $connection;
	}
	
    /**
     * 
     * @param string $msg
     * @return string
     */
	public function translate($msg)
	{
		$translate = $this->getServiceLocator()->get('viewHelperManager')->get('translate');
		return $translate($msg);
	}
	
	// @TODO: messages which displayed when user have not enough permission to access a resoure. Need to move to an appropriate place later
	protected $accessDeniedMessages = array(
	);
	/**
	* @TODO: get messages which displayed when user have not enough permission to access a resoure. Need to move to an appropriate place later
	*
	* @param string $resoucreId
	* @return string
	*/
	public function getAccessDeniedMessage($resoucreId, $priviledge)
	{
		$message = "You do not have enough permission to access this page.";
		if (array_key_exists($resoucreId . ":" . $priviledge, $this->accessDeniedMessages)) {
			$message = $this->accessDeniedMessages[$resoucreId . ":" . $priviledge];
		} else if (array_key_exists($resoucreId, $this->accessDeniedMessages)) {
			$message = $this->accessDeniedMessages[$resoucreId];
		}
		return $message;
	}
    
    /**
     * 
     * @return \Zend\Authentication\AuthenticationService;
     */
    public function getAuthenticateService()
    {
        return $this->getServiceLocator()->get('AuthenticateService');
    }
    
    public function hasIdentity()
    {
        return $this->getAuthenticateService()->hasIdentity();
    }
    
    public function getIdentity()
    {
        return $this->getAuthenticateService()->getIdentity();
    }
    
    public function getLoggedInUserId()
    {
        $user = $this->getServiceLocator()->get('LoggedInUser');
        if ($user) {
            return $user->getUserId();
        }
        return null;
    }
    
    /**
     * Trigger event to log (log error and send notification to developer) when we don't want throw exception.
     * @param \Exception $exception
     * @param int $statusCode Will be set to status for response. default 500 (Internal Server Error)
     * @param string $message Message return to client
     * @return JsonModel 
     */
    protected function triggerException($exception, $statusCode = 500, $message = null)
    {
        $application = $this->getServiceLocator()->get('Application');
        $event = clone $this->getEvent();
        $event->setName(MvcEvent::EVENT_DISPATCH_ERROR);
        $event->setParam('exception', $exception);
        /* @var $application \Zend\Mvc\Application */
        $application->getEventManager()->trigger($event);
        $this->getResponse()->setStatusCode($statusCode);
        if ($message === null) {
            $message = $this->translate('The request failed due to an internal error.');
        }
        return new JsonModel(array(
            'success' => false,
            'message' => $message
        ));
    }
    
    /**
     * Write user activity log
     * @param string $type
     * @param string $message
     * @param string $data
     * @return int
     */
    protected function writeLog($type, $message = '', $data = array())
    {
        $log = $this->getServiceLocator()->get('HtApplication\Model\Log');
        /* @var $log \HtApplication\Model\Log */
        $log->exchangeArray($data);
        if (!isset($data['user_id'])) {
            $log->setUserId($this->getLoggedInUserId());
        }

        $remote = new RemoteAddress();
        $ipAddress = $remote->getIpAddress();
        $log->setIpAddress($ipAddress);

        $log->setMessage($message);
        $log->setLogId(null);
        $log->setTimestamp(time());
        $log->setLogType(Log::LOG_TYPE_USER);
        $log->setType($type);
        $logId = $log->save();
        return $logId;
    }
}
