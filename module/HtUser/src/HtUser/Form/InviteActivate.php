<?php
namespace HtUser\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class InviteActivate extends Form
{
	/**
	 * @SuppressWarnings(PHPMD)
	 */
	public function __construct($name = null)
	{
		parent::__construct('invite-activate');
		$this->setAttributes(array(
			'method' => 'post'
		));
		
		$firstname = new Element\Text('firstname');
        $firstname->setLabel("First name:")->setAttributes(array('required' => true));
        $this->add($firstname);
        
        $lastname = new Element\Text('lastname');
        $lastname->setLabel("Last name:")->setAttributes(array('required' => true));
        $this->add($lastname);
		
		$password = new Element\Password('password');
		$password->setLabel('Password: ')->setAttributes(array(
        		'size'  => '30',
				'required' => true,
		));
		$this->add($password);

		$passwordConfirm = new Element\Password('password_confirm');
		$passwordConfirm->setLabel('Confirm Password: ')->setAttributes(array(
        		'size'  => '30',
				'required' => true,
    	));
		$this->add($passwordConfirm);
		               
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Create account',
        		'class' => 'btn btn-lg btn-primary btn-block',
                'id' => 'submitbutton',
            ),
        ));	
	}
}
