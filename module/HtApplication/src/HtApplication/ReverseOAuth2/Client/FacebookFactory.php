<?php
namespace HtApplication\ReverseOAuth2\Client;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use HtApplication\ReverseOAuth2\Client\Facebook;
class FacebookFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator) {
        $me = new Facebook();
        $cf = $serviceLocator->get('Config');
        $me->setOptions(new \ReverseOAuth2\ClientOptions($cf['reverseoauth2']['facebook']));
        return $me;
    }
}