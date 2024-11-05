<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Enums;

enum EbPublishedState: int
{
  case any = 0;
  case published = 1;
  case cancelled = 2;
  case waitlist = 3;
}

