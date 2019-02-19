<?php
namespace HtApplication\Service\Model\Acl;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use HtApplication\Model\Acl\Resource;

class ResourceFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
    {
		$model = new Resource();
		$model->setServiceManager($serviceLocator);
		$model->setTable($serviceLocator->get('HtApplication\Model\Acl\ResourceTable'));
		return $model;
    }
}