<?php
namespace HtAuthentication\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use AppCore\Db\TableGateway\AbstractTableGateway;

class AuthenticationAccountTable extends AbstractTableGateway
{
	protected $table = 'authentication_account';
	public function __construct(Adapter $adapter)
	{
		$this->adapter = $adapter;
		$this->resultSetPrototype = new ResultSet();
		$this->resultSetPrototype->setArrayObjectPrototype(new AuthenticationAccount());
		$this->initialize();
	}
}
