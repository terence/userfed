<?php
namespace HtAuthentication\Service\Model;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use HtAuthentication\Model\ApplicationServerCollection;

class ApplicationServerCollectionFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
    {
		$model = new ApplicationServerCollection();
		$model->setServiceManager($serviceLocator);
		$model->setTable($serviceLocator->get('HtAuthentication\Model\ApplicationServerTable'));
		return $model;
    }
}
