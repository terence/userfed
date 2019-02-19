<?php
namespace AppCore;

class Exception extends \Exception
{
	const PUBLIC_VIEWABLE_ERROR_CODE = 999999999;
	/**
	 * Check if public user can view this exception error
	 */
	public function isPublic()
	{
		if ($this->getCode() == self::PUBLIC_VIEWABLE_ERROR_CODE) {
			return true;
		} else {
			return false;
		}
	}
}