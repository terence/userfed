<?php
/**
 * Show message: error, success of current object
 * @author Tuan Ngo
 *
 */
namespace AppCore\View\Helper;

use Zend\View\Helper\AbstractHelper;
class Message extends AbstractHelper
{
	/**
	 * @notice No need to handle form message as it already render form error message rendering for each form element
	 * @param object $object a ViewModel object
	 */
	public function __invoke($object, $allowClose = true)
	{
		$dismissClass = '';
		if ($allowClose) {
			$dismissClass = ' alert-dismissable';
		}
		$dimissBtn = '';
		if ($allowClose) {
			$dimissBtn = '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
		}
		if (isset($object->errorCode)) {
			if (!empty($object->message)) {
				if ($object->errorCode == 0) {
					return "<div class='alert alert-success" . $dismissClass . "'>" . $dimissBtn . $object->message . "</div>";
				} else {
					return "<div class='alert-danger" . $dismissClass . "'>" . $dimissBtn . $object->message . "</div>";
				}
			}
		} else if (!empty($object->successMessage)) {
			return "<div class='alert alert-success" . $dismissClass . "'>" . $dimissBtn . $object->successMessage . "</div>";
		} else if (!empty($object->errorMessage)) {
			if (is_string($object->errorMessage)) {
				return "<div class='alert alert-danger" . $dismissClass . "'>" . $dimissBtn . $object->errorMessage . "</div>";
			} else if (is_array($object->errorMessage)) {
				/**support formats: 
				 * format 1: zend error array format
				 * array (
				 		'city_id' =>
				 		array (
				 				'notInArray' => 'The input was not found in the haystack',
				 		),
				 )
				 *format 2: array of string
				 *
				*/
				$html = "";
				foreach ($object->errorMessage as $message) {
					//format 2:
					if (is_string($message)) {
						$html .= $message . "<br/>";
					} 
					//format 1: 
					else if (is_array($message)){
						$message = array_values($message);
						if (count($message) > 0 && is_string($message[0])) {
							$html .= $message[0] . "<br/>";
						}
					}
				}
				if (!empty($html)) {
					$html = "<div class='alert alert-danger" . $dismissClass . "'>" . $dimissBtn . $html . "</div>";
				}
				return $html;
			}
			
		} else if (!empty($object->infoMessage)) {
			return "<div class='alert alert-info" . $dismissClass . "'>" . $dimissBtn . $object->infoMessage . "</div>";
		}
	}
}