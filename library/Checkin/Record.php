<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Checkin;

class Record
{
  public string $badgeId = '';
  public bool $issued = false;
  public bool $printed = false;
  public string $legalName = '';
  public string $city = '';
  public string $clawPackage = '';
  public string $dinner = '';
  public string $brunch = '';
  public string $buffets = '';
  public string $shifts = '';
  public string $registration_code = '';
  public string $shirtSize = '';
  public string $error = '';
  public string $info = '';
}
