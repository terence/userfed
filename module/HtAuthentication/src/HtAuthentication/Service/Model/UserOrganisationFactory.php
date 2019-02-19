<?php
namespace HtAuthentication\Service\Model;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use HtAuthentication\Model\UserOrganisation;

class UserOrganisationFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
    {
		$model = new UserOrganisation();
		$model->setServiceManager($serviceLocator);
		$model->setTable($serviceLocator->get('HtAuthentication\Model\UserOrganisationTable'));
		return $model;
    }
}
