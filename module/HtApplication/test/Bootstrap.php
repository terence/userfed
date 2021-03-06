<?php

/**
 * Boostrap in this example work (even it's old version)
 * @see http://framework.zend.com/manual/2.0/en/user-guide/unit-testing.html 
 * 
 * @see http://framework.zend.com/manual/2.2/en/tutorials/unittesting.html
 * @see http://framework.zend.com/manual/2.2/en/modules/zend.test.phpunit.html
 */

namespace HtApplicationTest;

use Zend\Loader\AutoloaderFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;
use RuntimeException;

error_reporting(E_ALL | E_STRICT);
chdir(__DIR__);

// Define application environment
function getAppEnv()
{
	$env = getenv('APPLICATION_ENV');
	if (!empty($env)) {
		return $env;
	}
	
	//some apache version, setenv in htaccess file with rewrite will add REDIRECT_ 
	//prefix to setting
	$env = getenv('REDIRECT_APPLICATION_ENV');
	if (!empty($env)) {
		return $env;
	}
	//default env
	return 'production';
}
defined('APPLICATION_ENV') || define('APPLICATION_ENV', getAppEnv());

/**
 * If we use mail file transport, we will need ROOT_PATH to define mail folder
 */
if (!defined('ROOT_PATH')) {
	define('ROOT_PATH', realpath(dirname(__FILE__) . "/../../../"));
}

class Bootstrap {

	protected static $serviceManager;
	protected static $config;
	protected static $bootstrap;

	public static function init() {
		// Load the user-defined test configuration file, if it exists; otherwise, load
		if (is_readable(__DIR__ . '/TestConfig.php')) {
			$testConfig = include __DIR__ . '/TestConfig.php';
		} else {
			$testConfig = include __DIR__ . '/TestConfig.php.dist';
		}

		$zf2ModulePaths = array();

		if (isset($testConfig['module_listener_options']['module_paths'])) {
			$modulePaths = $testConfig['module_listener_options']['module_paths'];
			foreach ($modulePaths as $modulePath) {
				if (($path = static::findParentPath($modulePath))) {
					$zf2ModulePaths[] = $path;
				}
			}
		}

		$zf2ModulePaths = implode(PATH_SEPARATOR, $zf2ModulePaths) . PATH_SEPARATOR;
		$zf2ModulePaths .= getenv('ZF2_MODULES_TEST_PATHS') ? : (defined('ZF2_MODULES_TEST_PATHS') ? ZF2_MODULES_TEST_PATHS : '');

		static::initAutoloader();

		// use ModuleManager to load this module and it's dependencies
		$baseConfig = array(
			'module_listener_options' => array(
				'module_paths' => explode(PATH_SEPARATOR, $zf2ModulePaths),
			),
		);

		$config = ArrayUtils::merge($baseConfig, $testConfig);

		$serviceManager = new ServiceManager(new ServiceManagerConfig());
		$serviceManager->setService('ApplicationConfig', $config);
		$serviceManager->get('ModuleManager')->loadModules();
		static::$serviceManager = $serviceManager;
		static::$config = $config;
	}

	public static function getServiceManager() {
		return static::$serviceManager;
	}

	public static function getConfig() {
		return static::$config;
	}

	protected static function initAutoloader() {
		$vendorPath = static::findParentPath('vendor');

		if (is_readable($vendorPath . '/autoload.php')) {
			include $vendorPath . '/autoload.php';
		} else {
			$zf2Path = getenv('ZF2_PATH') ? : (defined('ZF2_PATH') ? ZF2_PATH : (is_dir($vendorPath . '/ZF2/library') ? $vendorPath . '/ZF2/library' : false));

			if (!$zf2Path) {
				throw new RuntimeException('Unable to load ZF2. Run `php composer.phar install` or define a ZF2_PATH environment variable.');
			}

			include $zf2Path . '/Zend/Loader/AutoloaderFactory.php';
		}

		AutoloaderFactory::factory(array(
			'Zend\Loader\StandardAutoloader' => array(
				'autoregister_zf' => true,
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/' . __NAMESPACE__,
				),
			),
		));
	}

	protected static function findParentPath($path) {
		$dir = __DIR__;
		$previousDir = '.';
		while (!is_dir($dir . '/' . $path)) {
			$dir = dirname($dir);
			if ($previousDir === $dir)
				return false;
			$previousDir = $dir;
		}
		return $dir . '/' . $path;
	}

}

Bootstrap::init();