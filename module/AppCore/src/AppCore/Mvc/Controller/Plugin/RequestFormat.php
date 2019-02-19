<?php
namespace AppCore\Mvc\Controller\Plugin;

class RequestFormat extends AbstractPlugin
{
	/**
	 * Add basepath to the url
	 * @param string $uri without / at the end
	 */
	public function isJson()
	{
		$controller = $this->getController();
		$headers = $controller->getRequest()->getHeaders();	
		
		$returnJson = false;
		if ($headers->has('Accept')) {
			$accept = $headers->get('Accept')->getFieldValue();
			if (strpos($accept, 'application/json') !== false) {
				$returnJson = true;
			}
		}
		return $returnJson;
	}
}