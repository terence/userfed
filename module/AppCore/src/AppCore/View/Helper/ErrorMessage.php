<?php
/**
 * Show error message in twitter bootstrap style
 * @author Tuan Ngo
 *
 */
namespace AppCore\View\Helper;

use Zend\View\Helper\AbstractHelper;
class ErrorMessage extends AbstractHelper
{
	/**
	 * @notice No need to handle form message as it already render form error message rendering for each form element
	 * @param unknown_type $message
	 */
	public function __invoke($message = null)
	{ 
		if (!empty($message)) {
			//allow to render a list of error or just an error
			if (!is_array($message)) {
				$message = array($message);
			}
			
			//clean empty message
			$finalMessage = array();
			foreach ($message as $m) {
				if (!empty($m)) {
					$finalMessage[] = $m;
				}
			}
			
			//render
			if (!empty($finalMessage)) {
				return "<div class='alert-danger'>" . implode($finalMessage, '<br/>') . "</div>";
			}
		}
		return "";
	}
}