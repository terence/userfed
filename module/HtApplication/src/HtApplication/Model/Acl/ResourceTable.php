<?php
namespace HtApplication\Model\Acl;

use Zend\Db\Adapter\Adapter;
use AppCore\Db\TableGateway\AbstractTableGateway;

use Zend\Db\ResultSet\ResultSet;
use HtApplication\Model\Acl\Resource;

class ResourceTable extends AbstractTableGateway
{
    protected $table = 'permission_resource';
	  
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Resource());
        $this->initialize();
    }
}