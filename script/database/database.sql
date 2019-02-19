SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';


-- -----------------------------------------------------
-- Table `user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `user` (
  `user_id` INT NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NULL,
  `firstname` VARCHAR(255) NULL,
  `lastname` VARCHAR(25) NULL,
  `creation_date` DATETIME NOT NULL,
  `last_updated` DATETIME NULL,
  `is_enabled` TINYINT NULL DEFAULT 1,
  `is_deleted` TINYINT(1) NULL DEFAULT 0 COMMENT '0 is false, 1 is true',
  PRIMARY KEY (`user_id`),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `email_template`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `email_template` (
  `email_template_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `code` VARCHAR(255) NOT NULL,
  `body` TEXT NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`email_template_id`),
  UNIQUE INDEX `code_UNIQUE` (`code` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `system_setting`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `system_setting` (
  `setting_id` INT(11) NOT NULL AUTO_INCREMENT,
  `value` TEXT NOT NULL,
  `key` TEXT NOT NULL,
  `type` VARCHAR(255) NOT NULL COMMENT 'Boolean, Text, Int, Float',
  PRIMARY KEY (`setting_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `permission_role`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `permission_role` (
  `role_id` VARCHAR(255) NOT NULL COMMENT 'admin,member,guest',
  `description` VARCHAR(255) NULL,
  `creation_date` DATETIME NULL,
  `last_updated` DATETIME NULL,
  PRIMARY KEY (`role_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `permission_resource`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `permission_resource` (
  `resource_id` VARCHAR(255) NOT NULL,
  `parent` VARCHAR(255) NULL,
  `description` VARCHAR(255) NULL,
  `sort_order` INT NULL,
  `hide_in_permission_editor` TINYINT(1) NULL DEFAULT 1 COMMENT 'true then it will not show in editor.Example: Ajax action, Web services',
  PRIMARY KEY (`resource_id`),
  INDEX `fk_permission_resource_permission_resource1_idx` (`parent` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `permission_acl`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `permission_acl` (
  `acl_id` INT NOT NULL AUTO_INCREMENT,
  `resource_id` VARCHAR(255) NOT NULL,
  `access` TINYINT NOT NULL,
  `priviledges` TEXT NULL,
  `assertion_class` TEXT NULL,
  `role_id` VARCHAR(255) NULL,
  `creation_date` DATETIME NULL,
  `last_updated` DATETIME NULL,
  `sort_order` INT NULL,
  PRIMARY KEY (`acl_id`),
  INDEX `fk_permission_acl_permission_resource1_idx` (`resource_id` ASC),
  INDEX `fk_permission_acl_permission_role1_idx` (`role_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `permission_user_role`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `permission_user_role` (
  `user_id` INT NOT NULL,
  `role_id` VARCHAR(255) NOT NULL,
  `creation_date` DATETIME NULL,
  `last_updated` DATETIME NULL,
  `is_deleted` TINYINT(1) NULL DEFAULT 0 COMMENT '0 is false, 1 is true',
  INDEX `fk_permission_user_role_permission_role1_idx` (`role_id` ASC),
  INDEX `fk_permission_user_role_user1_idx` (`user_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `authentication_account`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `authentication_account` (
  `authentication_account_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `type` VARCHAR(255) NOT NULL,
  `creation_date` DATETIME NOT NULL,
  `last_updated` DATETIME NULL,
  `is_deleted` TINYINT(1) NULL DEFAULT 0 COMMENT '0 is false, 1 is true',
  PRIMARY KEY (`authentication_account_id`),
  INDEX `fk_authentication_account_user1_idx` (`user_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `authentication_oauth`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `authentication_oauth` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `provider` VARCHAR(45) NULL,
  `provider_user_id` VARCHAR(255) NULL,
  `authentication_account_id` INT NULL,
  `creation_date` DATETIME NOT NULL,
  `last_updated` DATETIME NULL,
  `is_deleted` TINYINT(1) NULL DEFAULT 0 COMMENT '0 is false, 1 is true',
  PRIMARY KEY (`id`),
  INDEX `fk_authentication_oauth_authentication_account1_idx` (`authentication_account_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `organisation`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `uf_organisation` (
  `organisation_id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `domain` VARCHAR(255) NOT NULL DEFAULT 'eg. orgname.application.com',
  `creation_date` DATETIME NOT NULL,
  `last_updated` DATETIME NULL,
  `is_deleted` TINYINT(1) NULL DEFAULT 0 COMMENT '0 is false, 1 is true',
  PRIMARY KEY (`organisation_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `application`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `application` (
  `application_id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `creation_date` DATETIME NOT NULL,
  `last_updated` DATETIME NULL,
  `is_deleted` TINYINT(1) NULL DEFAULT 0 COMMENT '0 -> false, 1 -> true',
  PRIMARY KEY (`application_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `server`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `server` (
  `server_id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `ip` VARCHAR(255) NOT NULL COMMENT 'Server IP address',
  `location` VARCHAR(255) NOT NULL COMMENT 'Physical location of server',
  `status` TINYINT NOT NULL COMMENT 'State of server\n1 - Active (Production)\n0 - Decommissioned\n2 - Active (Test)',
  `creation_date` DATETIME NOT NULL,
  `last_updated` DATETIME NULL,
  `domain` VARCHAR(255) NOT NULL COMMENT 'domain of server, will replace IP during testing',
  `secret` VARCHAR(255) NOT NULL COMMENT 'Each server will have a secret to identify itself',
  `is_deleted` TINYINT(1) NULL DEFAULT 0 COMMENT '0 is false, 1 is true',
  PRIMARY KEY (`server_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `application_server`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `application_server` (
  `application_server_id` INT NOT NULL AUTO_INCREMENT,
  `application_id` INT NOT NULL,
  `server_id` INT NOT NULL,
  `creation_date` DATETIME NOT NULL,
  `last_updated` DATETIME NULL,
  `is_deleted` TINYINT(1) NULL DEFAULT 0 COMMENT '0 false, 1 true',
  INDEX `fk_application_server_application1_idx` (`application_id` ASC),
  INDEX `fk_application_server_server1_idx` (`server_id` ASC),
  PRIMARY KEY (`application_server_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `user_application`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_application` (
  `user_application_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `invitation_token` TEXT NULL COMMENT 'length = 1024\nInvitation token used to authenticate a users\' invitation to an application-­shard.',
  `invitation_token_creation` DATETIME NULL COMMENT 'Invitation token timestamp',
  `default` TINYINT NOT NULL COMMENT '1: yes\n0: no\n',
  `destination_url` TEXT NULL,
  `creation_date` DATETIME NOT NULL,
  `last_updated` DATETIME NULL,
  `organisation_id` INT NULL,
  `server_id` INT NULL,
  `application_id` INT NULL,
  `is_deleted` TINYINT(1) NULL DEFAULT 0 COMMENT '0 is false, 1 is true',
  INDEX `fk_user_application_user1_idx` (`user_id` ASC),
  PRIMARY KEY (`user_application_id`),
  INDEX `fk_user_application_organisation1_idx` (`organisation_id` ASC),
  INDEX `fk_user_application_server1_idx` (`server_id` ASC),
  INDEX `fk_user_application_application1_idx` (`application_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `token`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `token` (
  `token_id` INT NOT NULL AUTO_INCREMENT,
  `token` TEXT NOT NULL COMMENT 'length : 1024',
  `token_registration_date` DATETIME NOT NULL COMMENT 'Token timestamp',
  `creation_date` DATETIME NOT NULL,
  `last_updated` DATETIME NULL,
  PRIMARY KEY (`token_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `web_session`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `web_session` (
  `id` CHAR(32) NOT NULL,
  `name` CHAR(32) NOT NULL,
  `modified` INT NULL,
  `lifetime` INT NULL,
  `data` TEXT NULL,
  PRIMARY KEY (`id`, `name`))
ENGINE = InnoDB
COMMENT = 'for store web session (manage by php/zend2)';


-- -----------------------------------------------------
-- Table `token_web_session`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `token_web_session` (
  `session_id` CHAR(32) NOT NULL,
  `token_id` INT NOT NULL)
ENGINE = InnoDB
COMMENT = 'link web session and token, so we can delete it later';


-- -----------------------------------------------------
-- Table `user_log`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_log` (
  `log_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NULL,
  `ip_address` VARCHAR(255) NULL,
  `timestamp` INT NULL,
  `type` VARCHAR(255) NULL,
  `log_type` TINYINT(1) NULL DEFAULT 1,
  `message` VARCHAR(255) NULL,
  `creation_date` DATETIME NOT NULL,
  `last_updated` DATETIME NULL,
  `is_deleted` TINYINT(1) NULL DEFAULT 0 COMMENT '0 is false, 1 is true',
  PRIMARY KEY (`log_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `invite`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `invite` (
  `invite_id` INT NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NULL,
  `code` VARCHAR(255) NULL,
  `invited_date` DATETIME NULL,
  `creation_date` DATETIME NOT NULL,
  `last_updated` DATETIME NULL,
  PRIMARY KEY (`invite_id`),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC),
  UNIQUE INDEX `code_UNIQUE` (`code` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `application_invite`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `application_invite` (
  `application_invite_id` INT NOT NULL AUTO_INCREMENT,
  `invite_id` INT NOT NULL,
  `application_id` INT NOT NULL,
  `server_id` INT NOT NULL,
  `creation_date` DATETIME NULL,
  `last_updated` DATETIME NULL,
  `is_deleted` TINYINT(1) NULL DEFAULT 0 COMMENT '0 -> false, 1 -> true',
  INDEX `fk_application_invite_invite1_idx` (`invite_id` ASC),
  PRIMARY KEY (`application_invite_id`),
  INDEX `fk_application_invite_application1_idx` (`application_id` ASC),
  INDEX `fk_application_invite_server1_idx` (`server_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `authentication_internal`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `authentication_internal` (
  `authentication_internal_id` INT NOT NULL AUTO_INCREMENT,
  `authentication_account_id` INT NULL,
  `username` VARCHAR(255) NOT NULL COMMENT 'email',
  `password` VARCHAR(255) NOT NULL,
  `change_password_key` VARCHAR(255) NULL,
  `activation_key` VARCHAR(255) NULL,
  `creation_date` DATETIME NOT NULL,
  `last_updated` DATETIME NULL,
  `is_activated` TINYINT(1) NULL,
  `is_deleted` TINYINT(1) NULL DEFAULT 0 COMMENT '0 is false, 1 is true',
  INDEX `fk_authentication_internal_authentication_account1_idx` (`authentication_account_id` ASC),
  PRIMARY KEY (`authentication_internal_id`),
  UNIQUE INDEX `change_password_key_UNIQUE` (`change_password_key` ASC),
  UNIQUE INDEX `activation_key_UNIQUE` (`activation_key` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `application_organisation`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `application_organisation` (
  `application_organisation_id` INT NOT NULL AUTO_INCREMENT,
  `organisation_id` INT NOT NULL,
  `application_id` INT NOT NULL,
  `creation_date` DATETIME NOT NULL,
  `last_updated` DATETIME NULL,
  `is_deleted` TINYINT(1) NULL DEFAULT 0 COMMENT '0 -> false, 1 -> true',
  PRIMARY KEY (`application_organisation_id`),
  INDEX `fk_application_organisation_organisation1_idx` (`organisation_id` ASC),
  INDEX `fk_application_organisation_application1_idx` (`application_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `user_organisation`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_organisation` (
  `user_organisation_id` INT NOT NULL AUTO_INCREMENT,
  `organisation_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `is_deleted` TINYINT(1) NULL DEFAULT 0 COMMENT '0 is false, 1 is true',
  `creation_date` DATETIME NOT NULL,
  `last_updated` DATETIME NULL,
  PRIMARY KEY (`user_organisation_id`),
  INDEX `fk_user_organisation_organisation1_idx` (`organisation_id` ASC),
  INDEX `fk_user_organisation_user1_idx` (`user_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `application_server_organisation`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `application_server_organisation` (
  `application_server_organisation_id` INT NOT NULL AUTO_INCREMENT,
  `application_id` INT NOT NULL,
  `organisation_id` INT NOT NULL,
  `server_id` INT NOT NULL,
  `creation_date` DATETIME NOT NULL,
  `last_updated` DATETIME NULL,
  `is_deleted` TINYINT(1) NULL DEFAULT 0 COMMENT '0 -> false, 1 -> true',
  INDEX `fk_application_server_organization_application1_idx` (`application_id` ASC),
  INDEX `fk_application_server_organization_organisation1_idx` (`organisation_id` ASC),
  INDEX `fk_application_server_organization_server1_idx` (`server_id` ASC),
  PRIMARY KEY (`application_server_organisation_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `user_application_token`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_application_token` (
  `user_application_id` INT NOT NULL,
  `token_id` INT NOT NULL,
  INDEX `fk_user_application_token_user_application1_idx` (`user_application_id` ASC),
  INDEX `fk_user_application_token_token1_idx` (`token_id` ASC),
  UNIQUE INDEX `user_application_id_UNIQUE` (`user_application_id` ASC),
  UNIQUE INDEX `token_id_UNIQUE` (`token_id` ASC))
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
