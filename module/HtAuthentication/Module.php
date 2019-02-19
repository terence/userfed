<?php

namespace HtAuthentication;

class Module
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
				'AuthAccount\OAuth' => 'HtAuthentication\Service\Model\Adapter\OAuthFactory',
				'AuthAccount\Internal' => 'HtAuthentication\Service\Model\Adapter\InternalFactory',
				'AuthAccount' => 'HtAuthentication\Service\Model\AuthenticationAccountFactory',
				'AuthAccountCollection' => 'HtAuthentication\Service\Model\AuthenticationAccountCollectionFactory',
				'App' => 'HtAuthentication\Service\Model\ApplicationFactory',
				'AppServer' => 'HtAuthentication\Service\Model\ApplicationServerFactory',
				'AppServerCollection' => 'HtAuthentication\Service\Model\ApplicationServerCollectionFactory',
				'Server' => 'HtAuthentication\Service\Model\ServerFactory',
				'Token' => 'HtAuthentication\Service\Model\TokenFactory',
				'UserApp' => 'HtAuthentication\Service\Model\UserApplicationFactory',
				'UserAppCollection' => 'HtAuthentication\Service\Model\UserApplicationCollectionFactory',
				'Org' => 'HtAuthentication\Service\Model\OrganisationFactory',
				'AppOrg' => 'HtAuthentication\Service\Model\ApplicationOrganisationFactory',
				'AppServerOrg' => 'HtAuthentication\Service\Model\ApplicationServerOrganisationFactory',
				'AppServerOrgCollection' => 'HtAuthentication\Service\Model\ApplicationServerOrganisationCollectionFactory',
				'UserOrg' => 'HtAuthentication\Service\Model\UserOrganisationFactory',
				'UserOrgCollection' => 'HtAuthentication\Service\Model\UserOrganisationCollectionFactory',
			),
		);
	}

}
