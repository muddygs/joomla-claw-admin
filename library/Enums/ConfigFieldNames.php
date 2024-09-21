<?php

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
  case SPA_SERVICES = 12;

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
      ConfigFieldNames::SPA_SERVICES => 'spa_services',
    };
  }

  public static function fromString(string $str): ?ConfigFieldNames
  {
    return match (strtoupper($str)) {
      'CONFIG_DEBUG_EMAIL' => self::CONFIG_DEBUG_EMAIL,
      'CONFIG_IMAGES' => self::CONFIG_IMAGES,
      'CONFIG_OVERLAP_CATEGORY' => self::CONFIG_OVERLAP_CATEGORY,
      'CONFIG_URLPREFIX' => self::CONFIG_URLPREFIX,
      'SKILL_CATEGORY' => self::SKILL_CATEGORY,
      'SKILL_CLASS_TYPE' => self::SKILL_CLASS_TYPE,
      'SKILL_TIME_SLOT' => self::SKILL_TIME_SLOT,
      'SKILL_TRACK' => self::SKILL_TRACK,
      'SPA_SERVICES' => self::SPA_SERVICES,
      default => null,
    };
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
