<?php
namespace HtApplication\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\AbstractTableGateway;

class MailQueueTable extends AbstractTableGateway
{
    protected $table = 'mail_queue';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new SystemSetting());
        $this->initialize();
    }
}