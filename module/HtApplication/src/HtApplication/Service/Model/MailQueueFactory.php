<?php
namespace HtApplication\Service\Model;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use HtApplication\Model\MailQueue;

class MailQueueFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
    {
		$model = new MailQueue();
		$model->setServiceManager($serviceLocator);
		$model->setTable($serviceLocator->get('HtApplication\Model\MailQueueTable'));
		return $model;
    }
}