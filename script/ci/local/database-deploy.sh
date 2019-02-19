REPO=/vagrant
DB_USER=root
DB_PASSWORD=password
DB_NAME=trexanhlab_pg2
mysql -u$DB_USER -p$DB_PASSWORD -e "drop database $DB_NAME"
mysql -u$DB_USER -p$DB_PASSWORD -e "create database $DB_NAME CHARACTER SET utf8 COLLATE utf8_unicode_ci;"
mysql -u$DB_USER -p$DB_PASSWORD $DB_NAME < "$REPO/script/database/database.sql"
mysql -u$DB_USER -p$DB_PASSWORD $DB_NAME < "$REPO/script/database/database-init.sql"
mysql -u$DB_USER -p$DB_PASSWORD $DB_NAME < "$REPO/module/Payroll/script/payroll-2-db-database.sql"
mysql -u$DB_USER -p$DB_PASSWORD $DB_NAME < "$REPO/module/Payroll/script/pr-database-init.sql"
mysql -u$DB_USER -p$DB_PASSWORD $DB_NAME < "$REPO/module/Payroll/script/payroll-init-data-schedule_item.sql"
mysql -u$DB_USER -p$DB_PASSWORD $DB_NAME < "$REPO/module/Payroll/script/payroll-init-data-processing_schedule.sql"
mysql -u$DB_USER -p$DB_PASSWORD $DB_NAME < "$REPO/module/Payroll/script/pr-country-state-postcode-database-init.sql"
