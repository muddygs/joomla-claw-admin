<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Lib;

\defined('_JEXEC') or die;

use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Helpers\Helpers;
use Exception;
use Joomla\CMS\Factory;

/** 
 * @package ClawCorpLib\Lib\Aliases
 * @description: Provides dynamic event configuration information
 */
class Aliases
{
  static function current(bool $nocache = false): string
  {
    // Is the session setting for alias set?
    // Only check when not in admin
    if (!$nocache) {
      $app = Factory::getApplication();
      if ($app->isClient('site')) {
        $siteAlias = Helpers::sessionGet('eventAlias');
        if (!is_null($siteAlias) && $siteAlias != '') {
          return $siteAlias;
        }
      }
    }

    return EventConfig::getCurrentEventAlias();
  }

  static function currentByLocation(int $clawLocationId): string
  {
    if (1 != $clawLocationId && 2 != $clawLocationId) {
      throw new Exception("Invalid clawLocationId", 1);
    }

    return EventConfig::getCurrentEventAlias($clawLocationId);
  }
}
