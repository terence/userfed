<?php

namespace HtApplication\Service\Model;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use HtApplication\Model\LogCollection;

class LogCollectionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sl)
    {
        $model = new LogCollection();
        $model->setServiceManager($sl);
        $model->setTable($sl->get('HtApplication\Model\LogTable'));
        return $model;
    }
}
