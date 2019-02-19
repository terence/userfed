<?php
namespace AppCore\Mvc\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin as ZendAbstractPlugin;

class AbstractPlugin extends ZendAbstractPlugin
{
	/**
	 * @todo implement translation later.
	 * @param string $message
	 * @return string
	 */
	public function translate($message)
	{
		return $message;
	}
}