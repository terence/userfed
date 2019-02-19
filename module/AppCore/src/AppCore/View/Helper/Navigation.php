<?php
/* 
 * A custom navigation based on Zend\View\Helper\Navigation
 * to support multiple roles in acl
 */
namespace AppCore\View\Helper;

use Zend\View\Helper\Navigation as ZendNavigation;

class Navigation extends ZendNavigation
{
    protected $roles = array();

    public function getRoles()
    {
        return $this->roles;
    }

    public function setRoles($roles)
    {
        $this->roles = $roles;
        return $this;
    }
    
    /**
     * We need replace role via multi roles here.
     * @param array $params
     * @return boolean
     */
    protected function isAllowed($params)
    {
        $params['role'] = $this->getRoles();
        return parent::isAllowed($params);
    }

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
}