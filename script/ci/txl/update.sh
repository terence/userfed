#@link http://stackoverflow.com/questions/5947742/how-to-change-the-output-color-of-echo-in-linux
green='\e[0;32m'
yellow='\e[1;33m'
NC='\e[0m' # No Color

#by default we deploy to public's branch folder like httpdocs/branch-name
#if we want to deploy to public's root folder then try
#./update.sh -r lab0607 feature-try-jquerymobile-bootstrap-for-surtion-search-form
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
#./update.sh lab0607 feature-try-jquerymobile-bootstrap-for-surtion-search-form

echo -e "${green}Repo ${NC} $REPO"
echo -e "${green}Branch ${NC} $BRANCH"


echo -e "${green}Update code from git ${NC}"
cd $ROOT_PATH/$BRANCH
/usr/local/bin/git pull origin $BRANCH

echo -e "${green}Copy code to web public folder ${NC}"
if [ $DEPLOY_TO_PUBLIC_ROOT = "0" ]; then
	cd $PUBLIC_PATH/$BRANCH
else
	cd $PUBLIC_PATH
fi

# copy folders
for COPY_ITEM in css js image bower.json lib-custom
do
	if [ -d $COPY_ITEM ]; then
		rm -Rf $COPY_ITEM
	fi
	
	cp -rRp $ROOT_PATH/$BRANCH/$PUBLIC_FOLDER/$COPY_ITEM .
	
done

# generate css from less
echo -e "${green}less -> css ${NC}"
for ITEM in css/style lib-custom/bootstrap/bootstrap lib-custom/datatables/custom
do
	echo "compile $ITEM.less -> $ITEM.css"
	$TOOL_PATH/vendor/oyejorge/less.php/bin/lessc $ITEM.less > $ITEM.min.css	
done

# post css work
# copy bootstrap to default directory
echo -e "${green}less -> css : moving files ${NC}"
# we need to place bootstrap.min.css to bootstrap folder so fontawesome work
cp -f lib-custom/bootstrap/bootstrap.min.css lib/bootstrap/dist/css/bootstrap.min.css