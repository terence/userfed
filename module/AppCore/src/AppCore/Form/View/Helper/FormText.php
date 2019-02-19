<?php
/**
 * Add more attributes for text element
 */

namespace AppCore\Form\View\Helper;

use Zend\Form\View\Helper\FormText as ZendFormText;
use Zend\Form\ElementInterface;

class FormText extends ZendFormText
{
    /**
     * Additional attributes valid for the input tag type="text"
     *
     * @var array
     */
    protected $addtionalValidTagAttributes = array(
    	//add jquery validator attribute
    	'max'          => true,
    	'min'          => true,
    	'maxlength'    => true,
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