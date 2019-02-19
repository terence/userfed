<?php
namespace AppCore\Mvc\Controller;

use AppCore\Exception;
use Zend\Mvc\Controller\AbstractActionController as ZendAbstractActionController;
use Zend\Session\Container;

/**
 * AppCore Abstract controller
 *
 * Convenience methods for pre-built plugins (@see __call):
 *
 * @method \AppCore\Mvc\Controller\Plugin\PagingParams pagingParams()
 * @method \AppCore\Mvc\Controller\Plugin\BaseUrl baseUrl()
 * @method \AppCore\Mvc\Controller\Plugin\AddEmptyOption addEmptyOption()
 * @method \AppCore\Mvc\Controller\Plugin\SuccessMessage successMessage()
 * @method \AppCore\Mvc\Controller\Plugin\ExceptionMessage exceptionMessage()
 * @method \AppCore\Mvc\Controller\Plugin\Download download()
 * @method \AppCore\Mvc\Controller\Plugin\Permission permission()
 * @method \AppCore\Mvc\Controller\Plugin\Referer referer()
 */
abstract class AbstractActionController extends ZendAbstractActionController
{
	/**
	* Return config in application config
	*
	* @return array|object
	*/
	public function getConfig()
	{
        return $this->getServiceLocator()->get('Application')->getConfig();
	}
	
	/**
	 * If user already loggedin then he has identify
	 * 
	 * @return boolean
	 */
	public function hasIdentity()
	{
		$hasIdentity = $this->getAuthenticationService()->hasIdentity();
	
		return $hasIdentity;
	}
	
	/**
	 * Call getting identity function from storage
	 */
	public function getIdentity()
	{
		return $this->getAuthenticationService()->getIdentity();
	}
	
	/**
	 * Get id of logged in user
	 * 
	 * @return int | false
	 */
	public function getLoggedInUserId()
	{
		if ($this->hasIdentity()) {
			$identity = $this->getIdentity();
			if (method_exists($identity, "getUserId")) {
				return $identity->getUserId();
			} else if (isset($identity->userId)) {
				return $identity->userId;
			}
		}
		return false;
	}
	
	/**
	 * @return \Zend\Authentication\AuthenticationService
	 */
	public function getAuthenticationService()
	{
		if (!isset($this->authService)) {
			$this->authService = $this->getServiceLocator()->get('AuthenticateService');//new AuthenticationService();
		}
		return $this->authService;
	}
	
	/**
	 * @return Zend\Db\Adapter\Driver\ConnectionInterface
	 */
	public function getConnection()
	{
		/* @var $adapter \Zend\Db\Adapter\Adapter */
		$adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
		$connection = $adapter->getDriver()->getConnection();
		return $connection;
	}
	
	public function translate($msg)
	{
		$translate = $this->getServiceLocator()->get('viewHelperManager')->get('translate');
		return $translate($msg);
	}
	
	// @TODO: messages which displayed when user have not enough permission to access a resoure. Need to move to an appropriate place later
	protected $accessDeniedMessages = array(
		"FirstetagDeal:Deal" => "You must upgrade your account type to <b>Silver</b> to submit/edit deal.",
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
	 * With neutral module like Gallery which can be used in both admin/frontend
	 * Gallery controllers need to know which layout to render.
	 * If default layout appear, then neutral module will use this layout.
	 * @var string
	 */
	public static $defaultLayout = null;
	
	/**
	 * @param string $layoutPath
	 */
	public function setDefaultLayout($layoutPath)
	{
		$session = new Container('AppCoreApplication');
		$session->defaultLayout = $layoutPath;
	}
	
	/**
	 * @return string
	 */
	public function getDefaultLayout()
	{
		$session = new Container('AppCoreApplication');
		return $session->defaultLayout;
	}
	
	/**
	 * Set default local from config
	 */
	public function setLocal()
	{
		$sl = $this->getServiceLocator();
		$translator = $sl->get('translator');
		
		$config = $this->getConfig();
		
		if (array_key_exists('i18n', $config)) {
			if (array_key_exists('locale', $config['i18n'])) {
				if (!empty($config['i18n']['locale'])) {
					/* @var $translator \Zend\I18n\Translator\Translator */
					$translator->setLocale($config['i18n']['locale']);
					$translator->setFallbackLocale('en_US');
					
					/* @var $df \Zend\I18n\View\Helper\DateFormat */
					$df = $sl->get('ViewHelperManager')->get('dateFormat');
					$df->setLocale($config['i18n']['locale']);
				}
			}
		}
	}
	
	public function setTimezone()
	{
		$sl = $this->getServiceLocator();
		$config = $this->getConfig();
		
		if (array_key_exists('i18n', $config)) {
			if (array_key_exists('timezone', $config['i18n'])) {
				if (!empty($config['i18n']['timezone'])) {
					/* @var $df \Zend\I18n\View\Helper\DateFormat */
					$df = $sl->get('ViewHelperManager')->get('dateFormat');
					$df->setTimezone($config['i18n']['timezone']);
				}
			}
		}		
	}
	
	/**
	 * Set currency and currency format
	 */
	public function setCurrency()
	{
		$sl = $this->getServiceLocator();
		$cf = $sl->get('ViewHelperManager')->get('currencyFormat');
		$config = $this->getConfig();
		if (array_key_exists('i18n', $config)) {
			if (array_key_exists('currency', $config['i18n'])) {
				if (!empty($config['i18n']['currency'])) {
					/* @var $cf \Zend\I18n\View\Helper\CurrencyFormat */
					$cf->setCurrencyCode($config['i18n']['currency']);
				}
			}
		}
	}
	
	public function isPublicViewableException(\Exception $e)
	{
		if ($e->getCode() == Exception::PUBLIC_VIEWABLE_ERROR_CODE) {
			return true;
		}
		return false;
	}
	
	/**
	 * Throw out an exception with a specific code. We will capture the message of exceptions with this code and show user it in an our custom error notification.
	 * Other exceptions (system error with professional message) will be thrown or only show user an general message like "System error" or something like that.
	 *
	 * @param string $msg
	 * @throws \Exception
	 */
	protected function throwPublicException($message)
	{
		throw new Exception($message, Exception::PUBLIC_VIEWABLE_ERROR_CODE);
	}
}