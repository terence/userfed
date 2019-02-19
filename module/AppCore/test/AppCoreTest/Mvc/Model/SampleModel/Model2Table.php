<?php
namespace AppCoreTest\Mvc\Model\SampleModel;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use AppCore\Db\TableGateway\AbstractTableGateway;

class Model2Table extends AbstractTableGateway
{
	protected $table = 'model2table';
	public function __construct(Adapter $adapter)
	{
		$this->adapter = $adapter;
		$this->resultSetPrototype = new ResultSet();
		$this->resultSetPrototype->setArrayObjectPrototype(new Model2());
		$this->initialize();
	}
}
