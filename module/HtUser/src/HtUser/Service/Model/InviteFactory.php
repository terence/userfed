<?php
namespace HtUser\Service\Model;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use HtUser\Model\Invite;

class InviteFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
    {
		$model = new Invite();
		$model->setServiceManager($serviceLocator);
		$table = $serviceLocator->get('HtUser\Model\InviteTable');
		$model->setTable($table);
		return $model;
    }
}
