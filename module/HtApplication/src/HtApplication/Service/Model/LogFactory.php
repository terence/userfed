<?php
namespace HtApplication\Service\Model;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use HtApplication\Model\Log;

class LogFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
    {
		$model = new Log();
		$model->setTable($serviceLocator->get('HtApplication\Model\LogTable'));
		$model->setServiceManager($serviceLocator);
		return $model;
    }
}