#@link http://stackoverflow.com/questions/1885525/how-do-i-prompt-a-user-for-confirmation-in-bash-script
#@link http://stackoverflow.com/questions/59838/how-to-check-if-a-directory-exists-in-a-shell-script
#@link http://stackoverflow.com/questions/5947742/how-to-change-the-output-color-of-echo-in-linux

#./update-database.sh repo branch
green='\e[0;32m'
yellow='\e[1;33m'
NC='\e[0m' # No Color

REPO=$1
BRANCH=$2
ROOT_PATH=/var/www/vhosts/sites.trexanhlab.com/zf_projects/$REPO

echo -e "${green}Repo ${NC} $REPO"
echo -e "${green}Branch ${NC} $BRANCH"

mysql -h127.0.0.1 -ufashi_userfed -p'mG1OwtwqLxEINF' -e "drop database fashiona_userfed"
mysql -h127.0.0.1 -ufashi_userfed -p'mG1OwtwqLxEINF' -e "create database fashiona_userfed CHARACTER SET utf8 COLLATE utf8_unicode_ci;"
mysql -h127.0.0.1 -ufashi_userfed -p'mG1OwtwqLxEINF' fashiona_userfed < "$ROOT_PATH/$BRANCH/userfed/document/database/database.sql"
mysql -h127.0.0.1 -ufashi_userfed -p'mG1OwtwqLxEINF' fashiona_userfed < "$ROOT_PATH/$BRANCH/userfed/document/database/database-init.sql"
mysql -h127.0.0.1 -ufashi_userfed -p'mG1OwtwqLxEINF' fashiona_userfed < "$ROOT_PATH/$BRANCH/userfed/document/deploy/txl/database-init-test.sql"