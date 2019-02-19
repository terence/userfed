<?php

namespace HtApplication;

use AppCore\ModuleBase;
use Zend\View\HelperPluginManager;

use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;

class Module extends ModuleBase
{
	public function getAutoloaderConfig()
	{
		return array(
            'Zend\Loader\ClassMapAutoloader' => array(
				__DIR__ . '/autoload_classmap.php',
			),
			'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
				),
			),
		);
	}

	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}   
	
	public function getServiceConfig()
	{
		return array(
			'factories' => array(
				'HtApplication\Mail\Transport\File' =>  'HtApplication\Service\Mail\Transport\FileFactory',
				'HtApplication\Mail\Transport\Smtp' =>  'HtApplication\Service\Mail\Transport\SmtpFactory',
				'HtApplication\Model\Mail' => 'HtApplication\Service\Model\MailFactory',
				'HtApplication\Model\EmailTemplate' => 'HtApplication\Service\Model\EmailTemplateFactory',
				'HtApplication\Model\MailQueue' => 'HtApplication\Service\Model\MailQueueFactory',
                'AuthenticateService' => 'HtApplication\Service\AuthenticateServiceFactory',
				'HtApplication\Model\Log' => 'HtApplication\Service\Model\LogFactory',
				'HtApplication\Model\LogCollection' => 'HtApplication\Service\Model\LogCollectionFactory',
    			'HtApplication\Model\Acl\Acl' => 'HtApplication\Service\Model\Acl\AclFactory',
    			'HtApplication\Model\Acl\UserRole' => 'HtApplication\Service\Model\Acl\UserRoleFactory',
    			'HtApplication\Model\Acl\UserRoleCollection' => 'HtApplication\Service\Model\Acl\UserRoleCollectionFactory',
    			'HtApplication\Model\Acl\Role' => 'HtApplication\Service\Model\Acl\RoleFactory',
    			'HtApplication\Model\Acl\PermissionAcl' => 'HtApplication\Service\Model\Acl\PermissionAclFactory',
                'LoggedInUser' => 'HtApplication\Service\LoggedInUserFactory',
    			'HtApplication\Model\Acl\Resource' => 'HtApplication\Service\Model\Acl\ResourceFactory',
			)
		);
	}
	
    public function getViewHelperConfig()
    {
        $viewHelperConfig = parent::getViewHelperConfig();
        //place navigation helper here as it requires application specific objects
        $factories = array();
        if (!empty($viewHelperConfig['factories'])) {
            $factories = $viewHelperConfig['factories'];
        }
        
        $factories = array_merge($factories, array(
            'navigation' => function (HelperPluginManager $pm) {
                //@note: menu helper checking acl through navigation root from it's template
                $sl = $pm->getServiceLocator();
                $acl = $sl->get('HtApplication\Model\Acl\Acl');
                $acl->init();
                //replace with specific Navigation so we can handle multiple roles
                /* @var $navigation \AppCore\View\Helper\Navigation */
                $navigation = $pm->get('AppCore\View\Helper\Navigation');
                $navigation
                        ->getPluginManager()
                        ->setInvokableClass('breadcrumbs', 'AppCore\View\Helper\Navigation\Breadcrumbs');
                
                $navigation->setAcl($acl->getNavigationAcl());

                $auth = $sl->get('AuthenticateService');
                $roles = array('guest');
                if ($auth->hasIdentity()) {
                    /* @var $user \HtUser\Model\User */
                    $user = $sl->get("LoggedInUser");
                    $roles = $user->getRole();
                }
                $navigation->setRoles($roles);

                return $navigation;
            },
        ));
        
        $viewHelperConfig['factories'] = $factories;
        return $viewHelperConfig;
    }
        
	public function onBootstrap($e)
	{
        /* @var $application \Zend\Mvc\Application */
        $application = $e->getApplication();
        $sharedManager = $application->getEventManager()->getSharedManager();
        /* PHP 5.3 require set default timezone before calling the date() function.
        * So we call date_default_timezone_set() function here to ensure default timezone alway is set.
        * You can also set this value in php.ini file via adding the line
        * date.timezone = Australia/Sydney
        * Can see List of supported Timezones here: http://php.net/manual/en/timezones.php
        */
        $config = $application->getConfig();
        $timezone = 'Australia/Sydney';
        if (!empty($config['i18n']['timezone'])) {
            $timezone = $config['i18n']['timezone'];
        }
        date_default_timezone_set($timezone);
        
        $sharedManager->attach('Zend\Mvc\Application', array(MvcEvent::EVENT_DISPATCH_ERROR, MvcEvent::EVENT_RENDER_ERROR), function(MvcEvent $e) {
                if ($e->getParam('exception')){
                    //when there is error: store error to log file and send email to admin
                    $sm = $e->getApplication()->getServiceManager();
                    $sm->get('Zend\Log\Logger')->err($e->getParam('exception'));
                }
            }
        );
        
        $sharedManager->attach('Zend\Mvc\Application', array(MvcEvent::EVENT_DISPATCH, MvcEvent::EVENT_DISPATCH_ERROR),  function (MvcEvent $e) {
            $sm = $e->getApplication()->getServiceManager();
            $auth = $sm->get('AuthenticateService');
            /* Do not return identity in JsonModel */
            $layout = $e->getViewModel();
            if ($auth->hasIdentity() && !($layout instanceof JsonModel)) {
                $config = $sm->get('Config');
                $payrollUrl = '#';
                if (isset($config['payroll']['server_url'])) {
                    $payrollUrl = $config['payroll']['server_url'];
                }
                $identity = $auth->getIdentity();
                $variables = array(
                    'identity' => $identity,
                    'payrollUrl' => $payrollUrl,
                );
                $layout->setVariables($variables);
            }
        });
	}
}
