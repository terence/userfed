<?php
namespace HtApplication\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mail\Message;
use Zend\Log\Logger;
/**
 * Logger.
 */
class LoggerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Configure the logger
        $config = $serviceLocator->get('Config');
        $logConfig = isset($config['log']) ? $config['log'] : array();
		
		//if log through email, setup email transport service and email object
		foreach ($logConfig['writers'] as &$writer) {
			if ($writer['name'] == 'mail') {
				//init transport object for sending
				switch ($writer['options']['transport']) {
					case 'smtp':
						$writer['options']['transport'] = $serviceLocator->get('HtApplication\Mail\Transport\Smtp');
						break;
					case 'file':
						$writer['options']['transport'] = $serviceLocator->get('HtApplication\Mail\Transport\File');					
						break;
				}
				
				$senderAddress = 'senderaddress@senderaddress.com';
				$senderName = "default name - please set in ['mail']['senderName']";
				if (array_key_exists('mail', $config)) {
					if (array_key_exists('senderAddress', $config['mail'])) {
						$senderAddress = $config['mail']['senderAddress'];
					}
					if (array_key_exists('senderName', $config['mail'])) {
						$senderName = $config['mail']['senderName'];
					}
					$mail = new Message();
					$mail->setFrom($senderAddress, $senderName)
						->addTo($writer['options']['toAddress']);
					$writer['options']['mail'] = $mail;					
				}
				break;
			}
		}
        $logger = new Logger($logConfig);
        return $logger;
    }
}
