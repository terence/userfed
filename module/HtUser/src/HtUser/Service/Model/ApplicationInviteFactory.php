<?php
namespace HtUser\Service\Model;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use HtUser\Model\ApplicationInvite;

class ApplicationInviteFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
    {
		$model = new ApplicationInvite();
		$model->setServiceManager($serviceLocator);
		$table = $serviceLocator->get('HtUser\Model\ApplicationInviteTable');
		$model->setTable($table);
		return $model;
    }
}
