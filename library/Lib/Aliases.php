<?php

namespace ClawCorpLib\Lib;

\defined('_JEXEC') or die;

use ClawCorpLib\Enums\ConfigFieldNames;
use ClawCorpLib\Helpers\Config;
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

    return Config::getCurrentEventAlias();
  }

  // For refunds, allow past events to be identified
  // Also used for checking if volunteer is in good starting in registrationsurvey.php
  // TODO: Probably need to fix getInactiveEventAliases const past = ['c0423'];

  const onsiteCoupon = 'ONSITE-CASH'; // TODO: Remove all usage of this
  
  static function shiftCategories() {
    return Config::getConfigValuesText(ConfigFieldNames::CONFIG_SHIFT_CATEGORY);
  }

  static function overlapCategories() {
    return Config::getConfigValuesText(ConfigFieldNames::CONFIG_OVERLAP_CATEGORY);
  }

  const invoiceCategories = [
		'sponsorships',
		'donation',
    'donations-other',
		'sponsorships-gold',
    'sponsorships-black',
    'sponsorships-blue',
		'sponsorships-advertising',
    'sponsorships-master-sustaining',
		'sponsorships-logo',
		'vendormart'
  ];

  // Events listing sponsor icons
  static function sponsorIconDir() {
    return Config::getConfigValuesText(ConfigFieldNames::CONFIG_IMAGES, 'sponsor_icons');
  }

  // Events listing ad base (ads/thumbs used for preview)
  static function adsDir() {
    return Config::getConfigValuesText(ConfigFieldNames::CONFIG_IMAGES, 'ads');
  }

  // S&E Presenter Images
  static function presentersDir() {
    return Config::getConfigValuesText(ConfigFieldNames::CONFIG_IMAGES, 'presenters');
  }
}