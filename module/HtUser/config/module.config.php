<?php
return array(
    'controllers' => array(
        'invokables' => array(
        	'HtUser\Controller\Register' => 'HtUser\Controller\RegisterController',
        	'HtUser\Controller\Profile' => 'HtUser\Controller\ProfileController',
        	'HtUser\Controller\Invite' => 'HtUser\Controller\InviteController',
        	'HtUser\Controller\Access' => 'HtUser\Controller\AccessController',
		),
	),
	'router' => array(
        'routes' => array(
        	'profile' => array(
        		'type' => 'Literal',
        		'options' => array(
        			'route' => '/profile',
        			'defaults' => array(
        					'controller' 	=> 'HtUser\Controller\Profile',
        					'action'		=> 'index',
        			),
        		),
        	),
			'access' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/access',
					'defaults' => array(
						'controller' => 'HtUser\Controller\Access',
						'action' => 'index',
					),
				),
			),
			'invite-user' => array(
				'type' => 'Segment',
				'options' => array(
					'route' => '/invite[/:action][/:provider]',
					'contraints'	=> array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'provider' => '[a-zA-Z][a-zA-Z0-9_-]*',
					),
					'defaults' => array(
						'controller'	=> 'HtUser\Controller\Invite',
						'action'		=> 'invite',
					),
				),
			),
			'oauth-invite-register' => array(
				'type' => 'Segment',
				'options' => array(
					'route' => '/oauth/invite-register[/:provider]',
					'contraints'	=> array(
						'provider' => '[a-zA-Z][a-zA-Z0-9_-]*',
					),
					'defaults' => array(
						'controller'	=> 'HtUser\Controller\Invite',
						'action'		=> 'activate-oauth',
					),
				),
			),
		),
	),
	
    'view_manager' => array(
        'template_path_stack' => array(
            'HtUser' => __DIR__ . '/../view',
		),
	),
);