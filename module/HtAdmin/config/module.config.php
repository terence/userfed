<?php
return array(
    'controllers' => array(
        'invokables' => array(
        	'HtAdmin\Controller\Index'		=> 'HtAdmin\Controller\IndexController',
			'HtAdmin\Controller\Log'	=> 'HtAdmin\Controller\LogController',
			'HtAdmin\Controller\User'		=> 'HtAdmin\Controller\UserController',
			'HtAdmin\Controller\UserApplication' => 'HtAdmin\Controller\UserApplicationController',
			'HtAdmin\Controller\Organisation' => 'HtAdmin\Controller\OrganisationController',
			'HtAdmin\Controller\ApplicationOrganisation' => 'HtAdmin\Controller\ApplicationOrganisationController',
			'HtAdmin\Controller\UserOrganisation' => 'HtAdmin\Controller\UserOrganisationController',
			'HtAdmin\Controller\Application' => 'HtAdmin\Controller\ApplicationController',
			'HtAdmin\Controller\UserRole'	=> 'HtAdmin\Controller\UserRoleController',
            'HtAdmin\Controller\UserIdentity' => 'HtAdmin\Controller\UserIdentityController',
            'HtAdmin\Controller\Server' => 'HtAdmin\Controller\ServerController',
            'HtAdmin\Controller\ServerOrganisation' => 'HtAdmin\Controller\ServerOrganisationController',
            'HtAdmin\Controller\UserServer' => 'HtAdmin\Controller\UserServerController',
            'HtAdmin\Controller\ApplicationServer' => 'HtAdmin\Controller\ApplicationServerController',
            'HtAdmin\Controller\Role' => 'HtAdmin\Controller\RoleController',
            'HtAdmin\Controller\UserRest' => 'HtAdmin\Controller\UserRestController',
            'HtAdmin\Controller\DeletedUserRest' => 'HtAdmin\Controller\DeletedUserRestController',
		),
	),
	'router' => array(
        'routes' => array(
            'rest-api' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/rest-api',
                    'defaults' => array(
                        'controller' => 'HtAdmin\Controller\UserRest',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'user' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/user[/:id]',
                        ),
                    ),
                    'deleted-user' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/deleted-user[/:id]',
                            'defaults' => array(
                                'controller' => 'HtAdmin\Controller\DeletedUserRest'
                            ),
                        ),
                    ),
                ),
            ),
        	'admin' => array(
        		'type' => 'Literal',
        		'options' => array(
        			'route' => '/admin',
        			'defaults' => array(
        					'controller' 	=> 'HtAdmin\Controller\Index',
        					'action'		=> 'index',
        			),
        		),
				'may_terminate' => true,
				'child_routes' => array(
					'user' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/user',
							'defaults' => array(
								'controller' => 'HtAdmin\Controller\User',
								'action' => 'index',
							),
						),
						'may_terminate' => true,
						'child_routes' => array(
							'default' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '[/:action][/:id]',
									'defaults' => array(
										'controller' => 'HtAdmin\Controller\User',
										'action' => 'index'
									)
								),
							),
							'app' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/app[/:id]',
									'contraints'	=> array(
										'id' => '[0-9]+',
									),
									'defaults' => array(
										'controller' => 'HtAdmin\Controller\UserApplication',
										'action' => 'view-user'
									),
								),
							),
							'log' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/log[/:userId]',
									'contraints'	=> array(
										'userId' => '[0-9]+',
									),
									'defaults' => array(
										'controller' => 'HtAdmin\Controller\Log',
										'action' => 'user',
									),
								),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'details' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/details/:id',
                                            'contraints'	=> array(
                                                'id' => '[0-9]+',
                                            ),
                                            'defaults' => array(
                                                'controller' => 'HtAdmin\Controller\Log',
                                                'action' => 'details',
                                            ),
                                        ),
                                    ),
                                ),
							),
							'org' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/org[/:id]',
									'contraints' => array(
										'id' => '[0-9]+',
									),
									'defaults' => array(
										'controller' => 'HtAdmin\Controller\UserOrganisation',
										'action' => 'view-user'
									),
								),
							),
							'role' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/role[/:id][/:action][/:roleId]',
									'contraints' => array(
										'id' => '[0-9]+',
									),
									'defaults' => array(
										'controller' => 'HtAdmin\Controller\UserRole',
										'action' => 'user',
									),
								),
							),
                            'identity' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/identity[/:id][/:action]',
                                    'contraints' => array(
                                        'id' => '[0-9]+',
                                    ),
                                    'defaults' => array(
                                        'controller' => 'HtAdmin\Controller\UserIdentity',
                                        'action' => 'index'
                                    ),
                                ),
                            ),
						),
					),
					'org' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/org',
							'defaults' => array(
								'controller' => 'HtAdmin\Controller\Organisation',
								'action' => 'index',
							),
						),
						'may_terminate' => true,
						'child_routes' => array(
							'common' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/:action[/:id]',
									'contraints' => array(
										'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
									),
								),
							),
							'user' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/user/:id',
									'contraints' => array(
										'id' => '[0-9]+'
									),
									'defaults' => array(
										'controller' => 'HtAdmin\Controller\UserOrganisation',
										'action' => 'view-org'
									),
								),
							),
							'app' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/app/:id',
									'contraints' => array(
										'id' => '[0-9]+'
									),
									'defaults' => array(
										'controller' => 'HtAdmin\Controller\ApplicationOrganisation',
										'action' => 'view-org'
									),
								),
							),
						),
					),
					'app' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/app',
							'defaults' => array(
								'controller' => 'HtAdmin\Controller\Application',
								'action' => 'index'
							),
						),
						'may_terminate' => true,
						'child_routes' => array(
							'common' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/:action[/:id]',
									'contraints' => array(
										'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
										'id' => '[0-9]+',
									),
								),
							),
							'user' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/user[/:id]',
									'contraints' => array(
										'id' => '[0-9]+',
									),
									'defaults' => array(
										'controller' => 'HtAdmin\Controller\UserApplication',
										'action' => 'view-app'
									),
								),
							),
							'org' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/org[/:id]',
									'contraints' => array(
										'id' => '[0-9]+'
									),
									'defaults' => array(
										'controller' => 'HtAdmin\Controller\ApplicationOrganisation',
										'action' => 'view-app'
									),
								),
							),
							'server' => array(
								'type' => 'Segment',
								'options' => array(
									'route' => '/server[/:id[/:action]]',
									'contraints' => array(
										'id' => '[0-9]+'
									),
									'defaults' => array(
										'controller' => 'HtAdmin\Controller\ApplicationServer',
										'action' => 'servers'
									),
								),
							),
						),
					),
                    'server' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/server',
                            'defaults' => array(
                                'controller' => 'HtAdmin\Controller\Server',
                                'action' => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'common' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/:action[/:id]',
                                    'contraints' => array(
                                        'id' => '[0-9]+'
                                    ),
                                ),
                            ),
                            'org' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/org[/:id]',
                                    'contraints' => array(
                                        'id' => '[0-9]+'
                                    ),
                                    'defaults' => array(
                                        'controller' => 'HtAdmin\Controller\ServerOrganisation',
                                        'action' => 'org'
                                    ),
                                ),
                            ),
                            'user' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/user[/:id]',
                                    'contraints' => array(
                                        'id' => '[0-9]+'
                                    ),
                                    'defaults' => array(
                                        'controller' => 'HtAdmin\Controller\UserServer',
                                        'action' => 'user'
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'role' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/role',
                            'defaults' => array(
                                'controller' => 'HtAdmin\Controller\Role',
                                'action' => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'common' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/:action[/:id]',
                                ),
                            ),
                            'user' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/user[/:id][/:action][/:userId]',
                                    'contraints' => array(
                                        'id' => '[\w]+',
                                    ),
                                    'defaults' => array(
                                        'controller' => 'HtAdmin\Controller\UserRole',
                                        'action' => 'role',
                                    ),
                                ),
                            ),
                            'permission' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/permission[/:id]',
                                    'defaults' => array(
                                        'controller' => 'HtAdmin\Controller\Role',
                                        'action' => 'edit-permission',
                                    ),
                                ),
                            ),
                        ),
                    ),
					'user-application' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/user-app[/:action][/:id]',
							'defaults' => array(
								'controller' => 'HtAdmin\Controller\UserApplication',
								'action' => 'index'
							),
						),
					),
					'organisation' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/organisation[/:action][/:id]',
							'defaults' => array(
								'controller' => 'HtAdmin\Controller\Organisation',
								'action' => 'index',
							),
						),
					),
					'application' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/application[/:action][/:id]',
							'defaults' => array(
								'controller' => 'HtAdmin\Controller\Application',
								'action' => 'index'
							),
						),
					),
					'application-organisation' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/app-org[/:action][/:id]',
							'defaults' => array(
								'controller' => 'HtAdmin\Controller\ApplicationOrganisation',
								'action' => 'index'
							),
						),
					),
					'user-organisation' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/user-org[/:action][/:id]',
							'defaults' => array(
								'controller' => 'HtAdmin\Controller\UserOrganisation',
								'action' => 'index'
							),
						),
					),
					'log' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/log',
                            'defaults' => array(
                                'controller' => 'HtAdmin\Controller\Log',
                                'action' => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'common' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/:action[/:id]',
                                    'contraints' => array(
                                        'id' => '[0-9]+'
                                    ),
                                ),
                            ),
                            'details' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/:id',
                                    'contraints' => array(
                                        'id' => '[0-9]+'
                                    ),
                                    'defaults' => array(
                                        'action' => 'details'
                                    ),
                                ),
                            ),
                        ),
                    ),
				),
        	),
		),
	),
	
    'view_manager' => array(
        'template_path_stack' => array(
            'HtAdmin' => __DIR__ . '/../view',
		),
	),
);
