<?php

namespace HtAdmin\Form\Filter;

use Zend\Form\Form;

class Organisation extends Form
{
	public function __construct()
	{
		parent::__construct('org-filter', array(
			'attributes' => array(
				'method' => 'GET',
				'class' => 'form-inline'
			),
		));
		
		$this->add(array(
			'name' => 'keyword',
			'type' => 'text',
			'attributes' => array(
				'class' => 'form-control',
				'placeholder' => 'Organisation Title',
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
