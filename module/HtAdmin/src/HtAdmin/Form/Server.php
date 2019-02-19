<?php

namespace HtAdmin\Form;

use HtAuthentication\Model\Server as ServerModel;

use Zend\Form\Form;

class Server extends Form
{
    public function __construct()
    {
        parent::__construct('create-server');
        
        $this->add(array(
            'name' => 'server_id',
            'type' => 'hidden'
        ));
        
        $this->add(array(
            'name' => 'title',
            'type' => 'text',
            'options' => array(
                'label' => 'Title: ',
            ),
            'attributes' => array(
                'placeholder' => 'Call it what you want'
            ),
        ));
        
        $this->add(array(
            'name' => 'description',
            'type' => 'textarea',
            'options' => array(
                'label' => 'Description: ',
            ),
        ));
        
        $this->add(array(
            'name' => 'ip',
            'type' => 'text',
            'options' => array(
                'label' => 'IP'
            ),
            'attributes' => array(
                'placeholder' => 'Server IP address'
            ),
        ));
        
        $this->add(array(
            'name' => 'location',
            'type' => 'text',
            'options' => array(
                'label' => 'Location: ',
            ),
            'attributes' => array(
                'placeholder' => 'Physical location of server',
            ),
        ));
        
        $this->add(array(
            'name' => 'status',
            'type' => 'select',
            'options' => array(
                'label' => 'Status: ',
                'value_options' => array(
                    ServerModel::STATUS_ACTIVE_PRODUCT => 'Production',
                    ServerModel::STATUS_ACTIVE_TEST => 'Test',
                    ServerModel::STATUS_DECOMMISSIONED => 'Decommissioned',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'domain',
            'type' => 'text',
            'options' => array(
                'label' => 'Domain: '
            ),
            'attributes' => array(
                'placeholder' => 'http://www.example.com'
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
}
