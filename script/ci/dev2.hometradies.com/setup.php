<?php
//var_dump($argv);
//php setup.php -r composer.json,.bowerrc,index.php,.htaccess
//php setup.php composer.json,.bowerrc,index.php,.htaccess

$DEST_PATH="/home/ht_dev2/userfed.hometradies.com";
$fileList = explode(",",$argv[1]);

//update file config/autoload/local.php
function updateLocalPhp()
{
	global $DEST_PATH;
	
	//include will evaluate ROOT_PATH so we fake the value here for later restore it
	define('ROOT_PATH', 'ROOT_PATH');
	//update db info
	$file = $DEST_PATH . "/config/autoload/local.php";
	$arr = include $file;
	$arr['db']['dsn'] = 'mysql:dbname=ht_dev2;host=hero.hometradies.com';
	$arr['db']['username'] = 'ht_dev2';
	$arr['db']['password'] = '-8Xnf!b2db';
	$content = var_export($arr, true);
	$content = str_replace('\'ROOT_PATH', 'ROOT_PATH . \'', $content);
	file_put_contents($file, "<?php return " . $content . ";");
}

foreach ($fileList as $file) {
	switch($file) {
		case "local.php":		
			updateLocalPhp();
			break;
	}
}