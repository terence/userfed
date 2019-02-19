<?php
namespace HtApplication\Controller;

use Zend\EventManager\EventManagerInterface;
use Zend\Session\Container;
use AppCore\Mvc\Controller\AbstractActionController as AppCoreAbstractActionController;

use Zend\Http\PhpEnvironment\RemoteAddress;
use HtApplication\Model\Acl\Role;
use Zend\Authentication\AuthenticationService;

use HtApplication\Model\Log;

abstract class AbstractActionController extends AppCoreAbstractActionController
{
	public function setEventManager(EventManagerInterface $events) 
	{
		parent::setEventManager($events);
		$controller = $this;
		$events->attach('dispatch', function ($e) use ($controller) {
            $sl = $controller->getServiceLocator();
            $session = $sl->get('Zend\Session\SessionManager');
            $container = new Container('initialized', $session);
            /**
             * Session Hijacking and Fixation
             * @see http://framework.zend.com/manual/1.12/en/zend.session.global_session_management.html#zend.session.global_session_management.session_identifiers.hijacking_and_fixation
             * Move SESSION code from Module Bootstrap here so we can mock SessionManager in unit test code
             */
            if (!isset($container->init)) {
                $session->start();
                $container->init = 1;
            } else {
                $session->start();
            }
        
			if ($controller->hasIdentity()) {
				$loggedUser = $sl->get('LoggedInUser');
				$errorMessage = null;
				if (!$loggedUser || $loggedUser->getIsDeleted()) {
					$errorMessage = $controller->translate('Your account has been deleted.');
				} else if (!$loggedUser->getIsEnabled()) {
					$errorMessage = $controller->translate('Your account has been susspended.');
				}
				
				if ($errorMessage) {
					$controller->getAuthenticationService()->clearIdentity();
					$controller->flashMessenger()->addErrorMessage($errorMessage);
					return $controller->redirect()->toRoute('login');
				}
			}
			
			if (!$controller->permission()->isAllow($e)) {
				return $controller->permission()->handleFailedAuthorize($e);
			}
		}, 100); // execute before executing action logic
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public function isAdmin()
	{
        $sl = $this->getServiceLocator();
		$loggedInUser = $sl->get("LoggedInUser");
		if ($loggedInUser) {
			$result = $loggedInUser->getRole();
			if (in_array(Role::ADMIN_ROLE, $result)) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Log user in by store user info in session
	 */
	protected function logUserIn($identity, AuthenticationService $auth = null)
	{
        $sl = $this->getServiceLocator();
        if (!isset($auth)) {
            $auth = $sl->get("AuthenticateService");
        }
		
		$auth->getStorage()->write($identity);
	}
	
	/**
	 * @param array $data
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