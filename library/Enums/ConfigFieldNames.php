<?php
namespace ClawCorpLib\Enums;

enum ConfigFieldNames
{
  case CONFIG_COMBO_EVENTS;
  case CONFIG_DEBUG_EMAIL;
  case CONFIG_IMAGES;
  case CONFIG_OVERLAP_CATEGORY;
  case CONFIG_SHIFT_CATEGORY;
  case CONFIG_TIMEZONE;
  case CONFIG_URLPREFIX;
  case SHIFT_SHIFT_AREA;
  case SKILL_CATEGORY;
  case SKILL_CLASS_TYPE;
  case SKILL_TIME_SLOT;
  case SKILL_TRACK;

  public function toString(): string
  {
    return match ($this) {
      ConfigFieldNames::CONFIG_COMBO_EVENTS => 'config_combo_events',
      ConfigFieldNames::CONFIG_DEBUG_EMAIL => 'config_debug_email',
      ConfigFieldNames::CONFIG_IMAGES => 'config_images',
      ConfigFieldNames::CONFIG_OVERLAP_CATEGORY => 'config_overlap_category',
      ConfigFieldNames::CONFIG_SHIFT_CATEGORY => 'config_shift_category',
      ConfigFieldNames::CONFIG_TIMEZONE => 'config_timezone',
      ConfigFieldNames::CONFIG_URLPREFIX => 'config_urlprefix',
      ConfigFieldNames::SHIFT_SHIFT_AREA => 'shift_shift_area',
      ConfigFieldNames::SKILL_CATEGORY => 'skill_category',
      ConfigFieldNames::SKILL_CLASS_TYPE => 'skill_class_type',
      ConfigFieldNames::SKILL_TIME_SLOT => 'skill_time_slot',
      ConfigFieldNames::SKILL_TRACK => 'skill_track',
    };
  }
}


