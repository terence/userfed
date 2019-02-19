<?php
namespace HtUser\Controller;

use HtApplication\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ProfileController extends AbstractActionController
{	
	public function indexAction()
	{
		$sl = $this->getServiceLocator();
		
		/* @var $user \HtUser\Model\User */
		$user = $sl->get('User');
		$userId = $this->getLoggedInUserId();
		if (!$user->load($userId)) {
			return array(
				'errorMessage' => $this->translate('User not found.'),
			);
		}
		
		return array(
			'user' => $user,
		);
	}
}