<?php
namespace HtApplication\Controller;

class IndexController extends AbstractActionController
{
	public function indexAction()
	{
		if (!$this->hasIdentity()) {
			return $this->forward()->dispatch('HtAuthentication\Controller\Internal', array('controller' => 'HtAuthentication\Controller\Internal', 'action' => 'login'));
		}
		return array('msg' => 'hello');
	}
	
	public function accessDeniedAction()
	{
		return array();
	}
}