DROP TABLE IF EXISTS `#__claw_events_current`;
CREATE TABLE `#__claw_events_current` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `type` enum('string','number','stringarray','numberarray','bool') NOT NULL DEFAULT 'string',
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `#__claw_events_current`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `#__claw_events_current`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

DROP TABLE IF EXISTS `#__claw_configuration`;
CREATE TABLE `#__claw_configuration` (
  `id` int(11) NOT NULL,
  `key` varchar(50) NOT NULL,
  `input` text NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `#__claw_configuration`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

ALTER TABLE `#__claw_configuration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

DROP TABLE IF EXISTS `#__claw_locations`;
CREATE TABLE `#__claw_locations`(
  `id` int(11) NOT NULL,
  `ordering` int(11) DEFAULT NULL,
  `catid` int(11) DEFAULT 0,
  `published` TINYINT(4) NOT NULL DEFAULT '1',
  `value` varchar(255) NOT NULL,
  `alias` varchar(255) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `#__claw_locations`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__claw_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

CREATE UNIQUE INDEX `aliasindex` ON `#__claw_locations` (`alias`, `catid`);

DROP TABLE IF EXISTS `#__claw_sponsors`;
CREATE TABLE `#__claw_sponsors`(
    `id` INT(11) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `link` VARCHAR(255) NOT NULL,
    `type` TINYINT NOT NULL,
    `logo_small` VARCHAR(255) NULL,
    `logo_large` VARCHAR(255) NULL,
    `published` TINYINT(4) NOT NULL DEFAULT '1',
    `ordering` INT(11) NULL DEFAULT NULL,
    `expires` DATE DEFAULT '0000-00-00 00:00:00',
    `mtime` DATE NOT NULL DEFAULT CURRENT_TIMESTAMP()
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `#__claw_sponsors`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__claw_sponsors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

DROP TABLE IF EXISTS `#__claw_shifts`;
CREATE TABLE `#__claw_shifts`(
    `id` INT(11) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `location` INT(11) NOT NULL,
    `requirements` VARCHAR(255) NOT NULL,
    `coordinators` text NULL,
    `published` TINYINT(4) NOT NULL DEFAULT '1',
    `mtime` DATE NOT NULL DEFAULT CURRENT_TIMESTAMP()
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__claw_shifts_grids`;
CREATE TABLE `#__claw_shifts_grids`(
    `id` INT(11) NOT NULL,
    `shift_id` INT(11) NOT NULL,
    `row_id` INT(11) NOT NULL,
    `date` DATE NOT NULL,
    `length` TINYINT NOT NULL DEFAULT '4',
    `primary` TINYINT NOT NULL DEFAULT '0',
    `secondary` TINYINT NOT NULL DEFAULT '0',
    `eventid` INT(11) NOT NULL DEFAULT '0',
    `mtime` DATE NOT NULL DEFAULT CURRENT_TIMESTAMP()
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `#__claw_shifts`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__claw_shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `#__claw_shifts_grids`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_shift_id` (`shift_id`),
  ADD KEY `idx_row_id` (`row_id`);

ALTER TABLE `#__claw_shifts_grids`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE `#__claw_schedule` (
  `id` int(11) NOT NULL,
  `published` TINYINT(4) DEFAULT NULL,
  `day` TEXT DEFAULT NULL,
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
  `sort_order` varchar(255) DEFAULT NULL,
  `mtime` datetime DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `#__claw_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fb_groupby_day_INDEX` (`day`(10)),
  ADD KEY `fb_groupbyorder_sort_order_INDEX` (`sort_order`(10));

ALTER TABLE `#__claw_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE `#__claw_presenters` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `published` TINYINT(4) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `legal_name` varchar(255) DEFAULT NULL,
  `event` varchar(10) DEFAULT NULL,
  `social_media` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `phone_info` varchar(10) DEFAULT NULL,
  `arrival` varchar(255) DEFAULT NULL,
  `copresenter` boolean DEFAULT 0,
  `copresenting` varchar(255) DEFAULT NULL,
  `comments` TEXT DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `photo` TEXT DEFAULT NULL,
  `submission_date` date DEFAULT NULL,
  `mtime` datetime DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `#__claw_presenters`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__claw_presenters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE `#__claw_skills` (
  `id` int(11) NOT NULL,
  `published` TINYINT(4) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `event` TEXT DEFAULT NULL,
  `day` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `length` int(4) DEFAULT 60,
  `presenters` TEXT DEFAULT NULL,
  `track` varchar(10) DEFAULT NULL,
  `audience` varchar(10) DEFAULT NULL,
  `category` varchar(10) DEFAULT NULL,
  `location` INT(11) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `photo` TEXT DEFAULT NULL,
  `handout_id` INT(11) DEFAULT NULL,
  `copresenter` varchar(255) DEFAULT NULL,
  `comments` TEXT DEFAULT NULL,
  `submission_date` date DEFAULT NULL,
  `mtime` datetime DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `#__claw_skills`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__claw_skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE `#__claw_skills_handouts` (
  `id` int(11) NOT NULL,
  `name` text  DEFAULT NULL,
  `alias` VARCHAR(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `filename` TEXT DEFAULT NULL,
  `skill_id` INT(11) DEFAULT NULL,
  `mtime` datetime DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `#__claw_skills_handouts`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__claw_skills_handouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
