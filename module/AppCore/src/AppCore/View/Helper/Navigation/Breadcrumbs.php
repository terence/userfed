<?php
/* 
 * support multiple roles in acl
 */

namespace AppCore\View\Helper\Navigation;

use Zend\View\Helper\Navigation\Breadcrumbs as ZendBreadcrumbs;

class Breadcrumbs extends ZendBreadcrumbs
{
    /**
     * Attaches default ACL listeners, if ACLs are in use
     */
    protected function setDefaultListeners()
    {
        if (!$this->getUseAcl()) {
            return;
        }
        $this->getEventManager()->getSharedManager()->attach(
            'Zend\View\Helper\Navigation\AbstractHelper',
            'isAllowed',
            array('AppCore\View\Helper\Navigation\Listener\AclListener', 'accept')
        );
    }
    
    /**
     * We need replace role via multiple roles value to handle multiple roles
     * @param array $params
     * @return boolean
     */
    protected function isAllowed($params)
    {
        $navigation = $this->getServiceLocator()->get('navigation');
        $roles = $navigation->getRoles();
        $params['role'] = $roles;
        return parent::isAllowed($params);
    }
}