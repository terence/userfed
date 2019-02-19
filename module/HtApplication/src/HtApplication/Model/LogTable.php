<?php
namespace HtApplication\Model;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\Adapter;
use AppCore\Db\TableGateway\AbstractTableGateway;

class LogTable extends AbstractTableGateway
{
	protected $table = 'user_log';
	
	public function __construct(Adapter $adapter)
	{
		$this->adapter = $adapter;
		$this->resultSetPrototype = new ResultSet();
		$this->resultSetPrototype->setArrayObjectPrototype(new Log());
		$this->initialize();
	}	
}