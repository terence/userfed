<?php
namespace HtAuthentication\Model;
use AppCore\Mvc\Model\Collection;
use AppCore\Mvc\Model\SoftDeleteableInterface;
use Zend\Db\Sql\Select;

class AuthenticationAccountCollection extends Collection implements SoftDeleteableInterface
{
	public function getAllLogin($userId, $limit = null, $offset = null)
	{
		$sm = $this->getServiceManager();
		
		$accountTable = $this->getTableName();
		
		$authInternal = $sm->get('AuthAccountInternal');
		/* @var $authInternal Adapter\Internal */
		
		$internalTable = $authInternal->getTableName();
		
		$oauth = $sm->get('AuthAccount\OAuth');
		/* @var $oauth \HtAuthentication\Model\Adapter\OAuth */
		$oauthTable = $oauth->getTableName();
		
		$rowset = $this->getTableGateway()->select(function (Select $select) use ($limit, $offset, $userId, $internalTable, $accountTable, $oauthTable) {
			$select->join($internalTable, "{$accountTable}.authentication_account_id = {$internalTable}.authentication_account_id", Select::SQL_STAR, Select::JOIN_LEFT)
				->join($oauthTable, "{$accountTable}.authentication_account_id = {$oauthTable}.authentication_account_id", Select::SQL_STAR, Select::JOIN_LEFT);
            
            /* auth_account_id for admin identity management. */
			$select->columns(array('auth_account_id' => "authentication_account_id", '*'));
			$select->where(array('user_id' => $userId));
			if ($limit) {
				$select->limit($limit);
			}
			if ($offset) {
				$select->offset($offset);
			}
		});
		
		return $rowset;
	}
	
}