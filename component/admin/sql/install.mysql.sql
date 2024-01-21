CREATE TABLE IF NOT EXISTS `#__claw_events_current` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `type` enum('string','number','stringarray','numberarray','bool') NOT NULL DEFAULT 'string',
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`);
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__claw_configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(50) NOT NULL,
  `input` text NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_key` (`key`);
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__claw_locations`(
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ordering` int(11) DEFAULT NULL,
  `catid` int(11) DEFAULT 0,
  `published` TINYINT(4) NOT NULL DEFAULT '1',
  `value` varchar(255) NOT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `mtime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

# CREATE UNIQUE INDEX `aliasindex` ON `#__claw_locations` (`alias`, `catid`);

CREATE TABLE IF NOT EXISTS `#__claw_sponsors`(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `link` VARCHAR(255) NOT NULL,
  `type` TINYINT NOT NULL,
  `description` TEXT DEFAULT NULL,
  `logo_small` VARCHAR(255) NULL,
  `logo_large` VARCHAR(255) NULL,
  `published` TINYINT(4) NOT NULL DEFAULT '1',
  `ordering` INT(11) NULL DEFAULT NULL,
  `expires` DATE DEFAULT '0000-00-00',
  `mtime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__claw_vendors`(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `published` TINYINT(4) NOT NULL DEFAULT '1',
  `event` TEXT DEFAULT NULL,
  `name` VARCHAR(255) NOT NULL,
  `spaces` TINYINT(4) NOT NULL DEFAULT '1',
  `link` VARCHAR(255) NOT NULL DEFAULT '',
  `description` TEXT NOT NULL,
  `logo` TEXT NOT NULL,
  `location` INT(11) DEFAULT NULL,
  `ordering` INT(11) NULL DEFAULT NULL,
  `mtime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__claw_shifts`(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `event` TEXT DEFAULT NULL,
  `shift_area` TEXT DEFAULT NULL,
  `grid` TEXT DEFAULT NULL,
  `requirements` VARCHAR(255) NOT NULL,
  `coordinators` text NULL,
  `published` TINYINT(4) NOT NULL DEFAULT '1',
  `mtime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__claw_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `published` TINYINT(4) DEFAULT NULL,
  `event` varchar(10) DEFAULT NULL,
  `day` DATE DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `featured` boolean NOT NULL DEFAULT 0,
  `event_title` varchar(255) DEFAULT NULL,
  `fee_event` TEXT DEFAULT NULL,
  `event_description` TEXT DEFAULT NULL,
  `onsite_description` TEXT DEFAULT NULL,
  `location` int(11) DEFAULT NULL,
  `sponsors` TEXT DEFAULT NULL,
  `poster` TEXT DEFAULT NULL,
  `photo_size` varchar(255) DEFAULT NULL,
  `event_id` int(4) DEFAULT NULL,
  `mtime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  KEY `fb_groupby_day_INDEX` (`day`),
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__claw_presenters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `published` TINYINT(4) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `legal_name` varchar(255) DEFAULT NULL,
  `event` varchar(10) DEFAULT NULL,
  `social_media` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `phone_info` varchar(255) DEFAULT NULL,
  `arrival` varchar(255) DEFAULT NULL,
  `copresenter` boolean DEFAULT 0,
  `copresenting` varchar(255) DEFAULT NULL,
  `comments` TEXT DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `photo` TEXT DEFAULT NULL,
  `submission_date` date DEFAULT NULL,
  `archive_state` VARCHAR(255) DEFAULT NULL,
  `mtime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__claw_skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `published` TINYINT(4) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `event` TEXT DEFAULT NULL,
  `day` date DEFAULT NULL,
  `time_slot` varchar(8) DEFAULT NULL,
  `type` varchar(8) DEFAULT NULL,
  `owner` INT(11) DEFAULT NULL,
  `presenters` TEXT DEFAULT NULL,
  `track` varchar(10) DEFAULT NULL,
  `audience` varchar(10) DEFAULT NULL,
  `category` varchar(15) DEFAULT NULL,
  `location` INT(11) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `photo` TEXT DEFAULT NULL,
  `handout_id` INT(11) DEFAULT NULL,
  `copresenter_info` varchar(255) DEFAULT NULL,
  `equipment_info` varchar(255) DEFAULT NULL,
  `requirements_info` varchar(255) DEFAULT NULL,
  `length_info` int(4) DEFAULT 60,
  `av` int(4) DEFAULT 0,
  `comments` TEXT DEFAULT NULL,
  `submission_date` date DEFAULT NULL,
  `archive_state` VARCHAR(255) DEFAULT NULL,
  `mtime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__claw_skills_handouts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text  DEFAULT NULL,
  `alias` VARCHAR(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `filename` TEXT DEFAULT NULL,
  `skill_id` INT(11) DEFAULT NULL,
  `mtime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__claw_profile_charge_log`(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `description` VARCHAR(255) NOT NULL,
  `eventbooking_event_id` INT(11) NOT NULL,
  `fname` VARCHAR(255) NOT NULL,
  `lname` VARCHAR(255) NOT NULL,
  `invoice_id` VARCHAR(255) NOT NULL,
  `profile_id` VARCHAR(255) NOT NULL,
  `payment_profile_id` VARCHAR(255) NOT NULL,
  `charge_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `charge_amount` DECIMAL(10,2) DEFAULT 0.0,
  `transaction_id` VARCHAR(50) NOT NULL
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__claw_jwt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `iat` int(11) DEFAULT NULL,
  `exp` int(11) DEFAULT NULL,
  `nonce` varchar(255) NOT NULL,
  `state` enum('init','new','expired','issued','revoked') NOT NULL DEFAULT 'new',
  `secret` varchar(255) NOT NULL,
  `email` varchar(64) NOT NULL,
  `subject` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__claw_field_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fieldname` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `text` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__claw_eventid_mapping` (
  `eventid` int(11),
  `alias` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`eventid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__claw_eventinfos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alias` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `ebLocationId` int(11) DEFAULT 0,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `prefix` varchar(255) DEFAULT NULL,
  `cancelBy` datetime DEFAULT NULL,
  `timezone` varchar(255) DEFAULT NULL,
  `active` boolean DEFAULT 0,
  `eventType` TINYINT(4) DEFAULT NULL,
  `onsiteActive` boolean DEFAULT 0,
  `termsArticleId` int(11) DEFAULT NULL,
  `eb_cat_shifts` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]' COMMENT 'JSON Shifts' CHECK (json_valid(`eb_cat_shifts`)),
  `eb_cat_supershifts` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]' COMMENT 'JSON Super Shifts' CHECK (json_valid(`eb_cat_supershifts`)),
  `eb_cat_speeddating` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]' COMMENT 'JSON Speed Dating' CHECK (json_valid(`eb_cat_speeddating`)),
  `eb_cat_equipment` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]' COMMENT 'JSON Equipment' CHECK (json_valid(`eb_cat_equipment`)),
  `eb_cat_sponsorship` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]' COMMENT 'JSON Sponsorships' CHECK (json_valid(`eb_cat_sponsorship`)),
  `eb_cat_meals` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]' COMMENT 'JSON Meals' CHECK (json_valid(`eb_cat_meals`)),
  `mtime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  KEY `fb_alias_INDEX` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__claw_packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eventAlias` varchar(255) DEFAULT NULL,
  `published` TINYINT(4) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `description` TEXT NOT NULL DEFAULT '',
  `alias` VARCHAR(255) NOT NULL DEFAULT '',
  `eventPackageType` SMALLINT(5) NOT NULL DEFAULT 0,
  `packageInfoType` TINYINT(4) NOT NULL DEFAULT 0,
  `couponKey` VARCHAR(10) NOT NULL DEFAULT '',
  `couponValue` FLOAT NOT NULL DEFAULT 0.0,
  `fee` FLOAT NOT NULL DEFAULT 0.0,
  `eventId` INT NOT NULL DEFAULT 0,
  `category` INT NOT NULL DEFAULT 0,
  `minShifts` INT NOT NULL DEFAULT 0,
  `requiresCoupon` BOOLEAN NOT NULL DEFAULT false,
  `couponAccessGroups` JSON NOT NULL DEFAULT '[]',
  `authNetProfile` BOOLEAN NOT NULL DEFAULT false,
  `start` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `isVolunteer` BOOLEAN NOT NULL DEFAULT false,
  `bundleDiscount` INT NOT NULL DEFAULT 0,
  `badgeValue` VARCHAR(255) NOT NULL DEFAULT '',
  `couponOnly` BOOLEAN NOT NULL DEFAULT false,
  `meta` JSON NOT NULL DEFAULT '[]',
  `mtime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  KEY `fb_eventalias_INDEX` (`eventAlias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;