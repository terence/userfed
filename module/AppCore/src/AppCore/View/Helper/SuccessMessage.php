<?php
/**
 * Show success message in twitter bootstrap style
 * @author Tuan Ngo
 *
 */
namespace AppCore\View\Helper;

use Zend\View\Helper\AbstractHelper;

class SuccessMessage extends AbstractHelper
{
	public function __invoke($message = null)
	{ 
		if (!empty($message)) {
			return "<div class='alert alert-success'>" . $message . "</div>";
		} else {
			return "";
		}
	}
}