<?php

namespace HtAdmin\Form\Filter;

use Zend\Form\Form;

class User extends Form
{
	/**
	 * @SuppressWarnings(PHPMD)
	 */
	public function __construct($name = null, $options = array())
	{
		parent::__construct('form-search', array(
			'method' => 'GET',
			'class' => 'form-inline'
		));
		
		$this->add(array(
			'name' => 'keyword',
			'type' => 'text',
			'attributes' => array(
				'class' => 'search-query input-medium form-control',
				'placeholder' => 'Email',
			),
		));
		
		$this->add(array(
			'name' => 'status',
			'type' => 'select',
			'attributes' => array(
				'class' => 'form-control'
			),
			'options' => array(
				'empty_option' => 'All',
				'value_options' => array(
					'enabled' => 'Enabled',
					'disabled' => 'Disabled',
					'deleted' => 'Deleted'
				),
			),
		));
	}
}
