<?php

namespace HtAuthentication\Form;

use Zend\Form\Element;
use Zend\Form\Form;

class Login extends Form
{

	/**
	 * @SuppressWarnings(PHPMD)
	 */
	public function __construct($name = null)
	{
		// we want to ignore the name passed
		parent::__construct('login');
		$this->setAttribute('method', 'post');

		$username = new Element\Text('email');
		$username->setLabel("Email address:")->setAttributes(array(
			'placeholder' => 'Email address',
			'class' => 'form-control',
			'required' => true
		));
		$this->add($username);

		$password = new Element\Password('password');
		$password->setLabel('Password: ')->setAttributes(array(
			'placeholder' => 'Password',
			'class' => 'form-control',
			'size' => '30',
			'required' => true,
		));
		$this->add($password);

		$remember = new Element\Checkbox('remember');
		$remember->setAttributes(array(
			'required' => false,
		));
		$this->add($remember);

		$this->add(array(
			'name' => 'submit',
			'attributes' => array(
				'type' => 'submit',
				'value' => 'Login',
				'class' => 'btn btn-primary',
				'id' => 'submitbutton',
			),
		));
	}

}
