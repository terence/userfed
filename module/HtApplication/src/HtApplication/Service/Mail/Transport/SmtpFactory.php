<?php
namespace HtApplication\Service\Mail\Transport;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Mail\Transport\SmtpOptions;
use Zend\Mail\Transport\Smtp;

class SmtpFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
    {
		$config = $serviceLocator->get('config');
		$config = $config['emailTransport']['smtp'];
		$mailTransport = new Smtp();
		$options  = new SmtpOptions(array(
				'name'	=> $config['host'],
				'host'  => $config['host'],
				'port'	=> $config['port'],
				'connection_class'	=> 'plain',
				'connection_config' => array(
						'username' => $config['username'],
						'password' => $config['password'],
						'ssl' => $config['ssl'],
				),
		));
		$mailTransport->setOptions($options);
		return $mailTransport;
    }
}
