<?php
namespace HtAuthentication\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use AppCore\Db\TableGateway\AbstractTableGateway;

class UserApplicationTable extends AbstractTableGateway
{
	protected $table = 'user_application';
	public function __construct(Adapter $adapter)
	{
		$this->adapter = $adapter;
		$this->resultSetPrototype = new ResultSet();
		$this->resultSetPrototype->setArrayObjectPrototype(new UserApplication());
		$this->initialize();
	}
}
