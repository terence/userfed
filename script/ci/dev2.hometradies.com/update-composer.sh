#@link http://stackoverflow.com/questions/5947742/how-to-change-the-output-color-of-echo-in-linux
green='\e[0;32m'
yellow='\e[1;33m'
NC='\e[0m' # No Color

REPO=$1
BRANCH=$2
ROOT_PATH=/var/www/vhosts/sites.trexanhlab.com/zf_projects/$REPO
PUBLIC_PATH=/var/www/vhosts/sites.trexanhlab.com/subdomains/$REPO/httpdocs
CODE_FOLDER=code/public_html
#./update-composer.sh repo branch

echo -e "${green}Repo ${NC} $REPO"
echo -e "${green}Branch ${NC} $BRANCH"


echo -e "${green}Update code from git ${NC}"
cd $ROOT_PATH/$BRANCH
/usr/local/bin/git pull origin $BRANCH

cd $ROOT_PATH/$BRANCH/code
php composer.phar update