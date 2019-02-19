#@link http://stackoverflow.com/questions/5947742/how-to-change-the-output-color-of-echo-in-linux
green='\e[0;32m'
yellow='\e[1;33m'
NC='\e[0m' # No Color

DEPLOY_TO_PUBLIC_ROOT="0"

if [ $1 = "-r" ]; then
    DEPLOY_TO_PUBLIC_ROOT="1"
    shift
fi

REPO=$1
BRANCH=$2
BASE_PATH="/var/www/vhosts/sites.trexanhlab.com"
ROOT_PATH=$BASE_PATH/zf_projects/$REPO
PUBLIC_PATH=$BASE_PATH/subdomains/$REPO/httpdocs
TOOL_PATH=$BASE_PATH/zf_projects/tool
CODE_FOLDER=userfed/code
PUBLIC_FOLDER=userfed/code/public_html
#./update-composer.sh repo branch

echo -e "${green}Repo ${NC} $REPO"
echo -e "${green}Branch ${NC} $BRANCH"

if [ $DEPLOY_TO_PUBLIC_ROOT = "0" ]; then
	cd $PUBLIC_PATH/$BRANCH
else
	cd $PUBLIC_PATH
fi

echo "Update bower.json"
for COPY_ITEM in bower.json
do
	cp -rRp $ROOT_PATH/$BRANCH/$PUBLIC_FOLDER/$COPY_ITEM .
done

$TOOL_PATH/vendor/bin/bowerphp install