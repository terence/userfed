<?php

namespace HtApplicationTest\Model\Acl;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

use HtUserTest\Bootstrap;
/* we need use serviceManager for setup connection to database. */
use HtApplication\Test\AbstractHttpControllerTestCase;
use HtApplication\Model\Acl\Navigation as AclNavigation;
use HtApplication\Model\Acl\Role;
use HtApplication\Model\Acl\Resource;
use HtApplication\Model\Acl\PermissionAcl;


class NavigationTest extends AbstractHttpControllerTestCase
{
    public $resources;
    public $permissions;
    public $roles;
    /**
     * @var AclNavigation 
     */
    public $navAcl;
	public function setUp()
	{
		chdir(ROOT_PATH);
		$this->setApplicationConfig(include ROOT_PATH . '/config/application.config.php');
		parent::setUp();
        
        $sl = $this->getApplicationServiceLocator();
        $permissionAcl = $sl->get('HtApplication\Model\Acl\PermissionAcl');
        $permissions = $this->permissions = $permissionAcl->getAll(array(), null, null, 'sort_order')->buffer();
        
        /* @var $model \HtApplication\Model\Acl\Role */
        $model = $sl->get('HtApplication\Model\Acl\Role');
        $roles = $this->roles = $model->getAll()->buffer();
        
        /* @var $resource \HtApplication\Model\Acl\Resource */
        $resource = $sl->get('HtApplication\Model\Acl\Resource');
        $resources = $this->resources = $resource->getAll()->buffer();
        
        $acl = $this->navAcl = new AclNavigation($roles, $resources, $permissions);
	}
	
	public function testBuildResourceTree()
	{
        $acl = $this->navAcl;
        $rTree = $this->invokeMethod($acl, 'buildResourceTree');
        //the level 1 resource should be level 1 node in tree
        $this->assertTrue(array_key_exists('CommonPages', $rTree));
        $this->assertTrue(array_key_exists('UserIdentity:public', $rTree));
        $this->assertTrue(array_key_exists('UserIdentity:private', $rTree));
        $this->assertTrue(array_key_exists('Admin', $rTree));
	}
    
    public function testGetChildrenOnTree()
    {
        $acl = $this->navAcl;
        $rTree = $this->invokeMethod($acl, 'buildResourceTree');
        $this->assertTrue(count($this->invokeMethod($acl, 'getChildrenOnTree', array('CommonPages', $rTree))) > 0);
        $this->assertTrue(count($this->invokeMethod($acl, 'getChildrenOnTree', array('UserIdentity:public', $rTree))) > 0);
        $this->assertTrue(count($this->invokeMethod($acl, 'getChildrenOnTree', array('UserIdentity:private', $rTree))) > 0);
        $this->assertTrue(count($this->invokeMethod($acl, 'getChildrenOnTree', array('Admin', $rTree))) > 0);
    }
    
    /**
    * Call protected/private method of a class.
    *
    * @param object &$object    Instantiated object that we will run method on.
    * @param string $methodName Method name to call
    * @param array  $parameters Array of parameters to pass into method.
    *
    * @return mixed Method return.
    */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
    
    public function testInitNavigationAcl()
    {
        $sl = $this->getApplicationServiceLocator();
        $permissionAcl = $sl->get('HtApplication\Model\Acl\PermissionAcl');
        $permissions = $this->permissions = $permissionAcl->getAll(array(), null, null, 'sort_order')->buffer();
        
        /* @var $model \HtApplication\Model\Acl\Role */
        $model = $sl->get('HtApplication\Model\Acl\Role');
        $roles = $this->roles = $model->getAll()->buffer();
        
        $resource = $sl->get('HtApplication\Model\Acl\Resource');
        $resources = $resource->getAll()->buffer();
        
        $role1 = new Role();
        $role1->exchangeArray(array('role_id' => 'guest', 'parent' => null));
        $role2 = new Role();
        $role2->exchangeArray(array('role_id' => 'member', 'parent' => 'guest'));
        $role3 = new Role();
        $role3->exchangeArray(array('role_id' => 'admin', 'parent' => 'member'));
        $roles = array($role1, $role2, $role3);
        
        $resource1 = new Resource();
        $resource1->exchangeArray(array('resource_id' => 'Resource1', 'parent' => null));
        $resource2 = new Resource();
        $resource2->exchangeArray(array('resource_id' => 'Resource1.1', 'parent' => 'Resource1'));
        $resource3 = new Resource();
        $resource3->exchangeArray(array('resource_id' => 'Resource1.1.1', 'parent' => 'Resource1.1'));
        $resource4 = new Resource();
        $resource4->exchangeArray(array('resource_id' => 'Resource1.2', 'parent' => 'Resource1'));
        $resources = array($resource1, $resource2, $resource3, $resource4);        
        
        $permission1 = new PermissionAcl();
        $permission1->exchangeArray(array(
            'role_id' => 'admin', 'resource_id' => 'Resource1.1.1', 'access' => 1
        ));
        $permissions = array($permission1);
        $acl = $this->navAcl = new AclNavigation($roles, $resources, $permissions);
        $acl->init();
        $this->assertTrue($acl->getAcl()->isAllowed('admin', 'Resource1.1'));
        $this->assertTrue($acl->getAcl()->isAllowed('admin', 'Resource1'));
        $this->assertFalse($acl->getAcl()->isAllowed('admin', 'Resource1.2'));
        
        
        $permission1 = new PermissionAcl();
        $permission1->exchangeArray(array(
            'role_id' => 'admin', 'resource_id' => 'Resource1', 'access' => 1
        ));
        $permissions = array($permission1);
        $acl = $this->navAcl = new AclNavigation($roles, $resources, $permissions);
        $acl->init();
        $this->assertTrue($acl->getAcl()->isAllowed('admin', 'Resource1.1.1'));
        $this->assertTrue($acl->getAcl()->isAllowed('admin', 'Resource1.1'));
        $this->assertTrue($acl->getAcl()->isAllowed('admin', 'Resource1.2'));        
    }
}