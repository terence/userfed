<?php
namespace HtAuthentication\Model;

use AppCore\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

class OrganisationTable extends AbstractTableGateway
{
	protected $table = 'uf_organisation';
	
	public function __construct(Adapter $adapter)
	{
		$this->adapter = $adapter;
		$this->resultSetPrototype = new ResultSet();
		$this->resultSetPrototype->setArrayObjectPrototype(new Organisation());
		$this->initialize();
	}
}
