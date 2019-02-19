<?php

namespace HtAdmin\Form\Filter;

use HtAuthentication\Model\Server as ServerModel;

use Zend\Form\Form;

class Server extends Form
{
	public function __construct()
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
				'placeholder' => 'Server Title',
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
                    ServerModel::STATUS_ACTIVE_PRODUCT => 'Production',
                    ServerModel::STATUS_ACTIVE_TEST => 'Test',
                    ServerModel::STATUS_DECOMMISSIONED => 'Decommissioned',
				),
			),
		));
	}
}
