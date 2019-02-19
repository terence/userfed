<?php
namespace HtAuthentication\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use AppCore\Db\TableGateway\AbstractTableGateway;

class ApplicationServerTable extends AbstractTableGateway
{
	protected $table = 'application_server';
	
	public function __construct(Adapter $adapter)
	{
		$this->adapter = $adapter;
		$this->resultSetPrototype = new ResultSet();
		$this->resultSetPrototype->setArrayObjectPrototype(new ApplicationServer());
		$this->initialize();
	}
}
