green='\e[0;32m'
yellow='\e[1;33m'
red='\e[0;31m'
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

echo -e "${red}Remove${NC}"
echo -e "${red}Repo:${NC} $REPO"
echo -e "${red}Branch:${NC} $BRANCH"

if [ $DEPLOY_TO_PUBLIC_ROOT == "0" ]; then
	rm -Rf $PUBLIC_PATH/$BRANCH	
else
	for ITEM in css lib min lib-custom js image
	do
		rm -Rf $PUBLIC_PATH/$ITEM
	done
	
	for ITEM in index.php .htaccess .bowerrc bower.json
	do
		rm -f $PUBLIC_PATH/$ITEM
	done
	
fi

rm -Rf $ROOT_PATH/$BRANCH