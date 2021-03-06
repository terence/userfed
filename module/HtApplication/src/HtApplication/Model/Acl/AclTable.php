<?php
namespace HtApplication\Model\Acl;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use AppCore\Db\TableGateway\AbstractTableGateway;

class AclTable extends AbstractTableGateway
{
    protected $table = 'permission_acl';
	  
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
}