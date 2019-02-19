#@link http://stackoverflow.com/questions/5947742/how-to-change-the-output-color-of-echo-in-linux
green='\e[0;32m'
yellow='\e[1;33m'
NC='\e[0m' # No Color

REPO=userfed

if [ -z "$1" ]; then
	BRANCH=develop
else
	BRANCH=$1
fi

TOOL_PATH=/home/ht_dev2/zf_projects/tool
SOURCE_PATH=/home/ht_dev2/zf_projects
DEST_PATH=/home/ht_dev2/userfed.hometradies.com

echo -e "${green}Repo ${NC} $REPO"
echo -e "${green}Branch ${NC} $BRANCH"


echo -e "${green}Update code from git ${NC}"
cd $SOURCE_PATH/$REPO/$BRANCH
git pull origin $BRANCH	

cd $DEST_PATH
for ITEM in module data
do
	rm -Rf $ITEM
	cp  -rRp $SOURCE_PATH/$REPO/$BRANCH/userfed/code/$ITEM .
	
done

cd $DEST_PATH/public
for ITEM in lib-custom js image css
do
	rm -Rf $ITEM
	cp  -rRp $SOURCE_PATH/$REPO/$BRANCH/userfed/code/public_html/$ITEM .
	
done


cd $DEST_PATH/public
# generate css from less
for ITEM in css/style bootstrap lib-custom/datatables/custom
do
	if [ $ITEM = "bootstrap" ]; then
		echo "we need to place bootstrap.min.css to bootstrap folder so fontawesome work"
		echo "compile lib-custom/bootstrap/$ITEM.less -> lib/bootstrap/dist/css/$ITEM.min.css"
		$TOOL_PATH/vendor/oyejorge/less.php/bin/lessc lib-custom/bootstrap/$ITEM.less > lib/bootstrap/dist/css/$ITEM.min.css
	else
		echo "compile $ITEM.less -> $ITEM.css"
		$TOOL_PATH/vendor/oyejorge/less.php/bin/lessc $ITEM.less > $ITEM.min.css
	fi
	
done