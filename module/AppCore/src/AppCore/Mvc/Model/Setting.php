<?php
namespace AppCore\Mvc\Model;

use AppCore\Mvc\Model\Model as AppCoreModel;

abstract class Setting extends AppCoreModel
{
	const TYPE_BOOLEAN = 'boolean';
	const TYPE_TEXT = 'text';
	const TYPE_INT = 'int';
	const TYPE_FLOAT = 'float';
	
	/**
	 * Convert from php type to db value (will be stored as text)
	 * 
	 * @param string $value
	 * @param string $type
	 * @return string 
	 */
	protected function toDbFormat($value, $type)
	{
		switch (strtolower($type)) {
			case self::TYPE_BOOLEAN:
				$value = (boolean) $value;
				if ($value === true || $value === "1" || $value === 1) {
					$value = 1;
				} else if ($value === false || $value === "0" || $value === 0) {
					$value = 0;
				} else {
					throw new Exception("Unrecognized boolean value");
				}
				break;
			case self::TYPE_TEXT:
				break;
			case self::TYPE_INT:
				$value = (intval($value));
				break;
			case self::TYPE_FLOAT:
				$value = floatval($value);
				break;
			default:
				$this->throwNormalException("Invalid type.");
		}
		return $value;
	}
	
	/**
	 * Convert from db text format to php type
	 * @param string $value
	 * @param string $type
	 * @return mixed
	 */
	protected function fromDbFormat($value, $type)
	{
		switch (strtolower($type)) {
			case self::TYPE_BOOLEAN:
				$value = (boolean) $value;
				if ($value == "1") {
					$value = true;
				} else if ($value == "0"){
					$value = false;
				}
				break;
			case self::TYPE_TEXT:
				break;
			case self::TYPE_INT:
				$value = (intval($value));
				break;
			case self::TYPE_FLOAT:
				$value = floatval($value);
				break;
			default:
				$this->throwNormalException("Invalid type.");
		}
		return $value;
	}
}