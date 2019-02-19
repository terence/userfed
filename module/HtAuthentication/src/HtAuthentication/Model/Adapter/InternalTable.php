<?php
namespace HtAuthentication\Model\Adapter;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use AppCore\Db\TableGateway\AbstractTableGateway;

class InternalTable extends AbstractTableGateway
{
	protected $table = 'authentication_internal';
	public function __construct(Adapter $adapter)
	{
		$this->adapter = $adapter;
		$this->resultSetPrototype = new ResultSet();
		$this->resultSetPrototype->setArrayObjectPrototype(new Internal());
		$this->initialize();
	}
}