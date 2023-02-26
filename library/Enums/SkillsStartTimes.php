<?php
namespace ClawCorpLib\Enums;

use ClawCorpLib\Helpers\Helpers;

enum SkillsStartTimes: string {
  case A0930 = '9:30';
  case A1000 = '10:00';
  case A1100 = '11:00';
  case P0100 = '13:00';
  case P0200 = '14:00';
  case P0300 = '15:00';
  case P0330 = '15:30';
  case P0430 = '16:30';
  case P0700 = '19:00';
  case TBD = '';

  public function ToString(): string {
    if ( SkillsStartTimes::TBD == $this ) return "TBD";

    return Helpers::formatTime($this->value);
  }

  public function ToSql(): string {
    date_default_timezone_set('etc/UTC');
    return date('H:i:s', strtotime($this->value));
  }

  public static function Find(string $key): ?SkillsStartTimes {
    foreach (SkillsStartTimes::cases() as $c )
    {
      if ( $c->name == $key ) return $c;
    }

    return null;
  }
}