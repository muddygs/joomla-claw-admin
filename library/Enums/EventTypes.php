<?php

namespace ClawCorpLib\Enums;

enum EventTypes: int
{
  case none = 0;
  case main = 1;
  case vc = 3;
  case refunds = 4;

  public function toString(): string
  {
    return match ($this) {
      EventTypes::main => 'Main',
      EventTypes::vc => 'Virtual Claw',
      EventTypes::refunds => 'Refunds',
      EventTypes::none => 'None',
    };
  }

  public static function toOptions(): array
  {
    $result = [];

    foreach ( EventTypes::cases() as $c ) {
      if ( $c == EventTypes::refunds ) continue;
      
      $result[$c->value] = $c->toString();
    }

    return $result;
  }

  public static function FindValue(int $key): EventTypes
  {
    foreach (EventTypes::cases() as $c) {
      if ($c->value == $key) return $c;
    }

    throw (new \Exception("Invalid EventTypes value: $key"));
  }
}
