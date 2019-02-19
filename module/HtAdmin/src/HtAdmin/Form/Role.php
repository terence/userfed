<?php

namespace HtAdmin\Form;

use Zend\Form\Form;

class Role extends Form
{
    public function __construct($name = null, $options = array())
    {
        parent::__construct('role');
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'role_id',
            'type' => 'text',
            'options' => array(
                'label' => 'Role ID'
            ),
        ));
        
        $this->add(array(
            'name' => 'description',
            'type' => 'text',
            'options' => array(
                'label' => 'Description'
            ),
        ));
        
        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
                'class' => 'btn btn-primary',
                'value' => 'Create'
            ),
        ));
        
    }
    
    public function getUpdateForm()
    {
        $this->remove('role_id');
        return $this;
    }
}