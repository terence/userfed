#@link http://stackoverflow.com/questions/1885525/how-do-i-prompt-a-user-for-confirmation-in-bash-script
#@link http://stackoverflow.com/questions/59838/how-to-check-if-a-directory-exists-in-a-shell-script
#@link http://stackoverflow.com/questions/5947742/how-to-change-the-output-color-of-echo-in-linux

green='\e[0;32m'
yellow='\e[1;33m'
NC='\e[0m' # No Color

#/zf_projects/surtion/tool/setup.sh repo-name branch-name
REPO=userfed

if [ -z "$1" ]; then
	BRANCH=develop
else
	BRANCH=$1
fi

SOURCE_PATH=/home/ht_dev2/zf_projects
DEST_PATH=/home/ht_dev2/userfed.hometradies.com

echo -e "${green}Repo ${NC} : $REPO"
echo -e "${green}Branch ${NC} : $BRANCH"

#setup folder
if [ ! -d "$SOURCE_PATH/$REPO" ]; then
	echo -e "$SOURCE_PATH/$REPO doesn't exist, ${green}Create it ${NC}"
	mkdir $SOURCE_PATH/$REPO
else
	echo -e "$SOURCE_PATH/$REPO : ${green}exist ${NC}"
fi

if [ ! -d "$SOURCE_PATH/$REPO/$BRANCH" ]; then
	echo -e "$SOURCE_PATH/$REPO/$BRANCH doesn't exist, ${green}Create it ${NC}"
	mkdir $SOURCE_PATH/$REPO/$BRANCH
fi

cd $SOURCE_PATH/$REPO

if [ ! -d "$BRANCH/.git" ]; then
	#get code from git
	git clone https://bitbucket.org/trexanhlab/userfed.git $BRANCH
else
	echo -e "$ROOT_PATH/$BRANCH : ${green}exist ${NC}"
	cd $SOURCE_PATH/$REPO/$BRANCH
	git pull origin $BRANCH	
	
fi

git checkout $BRANCH

# copy code first time
cd $DEST_PATH
rm -Rf *
cp  -rRp $SOURCE_PATH/$REPO/$BRANCH/userfed/code/* .

mv public_html public

if [ ! -f "config/autoload/local.php" ]; then
	echo "Create zend config file"
	cp config/autoload/local.php.dist config/autoload/local.php
	php $SOURCE_PATH/tool/setup.php local.php
	
fi

if [ ! -f "composer.phar" ]; then
	php -r "readfile('https://getcomposer.org/installer');" | php	
	php composer.phar update
	cp -rRp vendor/mrclay/minify/min public
else
	echo -e "composer.phar : ${green}exist ${NC}"
	
fi

echo -e "${yellow}Todo: ${NC}"
echo -e "${yellow}- run ./update.sh $BRANCH ${NC}"
echo -e "${yellow}- update config/autoload/local.php ${NC}"