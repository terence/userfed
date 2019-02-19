<?php
namespace HtApplication\Service\Model\Acl;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use HtApplication\Model\Acl\Role;

class RoleFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
    {
		$model = new Role();
		$model->setServiceManager($serviceLocator);
        $table = $serviceLocator->get('HtApplication\Model\Acl\RoleTable');
		$model->setTable($table);
		return $model;
    }
}