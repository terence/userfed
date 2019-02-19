<?php
namespace HtUser\Model;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\Adapter;
use AppCore\Db\TableGateway\AbstractTableGateway;

class InviteTable extends AbstractTableGateway
{
	protected $table = 'invite';
	
	public function __construct(Adapter $adapter)
	{
		$this->adapter = $adapter;
		$this->resultSetPrototype = new ResultSet();
		$this->resultSetPrototype->setArrayObjectPrototype(new Invite());
		$this->initialize();
	}	
}