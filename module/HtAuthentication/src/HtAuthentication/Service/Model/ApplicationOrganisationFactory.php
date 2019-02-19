<?php
namespace HtAuthentication\Service\Model;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use HtAuthentication\Model\ApplicationOrganisation;

class ApplicationOrganisationFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
    {
		$model = new ApplicationOrganisation();
		$model->setServiceManager($serviceLocator);
		$model->setTable($serviceLocator->get('HtAuthentication\Model\ApplicationOrganisationTable'));
		return $model;
    }
}
