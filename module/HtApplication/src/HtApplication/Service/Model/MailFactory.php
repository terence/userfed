<?php
namespace HtApplication\Service\Model;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use HtApplication\Model\Mail;

class MailFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
    {
		$model = new Mail();
		$model->setServiceLocator($serviceLocator);
		return $model;
    }
}