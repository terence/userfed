<?php

namespace HtUser\Model;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\Adapter;
use AppCore\Db\TableGateway\AbstractTableGateway;
use HtUser\Model\ApplicationInvite;

class ApplicationInviteTable extends AbstractTableGateway
{
	protected $table = 'application_invite';
	
	public function __construct(Adapter $adapter)
	{
		$this->adapter = $adapter;
		$this->resultSetPrototype = new ResultSet();
		$this->resultSetPrototype->setArrayObjectPrototype(new ApplicationInvite());
		$this->initialize();
	}	
}