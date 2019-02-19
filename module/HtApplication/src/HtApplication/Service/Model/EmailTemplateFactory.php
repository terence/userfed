<?php
namespace HtApplication\Service\Model;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use HtApplication\Model\EmailTemplate;

class EmailTemplateFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
    {
		$model = new EmailTemplate();
		$model->setServiceManager($serviceLocator);
		$model->setTable($serviceLocator->get('HtApplication\Model\EmailTemplateTable'));
		return $model;
    }
}