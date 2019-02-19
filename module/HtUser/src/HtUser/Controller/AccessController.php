<?php

namespace HtUser\Controller;

use HtApplication\Controller\AbstractActionController;

class AccessController extends AbstractActionController
{
	public function indexAction()
	{
		$successMessage = null;
		if ($this->flashMessenger()->hasSuccessMessages()) {
			$successMessage = implode("<br/>", $this->flashMessenger()->getSuccessMessages());
		}
		
		$sl = $this->getServiceLocator();
		
		//@todo: prefetch app and server to optimize sql query later
		/* @var $userApp \HtAuthentication\Model\UserApplication */
		$userApp = $sl->get('UserApp');
		$userApps = $userApp->getAll(array('user_id' => $this->getLoggedInUserId()));
		
		$userAppsInfo = array();
		/* @var $userApp \HtAuthentication\Model\UserApplication */
		foreach ($userApps as $userApp) {
			$userAppInfo['applicationTitle'] = $userApp->getApplication()->getTitle();
			$userAppInfo['serverTitle'] = $userApp->getServer()->getTitle();
			$userAppInfo['organisationTitle'] = '';
			$organisation = $userApp->getOrganisation();
			if ($organisation) {
				$userAppInfo['organisationTitle'] = $organisation->getTitle();
			}
			$userAppInfo['serverDomain'] = $userApp->getServer()->getDomain();
            $userAppInfo['serverRedirectLink'] = $this->url()->fromRoute("token", array('action' => 'redirect-to-client-app')) . 
                "?server=" . $userApp->getServer()->getServerId() . 
                "&app=" . $userApp->getApplication()->getApplicationId() .
                "&org=" . $userApp->getOrganisationId();
                
			$userAppsInfo[] = $userAppInfo;
		}
		
		return array(
			'userAppsInfo' => $userAppsInfo,
			"successMessage" => $successMessage
		);
	}
}
