echo "Update git and composer for userfed"
cd /home/ht_dev2/zf_projects/user-federation/
git pull origin master
cd /home/ht_dev2/zf_projects/user-federation/userfed/code
php composer.phar self-update
php composer.phar update

#copy all files/folder from git pull version
echo "Copy userfed code"
cd /home/ht_dev2/userfed.hometradies.com
mkdir bk
mv public/.htaccess config/autoload/local.php bk
find . -not -path "./bk*" -delete
cp -rRp /home/ht_dev2/zf_projects/user-federation/userfed/code/* .
mv public_html public
mv -f bk/.htaccess public
mv -f bk/local.php config/autoload
rm -Rf bk

echo "Update client app - app1"
cd /home/ht_dev2/app1.hometradies.com/public
mkdir bk
mv vendor app/config.php composer.phar bk
find . -not -path "./bk*" -delete
cp -rRp /home/ht_dev2/zf_projects/user-federation/sample-client/. /home/ht_dev2/app1.hometradies.com/public
mv bk/vendor bk/composer.phar .
mv bk/config.php ./app
rm -Rf bk
php composer.phar self-update
php composer.phar update

echo "Update client app - app2"
cd /home/ht_dev2/app2.hometradies.com/public
mkdir bk
mv vendor app/config.php composer.phar bk
find . -not -path "./bk*" -delete
cp -rRp /home/ht_dev2/zf_projects/user-federation/sample-client/. /home/ht_dev2/app2.hometradies.com/public
mv bk/vendor bk/composer.phar .
mv bk/config.php ./app
rm -Rf bk
php composer.phar self-update
php composer.phar update