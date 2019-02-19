<?php
namespace AppCore\Mvc\Controller\Plugin;

class BaseUrl extends AbstractPlugin
{
	/**
	 * Add basepath to the url
	 * @param string $uri without / at the end
	 */
	public function __invoke($uri)
	{
		$baseUrl = "";
		
		$controller = $this->getController();
		$config = $controller->getServiceLocator()->get("config");
		if (array_key_exists('baseUrl', $config)) {
			$baseUrl = $config['baseUrl'];
		}
		return $baseUrl . "/" . $uri;
	}
}