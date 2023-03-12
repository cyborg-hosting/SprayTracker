CREATE TABLE `sprays` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`steamid` VARCHAR(32) NOT NULL,
	`ip` VARCHAR(16) NOT NULL,
	`port` SMALLINT(32) UNSIGNED NOT NULL,
	`name` VARCHAR(96) NOT NULL,
	`filename` VARCHAR(12) NOT NULL,
	`firstdate` DATETIME NOT NULL,
	`date` DATETIME NOT NULL,
	`count` INT(10) UNSIGNED NOT NULL,
	`banned` TINYINT(1) NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	UNIQUE INDEX `filename` (`filename`),
	INDEX `steamid` (`steamid`),
	INDEX `firstdate` (`firstdate`),
	INDEX `date` (`date`),
	INDEX `count` (`count`)
)
ENGINE=InnoDB
ROW_FORMAT=DEFAULT