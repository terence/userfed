<?php
namespace AppCore\View\Helper;

use Zend\View\Helper\AbstractHelper;

class PageName extends AbstractHelper
{
	protected $pageName;
	
	public function setPageName($value)
	{
		$this->pageName = $value;
	}
	
	public function getPageName()
	{
		return $this->pageName . "-page";
	}
}