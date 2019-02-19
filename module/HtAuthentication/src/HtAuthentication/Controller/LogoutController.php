<?php
namespace HtAuthentication\Controller;

use HtAuthentication\Controller\TokenBaseController;
use Zend\View\Model\JsonModel;
use Zend\Mvc\MvcEvent;

class LogoutController extends TokenBaseController
{	
	public function logoutAction()
	{
		$this->getAuthenticationService()->clearIdentity();
		return $this->redirect()->toUrl('/');
	}
	
	/**
	 * Delete current session and token
	 */
	public function ssoLogoutAction()
	{
		$result = $this->verifySecretAndToken();
		if ($result instanceof JsonModel) {
			return $result;
		}
		$token = $this->getToken();
		$connection = $this->getConnection();
		try {
			$connection->beginTransaction();
			$token->delete();
			$connection->commit();
		} catch(\Exception $ex) {
			$connection->rollback();
			return new JsonModel(array('errorCode' => 1, 'message' => 'System error'));
		}
		return new JsonModel(array('errorCode' => 0, 'message' => 'Logout user from User Federation successfull'));
	}
}