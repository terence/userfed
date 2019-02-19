<?php
namespace AppCore\Form\View\Helper;

use Zend\Form\View\Helper\FormPassword as ZendFormPassword;
use Zend\Form\ElementInterface;
class FormPassword extends ZendFormPassword
{
    /**
     * Additional attributes valid for the input tag type="text"
     *
     * @var array
     */
    protected $addtionalValidTagAttributes = array(
    	//add jquery validator attribute
    	'minlength'    => true,
    );

    /**
     * Add more valid attribute before render
     * 
     * @param ElementInterface $element
     */
    public function render(ElementInterface $element)
    {
    	foreach($this->addtionalValidTagAttributes as $key => $value) {
    		$this->validTagAttributes[$key] = $value;
    	}
    	
    	return parent::render($element);
    }
}
