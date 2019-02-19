<?php

return array(
	'controllers' => array(
		'invokables' => array(
			'HtAuthentication\Controller\OAuth' => 'HtAuthentication\Controller\OAuthController',
			'HtAuthentication\Controller\Token' => 'HtAuthentication\Controller\TokenController',
			'HtAuthentication\Controller\Logout' => 'HtAuthentication\Controller\LogoutController',
			'HtAuthentication\Controller\AuthenticationAccount' => 'HtAuthentication\Controller\AuthenticationAccountController',
			'HtAuthentication\Controller\Internal' => 'HtAuthentication\Controller\InternalController',
			'HtAuthentication\Controller\ApiUser' => 'HtAuthentication\Controller\ApiUserController',
			'HtAuthentication\Controller\ApiOrganisation' => 'HtAuthentication\Controller\ApiOrganisationController'
		),
	),
	'router' => array(
		'routes' => array(
			'api-user' => array(
				'type' => 'Segment',
				'options' => array(
					'route' => '/api/user[/:action]',
					'contraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
					),
					'defaults' => array(
						'controller' => 'HtAuthentication\Controller\ApiUser',
						'action' => 'authenticated',
					),
				),
			),
			'api-org' => array(
				'type' => 'Segment',
				'options' => array(
					'route' => '/api/org[/:action]',
					'contraints'	=> array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
					),
					'defaults' => array(
						'controller' => 'HtAuthentication\Controller\ApiOrganisation'
					),
				),
			),			
			'internal' => array(
				'type' => 'Segment',
				'options' => array(
					'route' => '/internal[/:action[/:id]]',
					'contraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
					),
					'defaults' => array(
						'controller' => 'HtAuthentication\Controller\Internal',
						'action' => 'register',
					),
				),
			),
			'register' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/register',
					'defaults' => array(
						'controller' => 'HtAuthentication\Controller\Internal',
						'action' => 'register'
					),
				),
			), 
			'login' => array(
        		'type' => 'Literal',
        		'options' => array(
        			'route' => '/login',
        			'defaults' => array(
        					'controller' 	=> 'HtAuthentication\Controller\Internal',
        					'action'		=> 'login',
        			),
        		),
        	),
			'login-management' => array(
        		'type' => 'Literal',
        		'options' => array(
        			'route' => '/identity',
        			'defaults' => array(
        					'controller' 	=> 'HtAuthentication\Controller\AuthenticationAccount',
        					'action'		=> 'login-management',
        			),
        		),
        	),
			'oauth' => array(
				'type' => 'Zend\Mvc\Router\Http\Segment',
				'options' => array(
					'route' => '/oauth/:action[/:provider]',
					'contraints'	=> array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'provider' => '[a-zA-Z][a-zA-Z0-9_-]*',
					),
					'defaults' => array(
						'controller' => 'HtAuthentication\Controller\OAuth',
						'action' => 'index',
					),
				),
			),
			'token' => array(
				'type' => 'Zend\Mvc\Router\Http\Segment',
				'options' => array(
					'route' => '/token/:action',
					'contraints'	=> array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
					),
					'defaults' => array(
						'controller' => 'HtAuthentication\Controller\Token',
						'action' => 'verify',
					),
				),
			),
			'logout' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/logout',
					'defaults' => array(
						'controller' 	=> 'HtAuthentication\Controller\Logout',
						'action'		=> 'logout',
					),
				),
			),
			'logout-sso' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/sso-logout',
					'defaults' => array(
						'controller' 	=> 'HtAuthentication\Controller\Logout',
						'action'		=> 'sso-logout',
					),
				),
			),										
		)
	),
	'view_manager' => array(
		'template_path_stack' => array(
			'HtAuthentication' => __DIR__ . '/../view',
		),
	),
	'service_manager'=> array(
        'abstract_factories' => array(
			'HtApplication\Service\CommonModelTableAbstractFactory',
        ),
    )
);
