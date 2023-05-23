<?php

namespace ClawCorpLib\Enums;

enum EbPublishedState: int
{
  case any = 0;
  case published = 1;
  case cancelled = 2;
  case waitlist = 3;
}