<?php
/**
 * A base class for all application modules
 */
namespace AppCore;

use Zend\Session\Container;
use Zend\View\HelperPluginManager;
use Zend\Mvc\Controller\PluginManager;

class ModuleBase
{
    public function getViewHelperConfig()
    {
    	return array(
    		'factories' => array(
    			'tbFormRow' => function (HelperPluginManager $pm) {
    				$viewHelper = new \AppCore\Form\View\Helper\TbFormRow();
    				return $viewHelper;
    			},
    			"successMessage" => function(HelperPluginManager $pm) {
	    			$viewHelper = new \AppCore\View\Helper\SuccessMessage();
	    			return $viewHelper;    			
    			},
    			"infoMessage" => function(HelperPluginManager $pm) {
	    			$viewHelper = new \AppCore\View\Helper\InfoMessage();
	    			return $viewHelper;
    			},   
    			"errorMessage" => function(HelperPluginManager $pm) {
	    			$viewHelper = new \AppCore\View\Helper\ErrorMessage();
	    			return $viewHelper;
    			},  
    			"message" => function(HelperPluginManager $pm) {
	    			$viewHelper = new \AppCore\View\Helper\Message();
	    			return $viewHelper;
    			},
    			'pageName' => function (HelperPluginManager $pm) {
	    			$viewHelper = new \AppCore\View\Helper\PageName();
	    			return $viewHelper;
    			},
    			'formTag' => function (HelperPluginManager $pm) {
    				$viewHelper = new \AppCore\Form\View\Helper\FormTag();
    				return $viewHelper;
    			}, 
    			'formText' => function (HelperPluginManager $pm) {
	    			$viewHelper = new \AppCore\Form\View\Helper\FormText();
	    			return $viewHelper;
    			},
    			'formPassword' => function (HelperPluginManager $pm) {
	    			$viewHelper = new \AppCore\Form\View\Helper\FormPassword();
	    			return $viewHelper;
    			},
    			'tagList' => function (HelperPluginManager $pm) {
    				$viewHelper = new \AppCore\View\Helper\TagList();
    				return $viewHelper;
    			},
    			'monthSelect' => function (HelperPluginManager $pm) {
	    			$viewHelper = new \AppCore\View\Helper\MonthSelect();
	    			return $viewHelper;
    			},
    			'responsiveTable' => function (HelperPluginManager $pm) {
    				$viewHelper = new \AppCore\View\Helper\ResponsiveTable();
    				return $viewHelper;
    			},
				/**
				 * We want keep code follow zend helper,
				 * so we use key headLink, headScript, inlineScript of Zend Helper again to overwrite them
				 * for future we can change/remove library easier without changes anything.
				 */
				'headLink' => function (HelperPluginManager $pm) {
					$viewHelper = new \AppCore\View\Helper\MinifyHeadLink();
					$config = $pm->getServiceLocator()->get('config');
					/* we only enabled minify in production environment
					 * So we need a config for disable minify.
					 * If not config, It alway use minified file.
					 */
					if (isset($config['view_manager']['minifyJsCss'])) {
						$viewHelper->setMinifyCss($config['view_manager']['minifyJsCss']);
					}
					return $viewHelper;
				},
				'headScript' => function (HelperPluginManager $pm) {
					$viewHelper = new \AppCore\View\Helper\MinifyHeadScript();
					$config = $pm->getServiceLocator()->get('config');
					if (isset($config['view_manager']['minifyJsCss'])) {
						$viewHelper->setMinifyJs($config['view_manager']['minifyJsCss']);
					}
					return $viewHelper;
				},
				'inlineScript' => function (HelperPluginManager $pm) {
					$viewHelper = new \AppCore\View\Helper\MinifyInlineScript();
					$config = $pm->getServiceLocator()->get('config');
					if (isset($config['view_manager']['minifyJsCss'])) {
						$viewHelper->setMinifyJs($config['view_manager']['minifyJsCss']);
					}
					return $viewHelper;
				},
    		)
    	);
    }
    
    public function getControllerPluginConfig()
    {
    	return array(
    		'factories' => array(
    			'download' => function (PluginManager $pm) {
    				$helper = new \AppCore\Mvc\Controller\Plugin\Download();
    				return $helper;
		    	},
		    	'permission' => function (PluginManager $pm) {
			    	$helper = new \AppCore\Mvc\Controller\Plugin\Permission();
			    	return $helper;
		    	},
		    	'addEmptyOption' => function (PluginManager $pm) {
			    	$helper = new \AppCore\Mvc\Controller\Plugin\AddEmptyOption();
			    	return $helper;
		    	},
		    	'siteUrl' => function (PluginManager $pm) {
			    	$helper = new \AppCore\Mvc\Controller\Plugin\SiteUrl();
			    	return $helper;
		    	},
		    	'pagingParams' => function (PluginManager $pm) {
			    	$helper = new \AppCore\Mvc\Controller\Plugin\PagingParams();
			    	return $helper;
		    	},
		    	'successMessage' => function (PluginManager $pm) {
			    	$helper = new \AppCore\Mvc\Controller\Plugin\SuccessMessage();
			    	return $helper;
		    	},
		    	'exceptionMessage' => function (PluginManager $pm) {
			    	$helper = new \AppCore\Mvc\Controller\Plugin\ExceptionMessage();
			    	return $helper;
		    	},
				'referer' => function (PluginManager $pm) {
					$helper = new \AppCore\Mvc\Controller\Plugin\Referer();
					return $helper;
				},
				/**
				 * Override zend's init of flashMessenger
				 * so we can inject custom session manager
				 */
				'flashMessenger' => function (PluginManager $pm) {
			    	$helper = new \Zend\Mvc\Controller\Plugin\FlashMessenger();
					$sessionManager = $pm->getServiceLocator()->get("Zend\Session\SessionManager");
					$helper->setSessionManager($sessionManager);
			    	return $helper;
		    	},
		    )
    	);
    }    
}