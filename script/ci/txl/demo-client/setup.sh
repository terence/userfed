#get code from svn
svn checkout https://xp-dev.com/svn/hometradies/trunk/democlientapp /subdomains/payroll/httpdocs

# get libraries through composer
cd /subdomains/payroll/httpdocs
php -r "eval('?>'.file_get_contents('https://getcomposer.org/installer'));"
php composer.phar update
