<?php
namespace ClawCorpLib\Lib;

class Coupon {
  public function __construct(
    public string $code,
    public int $eventId,
  )
  {
    
  }
}