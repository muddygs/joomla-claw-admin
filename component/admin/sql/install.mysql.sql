CREATE TABLE IF NOT EXISTS `#__claw_locations`(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `event` TEXT DEFAULT NULL,
  `published` TINYINT(4) NOT NULL DEFAULT '1',
  `value` VARCHAR(255) NOT NULL,
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
  `coordinators` TEXT NULL,
  `published` TINYINT(4) NOT NULL DEFAULT '1',
  `mtime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__claw_schedule` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `published` TINYINT(4) DEFAULT NULL,
  `event` VARCHAR(10) DEFAULT NULL,
  `day` DATE DEFAULT NULL,
  `start_time` TIME DEFAULT NULL,
  `end_time` TIME DEFAULT NULL,
  `featured` boolean NOT NULL DEFAULT 0,
  `event_title` VARCHAR(255) DEFAULT NULL,
  `fee_event` TEXT DEFAULT NULL,
  `event_description` TEXT DEFAULT NULL,
  `onsite_description` TEXT DEFAULT NULL,
  `location` INT(11) DEFAULT NULL,
  `sponsors` TEXT DEFAULT NULL,
  `poster` TEXT DEFAULT NULL,
  `photo_size` VARCHAR(255) DEFAULT NULL,
  `event_id` INT(4) DEFAULT NULL,
  `mtime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  KEY `fb_groupby_day_INDEX` (`day`),
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__claw_presenters` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `uid` INT(11) NOT NULL,
  `published` TINYINT(4) DEFAULT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `legal_name` VARCHAR(255) DEFAULT NULL,
  `event` VARCHAR(10) DEFAULT NULL,
  `social_media` VARCHAR(255) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `phone_info` VARCHAR(255) DEFAULT NULL,
  `arrival` VARCHAR(255) DEFAULT NULL,
  `copresenter` boolean DEFAULT 0,
  `copresenting` VARCHAR(255) DEFAULT NULL,
  `comments` TEXT DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `image` MEDIUMBLOB DEFAULT NULL,
  `image_preview` MEDIUMBLOB DEFAULT NULL,
  `submission_date` DATE DEFAULT NULL,
  `archive_state` VARCHAR(255) DEFAULT NULL,
  `mtime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__claw_skills` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `published` TINYINT(4) DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `event` TEXT DEFAULT NULL,
  `day` DATE DEFAULT NULL,
  `time_slot` VARCHAR(8) DEFAULT NULL,
  `type` VARCHAR(8) DEFAULT NULL,
  `owner` INT(11) DEFAULT NULL,
  `presenters` TEXT DEFAULT NULL,
  `track` VARCHAR(10) DEFAULT NULL,
  `audience` VARCHAR(10) DEFAULT NULL,
  `category` VARCHAR(15) DEFAULT NULL,
  `location` INT(11) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `photo` TEXT DEFAULT NULL,
  `handout_id` INT(11) DEFAULT NULL,
  `copresenter_info` VARCHAR(255) DEFAULT NULL,
  `equipment_info` VARCHAR(255) DEFAULT NULL,
  `requirements_info` VARCHAR(255) DEFAULT NULL,
  `length_info` INT(4) DEFAULT 60,
  `av` INT(4) DEFAULT 0,
  `comments` TEXT DEFAULT NULL,
  `submission_date` DATE DEFAULT NULL,
  `archive_state` VARCHAR(255) DEFAULT NULL,
  `mtime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__claw_skills_handouts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` TEXT  DEFAULT NULL,
  `alias` VARCHAR(255) DEFAULT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `filename` TEXT DEFAULT NULL,
  `skill_id` INT(11) DEFAULT NULL,
  `mtime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
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
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `iat` INT(11) DEFAULT NULL,
  `exp` INT(11) DEFAULT NULL,
  `nonce` VARCHAR(255) NOT NULL,
  `state` enum('init','new','expired','issued','revoked') NOT NULL DEFAULT 'new',
  `secret` VARCHAR(255) NOT NULL,
  `email` VARCHAR(64) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__claw_field_values` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `fieldname` VARCHAR(255) DEFAULT NULL,
  `value` VARCHAR(255) DEFAULT NULL,
  `text` VARCHAR(255) DEFAULT NULL,
  `event` VARCHAR(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_event` (`event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__claw_eventid_mapping` (
  `eventid` INT(11),
  `alias` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`eventid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__claw_eventinfos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `alias` VARCHAR(255) DEFAULT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `ebLocationId` INT(11) DEFAULT 0,
  `start_date` DATETIME DEFAULT NULL,
  `end_date` DATETIME DEFAULT NULL,
  `prefix` VARCHAR(255) DEFAULT NULL,
  `cancelBy` DATETIME DEFAULT NULL,
  `timezone` VARCHAR(255) DEFAULT NULL,
  `active` boolean DEFAULT 0,
  `eventType` TINYINT(4) DEFAULT NULL,
  `onsiteActive` boolean DEFAULT 0,
  `termsArticleId` INT(11) DEFAULT NULL,
  `eb_cat_shifts` longTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]' COMMENT 'JSON Shifts' CHECK (json_valid(`eb_cat_shifts`)),
  `eb_cat_supershifts` longTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]' COMMENT 'JSON Super Shifts' CHECK (json_valid(`eb_cat_supershifts`)),
  `eb_cat_speeddating` longTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]' COMMENT 'JSON Speed Dating' CHECK (json_valid(`eb_cat_speeddating`)),
  `eb_cat_equipment` longTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]' COMMENT 'JSON Equipment' CHECK (json_valid(`eb_cat_equipment`)),
  `eb_cat_sponsorship` longTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]' COMMENT 'JSON Sponsorship' CHECK (json_valid(`eb_cat_sponsorship`)),
  `eb_cat_sponsorships` longTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]' COMMENT 'JSON Sponsorships' CHECK (json_valid(`eb_cat_sponsorships`)),
  `eb_cat_dinners` INT(11) DEFAULT NULL,  
  `eb_cat_brunches` INT(11) DEFAULT NULL,
  `eb_cat_buffets`  INT(11) DEFAULT NULL,
  `eb_cat_combomeals` INT(11) DEFAULT NULL,
  `eb_cat_invoicables` longTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]' COMMENT 'JSON Invoice Categories' CHECK (json_valid(`eb_cat_invoicables`)),
  `mtime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  KEY `fb_alias_INDEX` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__claw_packages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `eventAlias` VARCHAR(255) DEFAULT NULL,
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
