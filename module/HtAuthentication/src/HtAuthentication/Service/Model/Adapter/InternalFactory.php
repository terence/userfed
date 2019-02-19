<?php
namespace HtAuthentication\Service\Model\Adapter;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use HtAuthentication\Model\Adapter\Internal;

class InternalFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
    {
		$model = new Internal();
		$model->setServiceManager($serviceLocator);
		$model->setTable($serviceLocator->get('HtAuthentication\Model\Adapter\InternalTable'));
		return $model;
    }
}
