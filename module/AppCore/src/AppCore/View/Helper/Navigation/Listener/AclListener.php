<?php
/* 
 * Handle multiple roles checking
 */
namespace AppCore\View\Helper\Navigation\Listener;

use Zend\View\Helper\Navigation\Listener\AclListener as ZendAclListener;
use Zend\EventManager\Event;

class AclListener extends ZendAclListener
{
    /**
     * Copy code from Zend\View\Helper\Navigation\Listener\AclListener::accept and enanble multiple roles
     * 
     * @param  Event    $event
     * @return bool
     */
    public static function accept(Event $event)
    {
        $accepted = true;
        $params   = $event->getParams();
        $acl      = $params['acl'];
        $page     = $params['page'];
        $roles    = $params['role'];

        if (!$acl) {
            return $accepted;
        }
        $resource  = $page->getResource();
        $privilege = $page->getPrivilege();

        if ($resource || $privilege) {
            foreach ($roles as $role) {
                $accepted = $acl->hasResource($resource)
                            && $acl->isAllowed($role, $resource, $privilege);
                
                if ($accepted) {
                    break;
                }
            }
        }
        
        
        return $accepted;
    }
}
