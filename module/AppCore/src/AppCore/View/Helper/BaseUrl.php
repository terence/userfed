<?php
namespace AppCore\View\Helper;

use Zend\View\Helper\AbstractHelper;

class BaseUrl extends AbstractHelper
{
	protected $baseUrl;
	
	public function setBaseUrl($baseUrl)
	{
		$this->baseUrl = $baseUrl;
		return $this;
	}
	
	public function getBaseUrl()
	{
		return $this->baseUrl;
	}
	
	public function __invoke($uri = null)
	{ 
		if ($uri) {
			return $this->getBaseUrl() . "/" . $uri;
		} else {
			return $this->getBaseUrl();
		}
	}
}