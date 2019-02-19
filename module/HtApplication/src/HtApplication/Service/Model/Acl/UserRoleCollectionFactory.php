<?php
namespace HtApplication\Service\Model\Acl;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use HtApplication\Model\Acl\UserRoleCollection;

class UserRoleCollectionFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
    {
		$model = new UserRoleCollection();
		$model->setServiceManager($serviceLocator);
		$model->setTable($serviceLocator->get('HtApplication\Model\Acl\UserRoleTable'));
		return $model;
    }
}
