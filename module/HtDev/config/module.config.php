<?php
/**
 * @link http://framework.zend.com/manual/2.3/en/modules/zend.console.routes.html
 */
return array(
	'controllers' => array(
		'invokables' => array(
			'HtDev\Controller\Index' => 'HtDev\Controller\IndexController',
		),
	),    
    'console' => array(
        'router' => array(
            'routes' => array(
                'mock-data' => array(
                    'options' => array(
                        'route'    => 'mock data [--xss] [--orgcount=] [--appcount=]',
                        'defaults' => array(
                            'controller' => 'HtDev\Controller\Index',
                            'action'     => 'mock-data'
                        )
                    )
                ),
                'mock-user' => array(
                    'options' => array(
                        'route'    => 'mock user [--xss] [--usercount=]',
                        'defaults' => array(
                            'controller' => 'HtDev\Controller\Index',
                            'action'     => 'mock-user'
                        )
                    )
                ),                
                'crawl_app_mock' => array(
                    'options' => array(
                        'route'    => 'crawl_app_mock',
                        'defaults' => array(
                            'controller' => 'HtDev\Controller\Index',
                            'action'     => 'crawl-software-name'
                        )
                    )
                )
            )
        )
    ),    
);