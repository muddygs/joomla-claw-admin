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
