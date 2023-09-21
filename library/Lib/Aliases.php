<?php

namespace ClawCorpLib\Lib;

\defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Config;
use ClawCorpLib\Helpers\Helpers;
use Joomla\CMS\Factory;

class Aliases {
  static function current() {
    // Is the session setting for alias set?
    // Only check when not in admin
    $app = Factory::getApplication();
    $siteAlias = Helpers::sessionGet('eventAlias');
    if ( $app->isClient('site') && $siteAlias != '' ) {
      return $siteAlias;
    }

    return Config::getCurrentEventAlias();
  }

  static function active() {
    return Config::getActiveEventAliases();
  }
  
  // For refunds, allow past events to be identified
  // Also used for checking if volunteer is in good starting in registrationsurvey.php
  // TODO: Probably need to fix getInactiveEventAliases const past = ['c0423'];

  // For event info display
  #const timezone = 'America/New_York';

  const defaultPrefix = 'L23'; // Use C for CLAW, L for Leather Getaway, V for Virtual CLAW
  const onsiteActive = false;
  const onsiteCoupon = 'ONSITE-CASH'; // TODO: Remove all usage of this
  
  static function shiftCategories() {
    return Config::getConfigValuesText('config_shift_category');
  }

  static function overlapCategories() {
    return Config::getConfigValuesText('config_overlap_category');
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

  const sponsorshipCategories = [
    'sponsorships-black',
    'sponsorships-blue',
    'sponsorships-gold',
    'sponsorships-master-sustaining',
    'sponsorships-advertising',
    'sponsorships-logo'
  ];

  // Categories that require main events (other than shifts)
  const categoriesRequiringMainEvent = ['speed-dating', 'dinner', 'buffet-breakfast', 'buffet','meal-combos','equipment-rentals'];

  // Meal combo events -- clawcart expects leading dash
  const mealComboDinners = ['-thu-buffet','-fri-buffet','-dinner','-sun-buffet'];
  const mealComboAll =     ['-thu-buffet','-fri-buffet','-dinner','-sun-buffet','-fri-breakfast','-sat-breakfast','-brunch'];
  
  const categoriesTicketedEvents = [
    'dinner',
    'buffet',
    // 'parties',
    'buffet-breakfast'
  ];

  // Events listing sponsor icons
  static function sponsorIconDir() {
    return Config::getConfigValuesText('config_images', 'sponsor_icons');
  }

  // Events listing ad base (ads/thumbs used for preview)
  static function adsDir() {
    return Config::getConfigValuesText('config_images', 'ads');
  }

  // S&E Presenter Images
  static function presentersDir() {
    return Config::getConfigValuesText('config_images', 'presenters');
  }
}