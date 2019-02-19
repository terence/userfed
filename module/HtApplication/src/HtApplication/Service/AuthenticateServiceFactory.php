<?php
namespace HtApplication\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Authentication\Storage\Session;
use Zend\Authentication\AuthenticationService;

class AuthenticateServiceFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
    {
		$model = new AuthenticationService();
		/* @var $sessionManager \Zend\Session\SessionManager */
		$sessionManager = $serviceLocator->get("Zend\Session\SessionManager");
		//Initializing Session Storage
        //@note: what we do here basically the same as AuthenticationService storage init
        //but we use custom sessionManager with specified configuration
		/* @var $storage \Zend\Authentication\Storage\StorageInterface */        
        $storage = new Session(null, null, $sessionManager);
		$model->setStorage($storage);		
		return $model;
    }
}