#@link http://stackoverflow.com/questions/1885525/how-do-i-prompt-a-user-for-confirmation-in-bash-script
#@link http://stackoverflow.com/questions/59838/how-to-check-if-a-directory-exists-in-a-shell-script
#@link http://stackoverflow.com/questions/5947742/how-to-change-the-output-color-of-echo-in-linux

green='\e[0;32m'
yellow='\e[1;33m'
NC='\e[0m' # No Color

#by default we deploy to public's branch folder like httpdocs/branch-name
#if we want to deploy to public's root folder then try
#/zf_projects/surtion/tool/setup.sh -r repo-name branch-name
DEPLOY_TO_PUBLIC_ROOT="0"

if [ $1 = "-r" ]; then
    DEPLOY_TO_PUBLIC_ROOT="1"
    shift
fi

#/zf_projects/surtion/tool/setup.sh repo-name branch-name
REPO=$1
BRANCH=$2
ROOT_PATH=/var/www/vhosts/sites.trexanhlab.com/zf_projects/$REPO
PUBLIC_PATH=/var/www/vhosts/sites.trexanhlab.com/subdomains/$REPO/httpdocs
CODE_FOLDER=userfed/code
PUBLIC_FOLDER=userfed/code/public_html

echo -e "${green}Repo ${NC} : $REPO"
echo -e "${green}Branch ${NC} : $BRANCH"

#setup folder
if [ ! -d "$ROOT_PATH" ]; then
	echo -e "$ROOT_PATH doesn't exist, ${green}Create it ${NC}"
	mkdir $ROOT_PATH
else
	echo -e "$ROOT_PATH : ${green}exist ${NC}"
fi

if [ ! -d "$ROOT_PATH/$BRANCH" ]; then
	echo -e "$ROOT_PATH/$BRANCH doesn't exist, ${green}Create it ${NC}"
	echo -e "${green}ROOT_PATH ${NC} : $ROOT_PATH"
	cd $ROOT_PATH
	echo -e "Create branch folder: $BRANCH"
	mkdir $BRANCH
	#get code from git
	#git clone https://tuanngominh@bitbucket.org/trexanhlab/$REPO.git $BRANCH
	cd $BRANCH
	git init
	#@link http://stackoverflow.com/questions/6368987/how-do-i-fetch-only-one-branch-of-a-remote-git-repository
	git remote add -t $BRANCH origin git@bitbucket.org:trexanhlab/$REPO.git
	#git remote add origin git@bitbucket.org:trexanhlab/$REPO.git
	#git fetch --all
	git fetch origin $BRANCH
	#git branch master origin/master
else
	cd $ROOT_PATH/$BRANCH
	/usr/local/bin/git pull origin $BRANCH
	echo -e "$ROOT_PATH/$BRANCH : ${green}exist ${NC}"
fi

git checkout $BRANCH
cd $CODE_FOLDER

if [ ! -f "local.php" ]; then
	echo "Create zend config file"
	cp config/autoload/local.php.dist config/autoload/local.php
fi

#replace txl/txlzf reference from http to ssh 
#so we can fetch txl/txlzf from git though http: without error 
#(fetch though ssh -> error as our hosting package has limited RAM)
#@link http://stackoverflow.com/questions/15751651/sed-command-to-replace-code-in-file-and-save
#@link http://stackoverflow.com/questions/3306007/replace-a-string-in-shell-script
sed -i 's_"txl\(.*\),_"txl/txlzf": "dev-develop-git-http",_' composer.json

echo "add cache dir for composer, bower"
echo "update config for local.php"

if [ $DEPLOY_TO_PUBLIC_ROOT = "0" ]; then
	php $ROOT_PATH/tool/setup.php $REPO $BRANCH composer.json,local.php
else
	php $ROOT_PATH/tool/setup.php -r $REPO $BRANCH composer.json,local.php
fi

if [ ! -f "composer.phar" ]; then
	php -r "readfile('https://getcomposer.org/installer');" | php	
	php composer.phar update
else
	echo -e "composer.phar : ${green}exist ${NC}"
fi

sed -i 's/dev-develop-git-http/dev-develop/g' composer.json

#create public folder
if [ $DEPLOY_TO_PUBLIC_ROOT = "0" ]; then
	if [ ! -d "$PUBLIC_PATH/$BRANCH" ]; then
		mkdir $PUBLIC_PATH/$BRANCH
	else
		echo -e "$PUBLIC_PATH/$BRANCH : ${green}exist ${NC}"
	fi
fi

echo -e "${green}Copy initial code to web public folder ${NC}"
if [ $DEPLOY_TO_PUBLIC_ROOT = "0" ]; then
	cd $PUBLIC_PATH/$BRANCH
else
	cd $PUBLIC_PATH
fi

for COPY_ITEM in index.php .htaccess bower.json .bowerrc min
do
	if [ -d $COPY_ITEM ]; then
		rm -Rf $COPY_ITEM
	fi

	if [ -f $COPY_ITEM ]; then
		rm -f $COPY_ITEM
	fi
	
	if [ $COPY_ITEM = "min" ]; then
		cp -rRp $ROOT_PATH/$BRANCH/$CODE_FOLDER/vendor/mrclay/minify/$COPY_ITEM .
	else
		cp -rRp $ROOT_PATH/$BRANCH/$PUBLIC_FOLDER/$COPY_ITEM .
	fi
	
done

echo "patch index.php, .htaccess with new code location"
if [ $DEPLOY_TO_PUBLIC_ROOT = "0" ]; then
	php $ROOT_PATH/tool/setup.php $REPO $BRANCH index.php,.htaccess,.bowerrc
else
	php $ROOT_PATH/tool/setup.php -r $REPO $BRANCH index.php,.htaccess,.bowerrc
fi

echo -e "${yellow}Todo: ${NC}"

ROOT_OPTION=""
if [ $DEPLOY_TO_PUBLIC_ROOT = "1" ]; then
	ROOT_OPTION="-R"
fi

echo -e "${yellow}- run ./update-bower.sh $ROOT_OPTION $REPO $BRANCH ${NC}"
echo -e "${yellow}- run ./update.sh $ROOT_OPTION $REPO $BRANCH ${NC}"

echo -e "${yellow}Other Todo: ${NC}"
echo -e "if you think database is out of date"
echo -e "${yellow}- run ./update-database.sh $REPO $BRANCH ${NC}"
echo -e "Remove branch code"
echo -e "${yellow}- ./remove.sh $ROOT_OPTION $REPO $BRANCH ${NC}"