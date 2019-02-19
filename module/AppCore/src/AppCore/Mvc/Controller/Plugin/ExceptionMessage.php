<?php
namespace AppCore\Mvc\Controller\Plugin;

use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use AppCore\Exception;
class ExceptionMessage extends AbstractPlugin
{
	/**
	 * Show appropriate error message and view class
	 *
	 * @param \Exception $e
	 * @return \Zend\View\Model\ViewModel
	 */
	public function __invoke(\Exception $e)
	{
		$controller = $this->getController();
		$request = $controller->getRequest();
		
		//allow by pass ajax request which may be used by the simple profile form
		if ($request->isXmlHttpRequest()) {
			$view = new JsonModel();
		} else {
			$view = new ViewModel();
		}
	
		$view->setVariable('errorCode', 1);
	
		if ($this->isPublicViewableException($e)) {
			$view->setVariables(array(
					'errorMessage' => $e->getMessage()
			));
		} else {
			$view->setVariables(array(
				'errorMessage' => $this->translate("System error")
			));
		}
		return $view;
	}
	
	public function isPublicViewableException(\Exception $e)
	{
		if ($e->getCode() == Exception::PUBLIC_VIEWABLE_ERROR_CODE) {
			return true;
		}
		return false;
	}	
}