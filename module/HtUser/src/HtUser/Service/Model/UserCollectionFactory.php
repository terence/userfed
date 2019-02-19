<?php
namespace HtUser\Service\Model;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use HtUser\Model\UserCollection;

class UserCollectionFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
    {
		$model = new UserCollection();
		$model->setServiceManager($serviceLocator);
		$model->setTable($serviceLocator->get('HtUser\Model\UserTable'));
		return $model;
    }
}
