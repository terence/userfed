<?php
/* 
 * Create table class
 */

namespace HtApplication\Service;
 
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
 
class CommonModelTableAbstractFactory implements AbstractFactoryInterface
{
	/**
	 * 
	 * @param \Zend\ServiceManager\ServiceLocatorInterface $locator
	 * @param type $name
	 * @param type $requestedName
	 * @return type
	 * @SuppressWarnings(PHPMD)
	 */
    public function canCreateServiceWithName(ServiceLocatorInterface $locator, $name, $requestedName)
    {
        return (substr($requestedName, -5) === 'Table');
    }
 
	/**
	 * 
	 * @param \Zend\ServiceManager\ServiceLocatorInterface $locator
	 * @param type $name
	 * @param type $requestedName
	 * @SuppressWarnings(PHPMD)
	 */
    public function createServiceWithName(ServiceLocatorInterface $locator, $name, $requestedName)
    {
        $db = $locator->get('Zend\Db\Adapter\Adapter');
        $tablemodel = new $requestedName($db);
         
        return $tablemodel;
    }
}