<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Enums;

enum ConfigFieldNames: int
{
  case CONFIG_DEBUG_EMAIL = 2;
  case CONFIG_IMAGES = 3;
  case CONFIG_OVERLAP_CATEGORY = 4;
  case CONFIG_URLPREFIX = 6;
  case SKILL_CATEGORY = 8;
  case SKILL_CLASS_TYPE = 9;
  case SKILL_TIME_SLOT = 10;
  case SKILL_TRACK = 11;

  public function toString(): string
  {
    return match ($this) {
      ConfigFieldNames::CONFIG_DEBUG_EMAIL => 'config_debug_email',
      ConfigFieldNames::CONFIG_IMAGES => 'config_images',
      ConfigFieldNames::CONFIG_OVERLAP_CATEGORY => 'config_overlap_category',
      ConfigFieldNames::CONFIG_URLPREFIX => 'config_urlprefix',
      ConfigFieldNames::SKILL_CATEGORY => 'skill_category',
      ConfigFieldNames::SKILL_CLASS_TYPE => 'skill_class_type',
      ConfigFieldNames::SKILL_TIME_SLOT => 'skill_time_slot',
      ConfigFieldNames::SKILL_TRACK => 'skill_track',
    };
  }

  public static function fromName(string $str): ?ConfigFieldNames
  {
    $str = strtoupper($str);
    foreach (self::cases() as $case) {
      if ($str === strtoupper($case->name)) {
        return $case;
      }
    }

    return null;
  }

  public static function toOptions(): array
  {
    $result = [];

    foreach (ConfigFieldNames::cases() as $c) {
      $result[$c->value] = $c->toString();
    }

    return $result;
  }
}
