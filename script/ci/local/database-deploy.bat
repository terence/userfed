mysql -uroot -e "drop database trexanhlab_pg2"
mysql -uroot -e "create database trexanhlab_pg2 CHARACTER SET utf8 COLLATE utf8_unicode_ci;"
mysql -u root trexanhlab_pg2 < ../../database/database.sql
mysql -u root trexanhlab_pg2 < ../../database/database-init.sql
mysql -u root trexanhlab_pg2 < ../../../code/script/payroll-doctrine-ready.sql
mysql -u root trexanhlab_pg2 < ../../../code/script/payroll-init-data.sql
mysql -u root trexanhlab_pg2 < ../../../code/script/payroll-init-data-schedule_item.sql
mysql -u root trexanhlab_pg2 < ../../../code/script/payroll-init-data-processing_schedule.sql
mysql -u root trexanhlab_pg2 < ../../../code/script/payroll-init-data-rate-category.sql
