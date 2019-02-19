<?php
/**
 * Show info message in twitter bootstrap style
 * @author Tuan Ngo
 *
 */
namespace AppCore\View\Helper;

use Zend\View\Helper\AbstractHelper;

class InfoMessage extends AbstractHelper
{
	public function __invoke($message = null)
	{ 
		if (!empty($message)) {
			return "<div class='alert alert-info'>" . $message . "</div>";
		} else {
			return "";
		}
	}
}