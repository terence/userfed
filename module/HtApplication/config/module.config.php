<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'HtApplication\Controller\Index' => 'HtApplication\Controller\IndexController',
		),
	),
	'router' => array(
        'routes' => array(
			'home' => array(
				'type'	=> 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route' => '/',
					'defaults' => array(
						'controller' => 'HtApplication\Controller\Index',
						'action' => 'index',
					),
				),
			),
			'access-denied' => array(
				'type' => 'Zend\Mvc\Router\Http\Literal',
			    'options' => array(
			    	'route'    => '/access-denied',
			    	'defaults' => array(
			        	'controller' => 'HtApplication\Controller\Index',
			        	'action'     => 'access-denied',
					),
				),
			),
		),
	),
	
    'service_manager' => array(
	        'factories' => array(
	            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
				'ReverseOAuth2\Google' => 'HtApplication\ReverseOAuth2\Client\GoogleFactory',
				'ReverseOAuth2\Facebook' => 'HtApplication\ReverseOAuth2\Client\FacebookFactory',
                'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory'
			),
	),
    'navigation' => array(
        'default' => array(
            array(
                'label' => 'Profile',
                'route' => 'profile',
                'pages' => array(
                    array(
                        'label' => 'Profile',
                        'route' => 'profile',
                        'icon' => 'fa fa-file-text fa-lg',
                        'resource' => 'UserProfile',
                    ),
                    array(
                        'label' => 'Access',
                        'route' => 'access',
                        'icon' => 'fa fa-key fa-lg',
                        'resource' => 'UserAccess',
                    ),
                    array(
                        'label' => 'Identities',
                        'resource' => 'HtAuthentication:AuthenticationAccount',
                        'route' => 'login-management',
                        'icon' => 'fa fa-exchange fa-lg',
                        'pages' => array(
                            array(
                                'label' => 'Change Password',
                                'route' => 'internal',
                                'action' => 'update'
                            ),
                            array(
                                'label' => 'Add internal login',
                                'route' => 'internal',
                                'action' => 'add'
                            ),                    
                        )
                    ),                    
                )
            ),
            //admin route
            array(
                'label' => 'Admin',
                'route' => 'admin',
                'icon' => 'fa fa-tachometer fa-lg',
                'resource' => 'Admin',// Need this to don't display admin's menu if user is guest or standard user.
                'pages' => array(
                    array(
                        'label' => 'Users',
                        'route' => 'admin/user',
                        'icon' => 'fa fa-users fa-lg',
                        'resource' => 'Admin:User',
                        'pages' => array(
                            //@todo: change to route type
                            array(
                                'label' => 'Create User',
                                'controller' => 'HtAdmin\Controller\User',
                                'action' => 'create',
                            ),
                            array(
                                'label' => 'General Info',
                                'controller' => 'HtAdmin\Controller\User',
                                'action' => 'edit'
                            ),
                            array(
                                'label' => 'User\'s Application',
                                'controller' => 'HtAdmin\Controller\UserApplication',
                                'action' => 'view-user'
                            ),
                            array(
                                'label' => 'User\'s Log',
                                'controller' => 'HtAdmin\Controller\Log',
                                'action' => 'user'
                            ),
                            array(
                                'label' => 'User\'s Organisation',
                                'controller' => 'HtAdmin\Controller\UserOrganisation',
                                'action' => 'view-user'
                            ),
                            array(
                                'label' => 'User\'s Role',
                                'controller' => 'HtAdmin\Controller\UserRole',
                                'action' => 'user'
                            ),
                            array(
                                'label' => 'User\'s Identity',
                                'controller' => 'HtAdmin\Controller\UserIdentity',
                                'action' => 'index'
                            ),
                        )
                    ),
                    array(
                        'label' => 'Organisations',
                        'route' => 'admin/org',
                        'icon' => 'fa fa-sitemap fa-lg',
                        'resource' => 'Admin:Organisation',
                        'pages' => array(
                            //@todo: change to route type instead of controller/action
                            array(
                                'label' => 'Create Organisation',
                                'controller' => 'HtAdmin\Controller\Organisation',
                                'action' => 'create'
                            ),
                            array(
                                'label' => 'Organisation\'s General Info',
                                'controller' => 'HtAdmin\Controller\Organisation',
                                'action' => 'edit'
                            ),                    
                            array(
                                'label' => 'Organisation\'s Users',
                                'controller' => 'HtAdmin\Controller\UserOrganisation',
                                'action' => 'view-org'
                            ),
                            array(
                                'label' => 'Organisation\'s Applications',
                                'controller' => 'HtAdmin\Controller\ApplicationOrganisation',
                                'action' => 'view-org'
                            ),
                        )
                    ),
                    array(
                        'label' => 'Applications',
                        'route' => 'admin/app',
                        'icon' => 'fa fa-tasks fa-lg',
                        'resource' => 'Admin:Application',
                        'pages' => array(
                            //@todo: change to route type instead of controller/action
                            array(
                                'label' => 'Create Application',
                                'controller' => 'HtAdmin\Controller\Application',
                                'action' => 'create'
                            ),
                            array(
                                'label' => 'Application\'s General Info',
                                'controller' => 'HtAdmin\Controller\Application',
                                'action' => 'edit'
                            ),                    
                            array(
                                'label' => 'Application\'s Users',
                                'controller' => 'HtAdmin\Controller\UserApplication',
                                'action' => 'view-app'
                            ),
                            array(
                                'label' => 'Application\'s Organisations',
                                'controller' => 'HtAdmin\Controller\ApplicationOrganisation',
                                'action' => 'view-app'
                            ),
                            array(
                                'label' => 'Application\'s Servers',
                                'controller' => 'HtAdmin\Controller\ApplicationServer',
                                'action' => 'servers'
                            ),
                        )
                    ),
                    array(
                        'label' => 'Servers',
                        'route' => 'admin/server',
                        'icon' => 'fa fa-database fa-lg',
                        'resource' => 'Admin:Server',
                        'pages' => array(
                            array(
                                'label' => 'Create Server',
                                'controller' => 'HtAdmin\Controller\Server',
                                'action' => 'create'
                            ),
                            array(
                                'label' => 'Server\'s General Info',
                                'controller' => 'HtAdmin\Controller\Server',
                                'action' => 'edit'
                            ),
                        ),
                    ),
                    array(
                        'label' => 'Logs',
                        'route' => 'admin/log',
                        'icon' => 'fa fa-list-alt fa-lg',
                        'resource' => 'HtAdmin:Log',
                        'pages' => array(
                            array(
                                'label' => 'Log Details',
                                'controller' => 'HtAdmin\Controller\Log',
                                'action' => 'details'
                            ),
                        ),
                    ),
                    array (
                        'label' => 'Roles',
                        'route' => 'admin/role',
                        'icon' => 'fa fa-list-ol fa-lg',
                        'resource' => 'Admin:Role',
                        'pages' => array(
                            array(
                                'label' => 'Create Role', 
                                'controller' => 'HtAdmin\Controller\Role',
                                'action' => 'create'
                            ),
                            array(
                                'label' => 'Edit Role',
                                'controller' => 'HtAdmin\Controller\Role',
                                'action' => 'edit' 
                            ),
                            array(
                                'label' => 'Edit Permission',
                                'controller' => 'HtAdmin\Controller\Role',
                                'action' => 'edit-permission' 
                            ),
                            array(
                                'label' => 'Role\'s user',
                                'controller' => 'HtAdmin\Controller\UserRole',
                                'action' => 'role'
                            ),
                        ), 
                    ),
                ),
            )
        )
    ),	
	'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
			array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
			),
		),
	),
	
    'view_manager' => array(
		'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'error/404'				=> __DIR__ . '/../view/error/404.phtml',
            'error/index'			=> __DIR__ . '/../view/error/index.phtml',
            'layout/layout'			=> __DIR__ . '/../view/layout/layout-sidebar-menu.phtml',
            'partial/paginator-control'   => __DIR__ . '/../view/partial/pagination-control.phtml',
		),
        'template_path_stack' => array(
            'HtApplication' => __DIR__ . '/../view',
		),
		'strategies' => array(
			'ViewJsonStrategy',
		)
	),
);
