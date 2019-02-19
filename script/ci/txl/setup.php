<?php
//php setup.php repo branch
//var_dump($argv);
//setup.php -r repo-name branch-name composer.json,.bowerrc,index.php,.htaccess
//setup.php repo-name branch-name composer.json,.bowerrc,index.php,.htaccess
if ($argv[1] == "-r") {
	$DEPLOY_TO_PUBLIC_ROOT = true;
	$REPO = $argv[2];
	$BRANCH = $argv[3];
	$fileList = explode(",",$argv[4]);
} else {
	$DEPLOY_TO_PUBLIC_ROOT = false;
	$REPO = $argv[1];
	$BRANCH = $argv[2];
	$fileList = explode(",",$argv[3]);
}
$BASE_PATH="/var/www/vhosts/sites.trexanhlab.com";
$ROOT_PATH = $BASE_PATH . "/zf_projects/" . $REPO;
$PUBLIC_PATH = $BASE_PATH . "/subdomains/$REPO/httpdocs";
$CODE_FOLDER = "userfed/code";
$PUBLIC_FOLDER = "userfed/code/public_html";

function updateComposer() {
	//composer.json 
	global $BASE_PATH, $ROOT_PATH, $PUBLIC_PATH, $DEPLOY_TO_PUBLIC_ROOT, $BRANCH, $CODE_FOLDER;
	$file = $ROOT_PATH . "/" . $BRANCH . "/" . $CODE_FOLDER . "/composer.json";
	$content = file_get_contents($file);
	$content = json_decode($content,true);
	$content["config"] = array("cache-dir" => $BASE_PATH . "/private/composercache");
	$content = json_encode($content);
	file_put_contents($file, $content);
}

function updateBower() {
	//.bowerrc
	global $BASE_PATH, $ROOT_PATH, $PUBLIC_PATH, $DEPLOY_TO_PUBLIC_ROOT, $BRANCH, $CODE_FOLDER;
	$file = $PUBLIC_PATH . "/" . $BRANCH . "/.bowerrc";
	if ($DEPLOY_TO_PUBLIC_ROOT) {
		$file = $PUBLIC_PATH . "/.bowerrc";
	}
	
	$content = file_get_contents($file);
	$content = json_decode($content,true);
	$content["storage"] = array("packages" => $BASE_PATH . "/private/bowercache");
	$content = json_encode($content);
	file_put_contents($file, $content);
}

function updateIndexPhp() {
	//index.php
	global $BASE_PATH, $ROOT_PATH, $PUBLIC_PATH, $DEPLOY_TO_PUBLIC_ROOT, $BRANCH, $CODE_FOLDER;
	$indexFile = $PUBLIC_PATH . "/" . $BRANCH . "/" . "index.php";
	if ($DEPLOY_TO_PUBLIC_ROOT) {
		$indexFile = $PUBLIC_PATH . "/" . "index.php";
	}
	
	$content = file_get_contents($indexFile);
	$replaceString = array(
		"define('ROOT_PATH', realpath(dirname(__FILE__) . \"/../\"));" => "define('ROOT_PATH', \"{$ROOT_PATH}/{$BRANCH}/$CODE_FOLDER\");
	",
		"chdir(dirname(__DIR__));" => "chdir(\"{$ROOT_PATH}/{$BRANCH}/$CODE_FOLDER\");"
	);
	foreach ($replaceString as $key => $value) {
		$content = str_replace($key, $value, $content);
	}

	file_put_contents($indexFile, $content);	
}

function updateHtaccess() {
	//.htaccess
	global $BASE_PATH, $ROOT_PATH, $PUBLIC_PATH, $DEPLOY_TO_PUBLIC_ROOT, $BRANCH, $CODE_FOLDER;
	$htaccessFile = $PUBLIC_PATH . "/" . $BRANCH . "/" . ".htaccess";
	if ($DEPLOY_TO_PUBLIC_ROOT) {
		$htaccessFile = $PUBLIC_PATH . "/" . ".htaccess";
	}
	$content = file_get_contents($htaccessFile);
	$content = "php_flag display_errors on\n" . $content;
	file_put_contents($htaccessFile, $content);
}

//update file config/autoload/local.php
function updateLocalPhp()
{
	global $BASE_PATH, $ROOT_PATH, $PUBLIC_PATH, $DEPLOY_TO_PUBLIC_ROOT, $BRANCH, $CODE_FOLDER;
	
	//include will evaluate ROOT_PATH so we fake the value here for later restore it
	define('ROOT_PATH', 'ROOT_PATH');
	//update db info
	$file = $ROOT_PATH . "/" . $BRANCH . "/" . $CODE_FOLDER . "/config/autoload/local.php";
	$arr = include $file;
	$arr['db']['dsn'] = 'mysql:dbname=fashiona_userfed;host=localhost';
	$arr['db']['username'] = 'fashi_userfed';
	$arr['db']['password'] = 'mG1OwtwqLxEINF';
    
    if ($DEPLOY_TO_PUBLIC_ROOT) {
        $arr['view_manager']['minifyJsCss'] = true;
    } else {
        $arr['view_manager']['minifyJsCss'] = false;
    }
	$content = var_export($arr, true);
	$content = str_replace('\'ROOT_PATH', 'ROOT_PATH . \'', $content);
	file_put_contents($file, "<?php return " . $content . ";");
}

foreach ($fileList as $file) {
	switch($file) {
		case "composer.json" :
			updateComposer();
			break;
		case ".bowerrc" :
			updateBower();
			break;
		case "index.php":
			updateIndexPhp();
			break;
		case ".htaccess":		
			updateHtaccess();
			break;
		case "local.php":		
			updateLocalPhp();
			break;
	}
}