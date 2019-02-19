<?php
namespace HtApplication\Service\Session\SaveHandler;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Session\SaveHandler\DbTableGateway as DbTableGatewaySaveHandler;
use Zend\Session\SaveHandler\DbTableGatewayOptions as DbTableGatewaySaveHandlerOptions;
class DbTableGatewayFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
    {
		$config = $serviceLocator->get('Config');
		//default name
		$tableName = 'web_session';
		if (array_key_exists('session_save_handler', $config)) {
			if (array_key_exists('db', $config['session_save_handler'])) {
				if (array_key_exists('table', $config['session_save_handler']['db'])) {
					$tableName = $config['session_save_handler']['db']['table'];
				}
			}
		}
		$adapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');
		$tableGateway = new TableGateway($tableName, $adapter);
		$saveHandler = new DbTableGatewaySaveHandler(
			$tableGateway,
			new DbTableGatewaySaveHandlerOptions()
		);
		return $saveHandler;		
    }
}