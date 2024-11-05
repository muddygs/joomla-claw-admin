<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Enums;

enum JwtStates: string
{
  case new = 'new';
  case expired = 'expired';
  case issued = 'issued';
  case revoked = 'revoked';
  case confirm = 'confirm';
  case init = 'init';
  case error = 'error';
}
