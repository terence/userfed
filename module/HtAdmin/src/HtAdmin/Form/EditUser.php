<?php
namespace HtAdmin\Form;

use Zend\Form\Element;
use Zend\Form\Form;

class EditUser extends Form
{
	/**
	 * @SuppressWarnings(PHPMD)
	 */
    public function __construct($name = null)
    {
        parent::__construct('edit-user');
        $this->setAttribute('method', 'post');
        
        $userId = new Element\Hidden('user_id');
        $userId->setAttribute('required', false);
        $this->add($userId);
        
        $firstname = new Element\Text('firstname');
        $firstname->setLabel("First name:")->setAttributes(array('required' => true));
        $this->add($firstname);
        
        $lastname = new Element\Text('lastname');
        $lastname->setLabel("Last name:")->setAttributes(array('required' => true));
        $this->add($lastname);
		
        $email = new Element\Email('email');
		$email->setLabel('Email: ')
				->setAttributes(array(
					'required' => true,
					'readonly' => true,
					'disabled' => true,
					));
		$this->add($email);
		
		$supped = new Element\Checkbox('is_enabled');
		$supped->setLabel('Enabled');
		$this->add($supped);
		
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