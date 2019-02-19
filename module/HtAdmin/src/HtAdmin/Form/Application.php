<?php

namespace HtAdmin\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class Application extends Form
{
	/**
	 * @SuppressWarnings(PHPMD)
	 */
	public function __construct($name = null)
	{
		parent::__construct('create-application');
		$this->setAttribute('class', 'create-form');
		
		$applicationId = new Element\Hidden('application_id');
		$this->add($applicationId);
		
		$title = new Element\Text('title');
		$title->setLabel('Title: ')->setAttributes(array('required' => true));
		$this->add($title);
		
		$description = new Element\Textarea('description');
		$description->setLabel('Description: ');
		$this->add($description);
		
		$this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Create',
        		'class' => 'btn btn-primary',
                'id' => 'submitbtn',
            ),
        ));
	}
}
