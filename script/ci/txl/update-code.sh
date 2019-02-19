echo "Update git and composer for userfed"
cd /zf_projects/user-federation/
git pull origin master
cd /zf_projects/user-federation/userfed/code
php composer.phar self-update
php composer.phar update

echo "Update public file by overwrite /subdomains/userfed/httpdocs"
#rm -Rf /subdomains/userfed/httpdocs/* /subdomains/userfed/httpdocs/.htaccess
#find . \! -name 'index.php' -print
#copy all files/folder from git pull version, keep index.php
cd /subdomains/userfed/httpdocs/
mkdir bk
mv index.php .htaccess phpmyadmin bk
find . -not -path "./bk*" -delete
cp -rRp /zf_projects/user-federation/userfed/code/public_html/. /subdomains/userfed/httpdocs
mv bk/index.php bk/.htaccess bk/phpmyadmin .
rm -Rf bk

echo "Update client app - userfed-app1"
cd /subdomains/userfed-app1/httpdocs
mkdir bk
mv vendor app/config.php composer.phar bk
find . -not -path "./bk*" -delete
cp -rRp /zf_projects/user-federation/sample-client/. /subdomains/userfed-app1/httpdocs
mv bk/vendor bk/composer.phar .
mv bk/config.php ./app
rm -Rf bk

echo "Update client app - userfed-app2"
cd /subdomains/userfed-app2/httpdocs
mkdir bk
mv vendor app/config.php composer.phar bk
find . -not -path "./bk*" -delete
cp -rRp /zf_projects/user-federation/sample-client/. /subdomains/userfed-app2/httpdocs
mv bk/vendor bk/composer.phar .
mv bk/config.php ./app
rm -Rf bk