<?php
namespace AppCore\Mvc\Controller\Plugin;

use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class SuccessMessage extends AbstractPlugin
{
	/**
	 * Create appropriate view model which will contain the message.
	 * @param string|array $message Message can be a string or an array of data
	 */
	public function __invoke($message = null)
	{
		if (!isset($message)) {
			$message['message'] = $this->translate('Done');
		}
		if (is_array($message)) {
			if (!array_key_exists('message', $message)) {
				$message['message'] = $this->translate('Done');
			}
		}
		$controller = $this->getController();
		$request = $controller->getRequest();
		
		//allow by pass ajax request which may be used by the simple profile form
		$message['errorCode'] = 0;
		if ($request->isXmlHttpRequest()) {
			$view = new JsonModel($message);
		} else {
			$view = new ViewModel($message);
		}
		return $view;
	}
}