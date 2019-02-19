<?php

namespace HtAuthentication\Form;

use Zend\Form\Element;
use Zend\Form\Form;

class Register extends Form
{
	/**
	 * @SuppressWarnings(PHPMD)
	 */
	public function __construct($name = null)
	{
		// we want to ignore the name passed
		parent::__construct('register');
		$this->setAttributes(array(
            'method' => 'post',
        ));

		$firstname = new Element\Text('firstname');
		$firstname->setLabel("First name:");
		$this->add($firstname);

		$lastname = new Element\Text("lastname");
		$lastname->setLabel("Last name:");
		$this->add($lastname);

		$email = new Element\Email('email');
		$email->setLabel('Email: ');
		$this->add($email);

		$emailConfirm = new Element\Email('email_confirm');
		$emailConfirm->setLabel('Confirm Email: ');
		$this->add($emailConfirm);

		$password = new Element\Password('password');
		$password->setLabel('Password: ');
		$this->add($password);

		$passwordConfirm = new Element\Password('password_confirm');
		$passwordConfirm->setLabel('Confirm Password: ');
		$this->add($passwordConfirm);
		
		$this->add(array(
			'name' => 'submit',
            'type' => 'button',
			'attributes' => array(
				'type' => 'submit',
				'value' => 'Register',
				'class' => 'btn btn-lg btn-primary btn-block',
				'id' => 'submitbutton',
			),
		));
	}

	/**
	 * Create add internal login form
	 * @return \HtAuthentication\Form\Register
	 */
	public function getAddLogin()
	{
		$this->remove('firstname');
		$this->remove('lastname');
		$this->get('submit')->setValue('Add');
		return $this;
	}

	public function getUpdateLogin()
	{
		$this->remove('firstname');
		$this->remove('lastname');
		$this->remove('email');
		$this->remove('email_confirm');

		$currentPassword = new Element\Password('current_password');
		$currentPassword->setLabel('Current Password: ')->setAttributes(array(
			'size' => '30',
			'required' => true,
		));
		$this->add($currentPassword);
		$this->get("password")->setLabel("New Password: ");
		$this->get("password_confirm")->setLabel("Confirm New Password: ");
		$this->get('submit')->setValue('Update');
		return $this;
	}
}
