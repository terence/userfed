<?php
namespace HtApplication\Service\Model\Acl;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use HtApplication\Model\Acl\PermissionAcl;

class PermissionAclFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $model = new PermissionAcl();
        $model->setServiceManager($serviceLocator);
        $table = $serviceLocator->get('HtApplication\Model\Acl\PermissionAclTable');
        $model->setTable($table);
        return $model;
    }
}