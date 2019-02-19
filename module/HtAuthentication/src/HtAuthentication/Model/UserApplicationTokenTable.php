<?php
/**
 * Manage token assign for user-application authorization
 */
namespace HtAuthentication\Model;

use AppCore\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

class UserApplicationTokenTable extends AbstractTableGateway
{
	protected $table = 'user_application_token';
	
	public function __construct(Adapter $adapter)
	{
		$this->adapter = $adapter;
		$this->initialize();
	}
}
