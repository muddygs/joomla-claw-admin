<?php
namespace ClawCorpLib\Enums;

enum ConfigFieldNames: int
{
  case CONFIG_COMBO_EVENTS = 1;
  case CONFIG_DEBUG_EMAIL = 2;
  case CONFIG_IMAGES = 3;
  case CONFIG_OVERLAP_CATEGORY = 4;
  case CONFIG_TIMEZONE = 5;
  case CONFIG_URLPREFIX = 6;
  case SHIFT_SHIFT_AREA = 7;
  case SKILL_CATEGORY = 8;
  case SKILL_CLASS_TYPE = 9;
  case SKILL_TIME_SLOT = 10;
  case SKILL_TRACK = 11;

  public function toString(): string
  {
    return match ($this) {
      ConfigFieldNames::CONFIG_COMBO_EVENTS => 'config_combo_events',
      ConfigFieldNames::CONFIG_DEBUG_EMAIL => 'config_debug_email',
      ConfigFieldNames::CONFIG_IMAGES => 'config_images',
      ConfigFieldNames::CONFIG_OVERLAP_CATEGORY => 'config_overlap_category',
      ConfigFieldNames::CONFIG_TIMEZONE => 'config_timezone',
      ConfigFieldNames::CONFIG_URLPREFIX => 'config_urlprefix',
      ConfigFieldNames::SHIFT_SHIFT_AREA => 'shift_shift_area',
      ConfigFieldNames::SKILL_CATEGORY => 'skill_category',
      ConfigFieldNames::SKILL_CLASS_TYPE => 'skill_class_type',
      ConfigFieldNames::SKILL_TIME_SLOT => 'skill_time_slot',
      ConfigFieldNames::SKILL_TRACK => 'skill_track',
    };
  }

  public static function fromString(string $str): ?ConfigFieldNames
  {
    switch (strtoupper($str)) {
      case 'CONFIG_COMBO_EVENTS':
        return self::CONFIG_COMBO_EVENTS;
      case 'CONFIG_DEBUG_EMAIL':
        return self::CONFIG_DEBUG_EMAIL;
      case 'CONFIG_IMAGES':
        return self::CONFIG_IMAGES;
      case 'CONFIG_OVERLAP_CATEGORY':
        return self::CONFIG_OVERLAP_CATEGORY;
      case 'CONFIG_TIMEZONE':
        return self::CONFIG_TIMEZONE;
      case 'CONFIG_URLPREFIX':
        return self::CONFIG_URLPREFIX;
      case 'SHIFT_SHIFT_AREA':
        return self::SHIFT_SHIFT_AREA;
      case 'SKILL_CATEGORY':
        return self::SKILL_CATEGORY;
      case 'SKILL_CLASS_TYPE':
        return self::SKILL_CLASS_TYPE;
      case 'SKILL_TIME_SLOT':
        return self::SKILL_TIME_SLOT;
      case 'SKILL_TRACK':
        return self::SKILL_TRACK;
      default:
        return null;
    }
  }

  public static function toOptions(): array
  {
    $result = [];

    foreach ( ConfigFieldNames::cases() as $c ) {
      $result[$c->value] = $c->toString();
    }

    return $result;
  }

}


