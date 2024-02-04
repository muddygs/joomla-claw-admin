<?php

namespace ClawCorpLib\Lib;

\defined('_JEXEC') or die;

use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Helpers\Helpers;
use Joomla\CMS\Factory;

/** 
 * @package ClawCorpLib\Lib\Aliases
 * @description: Provides dynamic event configuration information
*/
class Aliases {
  static function current(bool $nocache = false) {
    // Is the session setting for alias set?
    // Only check when not in admin
    if ( !$nocache ) {
      $app = Factory::getApplication();
      if ( $app->isClient('site') ) {
        $siteAlias = Helpers::sessionGet('eventAlias');
        if ( $siteAlias != '' ) {
          return $siteAlias;
        }
      }
    }

    return EventConfig::getCurrentEventAlias();
  }
}