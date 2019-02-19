<?php
/**
 * For security issue, as HTTP Referer may contain dangerous external url SO
 * This plugin allow redirect to http referer if referer's uri is an application's url (not a external url) and match a route.
 * If not it will redirect to safe app's url
 */
namespace AppCore\Mvc\Controller\Plugin;

use Zend\Http\Request;

class Referer extends AbstractPlugin
{
	/**
	 * Redirect to referer if referer uri is relative in site
	 * @param string $default
	 * if don't set we will redirect to index in controller if this method available, last resort is redirect to site's root url
	 * @return \Zend\Http\Response
	 */
	public function redirect($default = null)
	{
		$controller = $this->getController();
		$request = $controller->getRequest();
		
		/* @var $request Zend\Http\PhpEnvironment\Request */
		$requestHost = $request->getUri()->getHost();
		/* @var $referer \Zend\Http\Header\Referer */
		$event = $controller->getEvent();
		$referer = $request->getHeader('referer');
		$router = $event->getRouter();
		$url = $default;
		if ($referer) {
			$request = new Request();
			$request->setUri($referer->uri());
			$refererHost = $referer->uri()->getHost();
			$match = $router->match($request);
			/* Referer match a route*/
			if ($match && $requestHost == $refererHost) {
				$url = $referer->getUri();
			}
		}
		
		if (!$url) {
			/* redirect to index if controller has index method*/
			if (method_exists($controller, 'indexdAction')) {
				$routerMatch = $event->getRouteMatch();
				$url = $controller->url()->fromRoute($routerMatch->getMatchedRouteName(), array('action' => 'index'));
			} else {
				/* redirect to host root */
				$url = $request->getBasePath();
				if (empty($url)) {
					$url = '/';
				}
			}
		}
		return $controller->redirect()->toUrl($url);
	}
}
