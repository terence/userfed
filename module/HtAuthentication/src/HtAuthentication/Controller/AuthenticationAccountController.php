<?php

namespace HtAuthentication\Controller;

use HtApplication\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Null as PaginatorNull;

class AuthenticationAccountController extends AbstractActionController
{
	public function loginManagementAction()
	{
		$sl = $this->getServiceLocator();
		$page = $this->params()->fromQuery('page');
		$userId = $this->getLoggedInUserId();
		
		$identity = $this->getAuthenticationService()->getIdentity();
		$authAccountCollection = $sl->get('AuthAccountCollection');
		/* @var $authAccountCollection \HtAuthentication\Model\AuthenticationAccountCollection */
		
		$hasInternalLogin = false;
		$count = $authAccountCollection->count(array('user_id' => $userId, 'type' => 'internal'));
		if ($count > 0) {
			$hasInternalLogin = true;
		}
		
		$count = $authAccountCollection->count(array('user_id' => $userId));
		
		$pagingParams = $this->pagingParams();
		$pagingParamsData = $pagingParams->get($count);
		
		$paginator = new Paginator(new PaginatorNull($count));
		$paginator->setItemCountPerPage($pagingParamsData['itemPerPage']);
		$paginator->setCurrentPageNumber($page);
		
		$logins = $authAccountCollection->getAllLogin($userId, $pagingParamsData['limit'], $pagingParamsData['offset']);
		
		$errorMessage = '';
		$successMessage = '';
		
		if ($this->flashMessenger()->hasErrorMessages()) {
			$errorMessage = implode('<br />', $this->flashMessenger()->getErrorMessages());
		}
		
		if ($this->flashMessenger()->hasSuccessMessages()) {
			$successMessage = implode('<br />', $this->flashMessenger()->getSuccessMessages());
		}
		
		return array(
			'logins' => $logins,
			'totalLogins' => $count,
			'paginator' => $paginator,
			'errorMessage' => $errorMessage,
			'successMessage' => $successMessage,
			'identity' => $identity,
			'hasInternalLogin' => $hasInternalLogin
		);
	}
}
