<?php

namespace ClawCorpLib\Lib;

class Aliases {
  const current='l1122';
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

  // For checking volunteers who are in good standing, define the events for which
  // we check to offer an automatic coupon applicable for the current volunteer-2
  // registration

  const goodStandingEvents = [ 'c21-volunteer', 'c21-volunteer-2', 'c21-volunteer-1',
    'c22-volunteer-super', 'c22-volunteer-3', 'c22-volunteer', 'c22-volunteer-1',
  ];

  // For event info display
  #const timezone = 'America/New_York';
  const timezone = 'America/Los_Angeles';

  // For database, based on service provider
  const timezonedb = 'American/Chicago';
  
  const defaultPrefix = 'L22'; // Use C for CLAW, L for Leather Getaway, V for Virtual CLAW
  const onsiteActive = false;
  
  const shiftCategories = [
    'shifts-guestservices','shifts-facilities','shifts-badgecheck',/*'shifts-transportation',*/
    'shifts-hospitality','shifts-se', 'shifts-store', 'shifts-silentauction','shifts-float',
    'shifts-specialty'
  ];

  const overlapCategories = 
  [
    'shifts-guestservices','shifts-facilities','shifts-badgecheck','shifts-transportation',
    'shifts-hospitality','shifts-se', 'shifts-store', 'shifts-silentauction','shifts-float',
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
    'parties',
    'buffet-breakfast'
  ];

  // Events listing sponsor icons
  const imagedir = 'images/0_static_graphics/sponsors/100';

  // Events listing ad base (ads/thumbs use for preview)
  const adsdir = 'images/0_static_graphics/ads';

  // VendorMart icons
  const vendordir = 'images/0_static_graphics/vendors';

  const eventTitleMapping = [
    'c1121' => 'CLAW 21',
    '2021-11' => 'CLAW 21', // RSForm
    'c0422' => 'CLAW 22',
    '2022-04' => 'CLAW 22', // RSForm
    'l1122' => 'Leather Getaway 22',
    'c0423' => 'CLAW 23'
  ];
}