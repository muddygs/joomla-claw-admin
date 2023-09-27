<?php

namespace ClawCorpLib\Enums;

use ClawCorpLib\Lib\ClawEvents;

enum EventSponsorshipTypes: int
{
  case advertising = 1;
  case logo = 2;
  case master_sustaining = 3;
  case black = 4;
  case blue= 5;
  case gold= 6;

  public function toCategoryId(): int
  {
    return match ($this) {
      EventSponsorshipTypes::advertising => ClawEvents::getCategoryId('sponsorships-'.$this->name),
      EventSponsorshipTypes::logo => ClawEvents::getCategoryId('sponsorships-'.$this->name),
      EventSponsorshipTypes::master_sustaining => ClawEvents::getCategoryId('sponsorships-master-sustaining'),
      EventSponsorshipTypes::black => ClawEvents::getCategoryId('sponsorships-'.$this->name),
      EventSponsorshipTypes::blue => ClawEvents::getCategoryId('sponsorships-'.$this->name),
      EventSponsorshipTypes::gold => ClawEvents::getCategoryId('sponsorships-'.$this->name),
      default => 0
    };
  }
}
