<?php

namespace HtAdmin\Form;

use Zend\Form\Element;
use Zend\Form\Form;

class Organisation extends Form
{
	/**
	 * @SuppressWarnings(PHPMD)
	 */
	public function __construct($name = null)
	{
		parent::__construct('create-organisation');
		$this->setAttribute('class', 'create-form');
		
		$organisationId = new Element\Hidden('organisation_id');
		$this->add($organisationId);
				
		$title = new Element\Text('title');
		$title->setLabel('Title: ')->setAttributes(array(
			'required' => true,
			'placeholder' => 'Organisation Title'
		));
		$this->add($title);
		
		$description = new Element\Textarea('description');
		$description->setLabel('Description: ')->setAttributes(array(
			'placeholder' => 'Organisation Description'
		));
		$this->add($description);
		
		$domain = new Element\Text('domain');
		$domain->setLabel('Domain: ')->setAttributes(array(
			'placeholder' => 'http://www.example.com'
		));
		$this->add($domain);
		
		$this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Save',
        		'class' => 'btn btn-primary',
                'id' => 'submitbutton',
            ),
        ));
		
	}
}
