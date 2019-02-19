<?php
namespace HtAuthentication\Service\Model;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use HtAuthentication\Model\UserApplicationCollection;

class UserApplicationCollectionFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
    {
		$model = new UserApplicationCollection();
		$model->setServiceManager($serviceLocator);
		$model->setTable($serviceLocator->get('HtAuthentication\Model\UserApplicationTable'));
		return $model;
    }
}
