<?php

namespace HtApplication\Model\Acl;

use AppCore\Db\TableGateway\AbstractTableGateway;
use HtApplication\Model\Acl\PermissionAcl;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\Adapter;

class PermissionAclTable extends AbstractTableGateway
{
    protected $table = 'permission_acl';
    
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new PermissionAcl());
        $this->initialize();
    }
}
