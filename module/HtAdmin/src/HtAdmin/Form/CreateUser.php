<?php
namespace HtAdmin\Form;

use Zend\Form\Element;
use Zend\Form\Form;

class CreateUser extends Form
{
	/**
	 * @SuppressWarnings(PHPMD)
	 */
    public function __construct($name = null)
    {
        parent::__construct('create-user');
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
		$email->setLabel('Email: ')->setAttributes(array('multiple' => false, 'required' => true));
		$this->add($email);
		
        $emailConfirm = new Element\Email('email_confirm');
		$emailConfirm->setLabel('Confirm Email: ')->setAttributes(array('multiple' => false, 'required' => true));
		$this->add($emailConfirm);
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Create',
        		'class' => 'btn btn-primary',
                'id' => 'submitbutton',
            ),
        ));
    }
}