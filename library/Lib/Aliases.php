<?php

namespace ClawCorpLib\Lib;

class Aliases {
  const current='c0423';
  const active = [\ClawCorpLib\Lib\Aliases::current, 'l1122_hotels', 'virtualclaw', 'refunds'];
  const location = 'westin-bonaventure';

  // Used in override/registration.php to calculate hotel fees specifically
  // Put current first (needed specifically by lib/profiles.php)
  const hotels = ['l1122_hotels'];

  // For refunds, allow past events to be identified
  // Also used for checking if volunteer is in good starting in registrationsurvey.php
  const past = ['c1121','c0422'];

  // Day, night, etc passes
  const passesAliases = [];

  // For event info display
  #const timezone = 'America/New_York';
  const timezone = 'America/Los_Angeles';

  // For database, based on service provider
  const timezonedb = 'American/Chicago';
  
  const defaultPrefix = 'C23'; // Use C for CLAW, L for Leather Getaway, V for Virtual CLAW
  const onsiteActive = false;
  
  const shiftCategories = [
    'shifts-guestservices','shifts-facilities','shifts-badgecheck',/*'shifts-transportation',*/
    'shifts-hospitality','shifts-se', 'shifts-silentauction','shifts-float',
    'shifts-specialty'
  ];

  const overlapCategories = 
  [
    'shifts-guestservices','shifts-facilities','shifts-badgecheck',
    'shifts-hospitality','shifts-se', 'shifts-silentauction','shifts-float',
    'speed-dating','dinner','buffet','buffet-breakfast',
  ];

  const invoiceCategories = [
		'sponsorships',
		'donation', 'donations-other',
		'sponsorships-level-1','sponsorships-level2','sponsorships-level3',
		'sponsorships-advertising','sponsorships-master-sustaining',
		'sponsorships-logo', 'sponsorships-multi-level',
		'vendormart'];

  // Categories that require main events (other than shifts)
  const categoriesRequiringMainEvent = ['speed-dating', 'dinner', 'buffet-breakfast', 'buffet','meal-combos','equipment-rentals'];

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

  const eventTitleMapping = [
    'c1121' => 'CLAW 21',
    'c0422' => 'CLAW 22',
    'l1122' => 'Leather Getaway 22',
    'c0423' => 'CLAW 23'
  ];
}