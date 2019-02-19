#! /bin/bash

echo -e "\n--- Provisioning virtual machine..."
DBPASSWD=password
REPO_DIR=/vagrant

sudo apt-get update

# Install Git
echo -e "\n--- Installing Git ---\n"
sudo apt-get install git -y

# Install Nginx
echo -e "\n--- Installing Nginx ---\n"
sudo apt-get install nginx -y

# Install PHP
sudo apt-get install python-software-properties build-essential -y
sudo apt-get update

echo -e "\n--- Installing PHP ---\n"
sudo apt-get install php5-common php5-dev php5-cli php5-fpm -y

echo -e "\nInstalling PHP extensions ---\n"
sudo apt-get install curl php5-curl php5-gd php5-mcrypt php5-mysql php5-intl -y
# Turn on mcrypt extensions
sudo php5enmod mcrypt
sudo service php5-fpm restart


# Install MySQL

echo -e "\n--- Preparing MySQL ---\n"
sudo apt-get install debconf-utils -y
debconf-set-selections <<< "mysql-server mysql-server/root_password password $DBPASSWD"
debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $DBPASSWD"

echo -e "\n--- Installing MySQL ---\n"
sudo apt-get install mysql-server -y

# Install phpMyAdmin
echo -e "\n--- Preparing phpMyAdmin ---\n"
debconf-set-selections <<< "phpmyadmin phpmyadmin/dbconfig-install boolean true"
debconf-set-selections <<< "phpmyadmin phpmyadmin/app-password-confirm password $DBPASSWD"
debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/admin-pass password $DBPASSWD"
debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/app-pass password $DBPASSWD"
debconf-set-selections <<< "phpmyadmin phpmyadmin/reconfigure-webserver multiselect none"

echo -e "\n--- Installing phpMyAdmin ---\n"
sudo apt-get install phpmyadmin -y > /dev/null

echo -e "\n--- Installing Composer for PHP package management ---\n"
sudo curl --silent https://getcomposer.org/installer | php > /dev/null
sudo mv composer.phar /usr/local/bin/composer

echo -e "\n--- Installing NodeJS and NPM ---\n"
sudo apt-get install nodejs -y
sudo apt-get install npm -y
echo -e "\n--- Installing Bower & Less css compiler---\n"
sudo npm install -g bower less

echo -e "\n--- Create block server ---\n"
sudo ln -s $REPO_DIR/script/vagrant/conf/server_block.conf /etc/nginx/sites-enabled/server_block.conf

# Set config for default server block can execute php files.
sudo rm -rf /etc/nginx/sites-enabled/default
sudo cat > /etc/nginx/sites-enabled/default <<'DEFAULT_CONFIG'
server {
    listen 80 default_server;
    listen [::]:80 default_server ipv6only=on;

    root /usr/share/nginx/html;
    index index.php index.html index.htm;

    server_name localhost;

    location / {
        try_files $uri $uri/ =404;
    }

    error_page 404 /404.html;
    error_page 500 502 503 504 /50x.html;
    location = /50x.html {
        root /usr/share/nginx/html;
    }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
DEFAULT_CONFIG

# Create symbol link to phpmyadmin
sudo ln -s /usr/share/phpmyadmin /usr/share/nginx/html/phpmyadmin

# PHP Config for Nginx
sudo sed -i "s/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/" /etc/php5/fpm/php.ini

sudo service php5-fpm restart
sudo service nginx restart

# Init DB
echo -e "\n--- Initializing database for first running ---\n";
sudo chmod +x $REPO_DIR/script/CI/local/database-deploy.sh
$REPO_DIR/script/CI/local/database-deploy.sh