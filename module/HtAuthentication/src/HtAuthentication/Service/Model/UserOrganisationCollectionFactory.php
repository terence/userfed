<?php
namespace HtAuthentication\Service\Model;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use HtAuthentication\Model\UserOrganisationCollection;

class UserOrganisationCollectionFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
    {
		$model = new UserOrganisationCollection();
		$model->setServiceManager($serviceLocator);
		$model->setTable($serviceLocator->get('HtAuthentication\Model\UserOrganisationTable'));
		return $model;
    }
}
