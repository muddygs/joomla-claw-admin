<?php

namespace ClawCorpLib\Lib;

\defined('_JEXEC') or die;

class Aliases {
  const current='c0423';
  const active = [\ClawCorpLib\Lib\Aliases::current, 'l1122', 'virtualclaw', 'refunds'];
  const location = 'westin-bonaventure';

  // For refunds, allow past events to be identified
  // Also used for checking if volunteer is in good starting in registrationsurvey.php
  const past = ['l1122', 'c1121', 'c0422'];

  // Day, night, etc passes
  const passesAliases = [];

  // For event info display
  #const timezone = 'America/New_York';
  const timezone = 'America/Los_Angeles';

  // For database, based on service provider
  const timezonedb = 'American/Chicago';
  
  const defaultPrefix = 'C23'; // Use C for CLAW, L for Leather Getaway, V for Virtual CLAW
  const onsiteActive = false;
  const onsiteCoupon = 'ONSITE-CASH'; // TODO: Remove all usage of this
  
  const shiftCategories = [
    'shifts-artshow',
    'shifts-badgecheck',
    'shifts-events',
    'shifts-facilities',
    'shifts-float',
    'shifts-guestservices',
    'shifts-hospitality',
    'shifts-se',
    'shifts-silentauction',
    'shifts-specialty',
  ];

  const overlapCategories = 
  [
    'shifts-artshow',
    'shifts-badgecheck',
    'shifts-events',
    'shifts-facilities',
    'shifts-float',
    'shifts-guestservices',
    'shifts-hospitality',
    'shifts-se',
    'shifts-silentauction',

    'speed-dating',
    'dinner',
    'buffet',
    'buffet-breakfast',
  ];

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
  const imagedir = '/images/0_static_graphics/sponsors/100';

  // Events listing ad base (ads/thumbs use for preview)
  const adsdir = '/images/0_static_graphics/ads';

  // VendorMart icons
  const vendordir = '/images/0_static_graphics/vendors';

  // S&E Presenter Images
  const presentersdir = '/images/skills/presenters';

  // TODO: Pull from event data directly
  const eventTitleMapping = [
    'c1121' => 'CLAW 21',
    'c0422' => 'CLAW 22',
    'l1122' => 'Leather Getaway 22',
    'c0423' => 'CLAW 23',
    'l1123' => 'Leather Getaway 23',
  ];
}