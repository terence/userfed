<?php

namespace HtAuthentication\Validator;

use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;
use AppCore\Mvc\Model\Model;

class CheckUniqueModelProperty extends AbstractValidator
{

	const NOT_UNIQUE = 'notUnique';

	protected $model;
	protected $property;
	protected $messageTemplates = array(
		self::NOT_UNIQUE => "This %value% is existed.",
	);

	public function __construct($options = null)
	{
		if ($options && is_array($options)) {
			if (array_key_exists('model', $options)) {
				if ($options['model'] instanceof Model) {
					$this->setModel($options['model']);
				} else {
					throw new Exception\InvalidArgumentException("'model' must instanceof 'AppCore\Mvc\Model\Model'");
				}
			} else {
				throw new Exception\InvalidArgumentException("Missing option 'model'");
			}
			if (array_key_exists('property', $options)) {
				$this->setProperty($options['property']);
			}
		}
		parent::__construct($options);
	}

	public function setModel($model)
	{
		$this->model = $model;
		return $this;
	}

	public function getModel()
	{
		return $this->model;
	}

	public function setProperty($field)
	{
		$this->property = (string) $field;
		return $this;
	}

	public function getProperty()
	{
		return $this->property;
	}
	

	public function isValid($value)
	{
		$this->setValue($value);

		/* @var $model Model */
		$model = $this->getModel();

		$field = $this->getProperty();
		$count = $model->count(array($field => $value));

		if ($count > 0) {
			$this->error(self::NOT_UNIQUE);
			return false;
		}

		return true;
	}

}
