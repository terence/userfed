<?php
namespace HtApplication\Service\Model\Acl;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use HtApplication\Model\Acl\Acl;

class AclFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
    {
		$model = new Acl();
		$model->setServiceManager($serviceLocator);
		$model->setTable($serviceLocator->get('HtApplication\Model\Acl\AclTable'));
		return $model;
    }
}