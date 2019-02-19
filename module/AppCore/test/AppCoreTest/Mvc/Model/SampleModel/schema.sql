CREATE TABLE IF NOT EXISTS `model1table` (
	`model1_id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NULL,
	`is_deleted` TINYINT(1) NULL DEFAULT 0 COMMENT '0 is false, 1 is true',
	`last_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`creation_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`model1_id`))
ENGINE = InnoDB;
				
CREATE TABLE IF NOT EXISTS `model2table` (
	`model2_id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NULL,
	`is_deleted` TINYINT(1) NULL DEFAULT 0 COMMENT '0 is false, 1 is true',
	`last_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`creation_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`model2_id`))
ENGINE = InnoDB;
				
CREATE TABLE IF NOT EXISTS `model12table` (
	`model12_id` INT NOT NULL AUTO_INCREMENT,
	`model1_id` INT NOT NULL,
	`model2_id` INT NOT NULL,
	`is_deleted` TINYINT(1) NULL DEFAULT 0 COMMENT '0 is false, 1 is true',
	`last_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`creation_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',				
  PRIMARY KEY (`model12_id`))
ENGINE = InnoDB;