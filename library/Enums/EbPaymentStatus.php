<?php
namespace ClawCorpLib\Enums;

enum EbPaymentStatus : int
{
  case unknown = -1;
  case partial = 0;
  case paid = 1;
}
