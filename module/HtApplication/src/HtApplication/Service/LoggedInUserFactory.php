<?php
/* 
 * Build logged in user info
 */
namespace HtApplication\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LoggedInUserFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $auth = $serviceLocator->get('AuthenticateService');
        
        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
            $userId = null;
            if (method_exists($identity, "getUserId")) {
                $userId = $identity->getUserId();
            } else if (isset($identity->userId)) {
                $userId = $identity->userId;
            }

            /* @var $user \HtUser\Model\User */
            $user = clone $serviceLocator->get('User');
            //help to identify if user is deleted by admin during his session
            $user->disableSoftDelete();
            if ($user->load($userId)) {
                return $user;
            }
        }
        /**
         * ServiceManager not allowed return a null value in a Factory. So we need return a different null value here.
         */
        return false; 
    }
}

