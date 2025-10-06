<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Enums;

enum FeeEvents: string
{
  case included = "Included";
  case preorder = "Preorder Required";
  case door = "Door Purchase Available";
  case dooronly = "At Door Only";
}
