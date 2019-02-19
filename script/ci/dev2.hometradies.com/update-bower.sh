#@link http://stackoverflow.com/questions/5947742/how-to-change-the-output-color-of-echo-in-linux
green='\e[0;32m'
yellow='\e[1;33m'
NC='\e[0m' # No Color

#update-bower.sh develop

REPO=userfed

if [ -z "$1" ]; then
	BRANCH=develop
else
	BRANCH=$1
fi

TOOL_PATH=/home/ht_dev2/zf_projects/tool
SOURCE_PATH=/home/ht_dev2/zf_projects/$REPO/$BRANCH/userfed/code/public_html
DEST_PATH=/home/ht_dev2/userfed.hometradies.com/public

echo "Update bower.json"
cd $DEST_PATH
for COPY_ITEM in bower.json
do
	cp  -rRp $SOURCE_PATH/$COPY_ITEM .
done

$TOOL_PATH/vendor/bin/bowerphp install