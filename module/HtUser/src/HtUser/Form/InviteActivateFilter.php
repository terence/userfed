<?php
namespace HtUser\Form;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\Factory;

class InviteActivateFilter extends InputFilter implements InputFilterAwareInterface
{
	protected $inputFilter;
	public function getInputFilter()
	{
		if (!$this->inputFilter) {
			$inputFilter = new InputFilter();
			$factory = new Factory();
			
			$inputFilter->add($factory->createInput(array(
				'name' => 'firstname',
				'required' => true,
				'filters' => array(
					array('name' => 'StringTrim'),
					array('name' => 'StripTags'),
				),
			)));
			
			$inputFilter->add($factory->createInput(array(
				'name' => 'lastname',
				'required' => true,
				'filters' => array(
					array('name' => 'StringTrim'),
					array('name' => 'StripTags'),
				),
			)));
			
			$inputFilter->add($factory->createInput(array(
				'name' => 'password',
				'required' => true,
				'validators' => array(
					array(
						'name' => 'StringLength',
						'options' => array(
							'min' => 6,
							'max' => 30
						)
					)
				),
			)));
			
			$inputFilter->add($factory->createInput(array(
				'name' => 'password_confirm',
				'required' => true,
				'validators' => array(
					array(
						'name' => 'Identical',
						'strict' => false,
						'options' => array(
							'token' => 'password'
						)
					)
				),
			)));
			$this->inputFilter = $inputFilter;
		}
		return $this->inputFilter;
	}
	/**
	 * @SuppressWarnings(PHPMD)
	 */
	public function setInputFilter(InputFilterInterface $inputFilter)
	{
		throw new \Exception('Not use.');
	}
}
