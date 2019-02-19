<?php
namespace HtUser\Service\Model;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use HtUser\Model\User;

class UserFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
    {
		$model = new User();
		$model->setServiceManager($serviceLocator);
		$model->setTable($serviceLocator->get('HtUser\Model\UserTable'));
		return $model;
    }
}
