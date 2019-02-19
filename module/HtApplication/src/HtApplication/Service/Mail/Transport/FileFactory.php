<?php
namespace HtApplication\Service\Mail\Transport;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Mail\Transport\File;
use Zend\Mail\Transport\FileOptions;

class FileFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
    {
		$config = $serviceLocator->get('config');
		$config = $config['emailTransport']['file'];

		//create mail folder if not exist
		if (!file_exists($config['path']) and !is_dir($config['path'])) {
			mkdir($config['path']);
		}
		$mailTransport = new File();					
		$options  = new FileOptions(array(
			'path'	=> $config['path']
		));
		$mailTransport->setOptions($options);
		return $mailTransport;
    }
}
