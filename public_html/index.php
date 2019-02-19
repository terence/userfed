<?php
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

define('ROOT_PATH', realpath(dirname(__FILE__) . "/../"));

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

//Turn php error to Exception
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}
//migration note: PG1 use old mysql php extension so we see deprecated error, turn off 
//set_error_handler("exception_error_handler");

// Setup autoloading
include 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(include 'config/application.config.php')->run();