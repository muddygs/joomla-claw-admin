<?php

namespace ClawCorpLib\Helpers;

use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Enums\PackageInfoTypes;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Lib\Ebmgmt;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Lib\EventInfo;
use DateTimeImmutable;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;

class Deploy
{
  public const PACKAGEINFO = 1;
  public const SPEEDDATING = 2;
  public const EQUIPMENTRENTAL = 3;
  public const SPONSORSHIPS = 4;

  private int $gid_public = 0;
  private int $gid_registered = 0;
  private DatabaseDriver $db;

  public function __construct (
    public string $eventAlias,
    public int $type
  ) {
    // Validate events are valid
    if (!EventInfo::isValidEventAlias($this->eventAlias)) {
      die('Invalid to deployment event: ' . $this->eventAlias);
    }

    $this->gid_public = Helpers::getAccessId('Public');
    $this->gid_registered = Helpers::getAccessId('Registered');

    if ( 0 == $this->gid_public || 0 == $this->gid_registered ) {
      die('Invalid group id');
    }

    /** @var \Joomla\Database\DatabaseDriver */
    $this->db = Factory::getContainer()->get('DatabaseDriver');


  }

  public function deploy(): string
  {
    switch ($this->type) {
      case self::PACKAGEINFO:
        $log = $this->Packages();
        $log .= $this->DiscountBundles();
        return $log;
      break;

      case self::SPEEDDATING:
        return $this->SpeedDating();
      break;

      case self::EQUIPMENTRENTAL:
        return $this->Packages();
      break;

      case self::SPONSORSHIPS:
        return $this->Sponsorships();
      break;

      default:
        die('Invalid deploy type');
      break;
    }
  }

  private function Insert(
    string $mainCategoryId, 
    string $itemAlias, 
    string $title,
    string $description,
    string $article_id,
    string $cancel_before_date,
    string $cut_off_date,
    string $event_date,
    string $event_end_date,
    string $publish_down,
    string $individual_price,
    string $registration_start_date,
    string $registration_access,
    string $price_text = '',
    string $user_email_body = ''
  ): int {
    $insert = new ebMgmt(
      eventAlias: $this->eventAlias, 
      mainCategoryId: $mainCategoryId, 
      itemAlias: $itemAlias, 
      title: $title,
      description: $description
    );

    $insert->set('article_id', $article_id, false);
    $insert->set('cancel_before_date', $cancel_before_date);
    $insert->set('cut_off_date', $cut_off_date);
    $insert->set('event_date', $event_date);
    $insert->set('event_end_date', $event_end_date);
    $insert->set('publish_down', $publish_down);

    $insert->set('individual_price', $individual_price);
    $insert->set('price_text', $price_text);
    $insert->set('registration_start_date', $registration_start_date);
    $insert->set('payment_methods', 2); // Credit Cart
    $insert->set('registration_access', $registration_access);
    $insert->set('user_email_body', $user_email_body);
    $insert->set('user_email_body_offline', $user_email_body);

    $eventId = $insert->insert();

    return $eventId;
  }

  public function SpeedDating(): string 
  {
    $log = [];
    $count = 0;

    // Ignore server-specific timezone information
    date_default_timezone_set('etc/UTC');

    $eventConfig = new EventConfig($this->eventAlias, [PackageInfoTypes::speeddating]);
    $info = $eventConfig->eventInfo;
    $packageInfos = $eventConfig->packageInfos;

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($packageInfos as $packageInfo) {
      foreach ( $packageInfo->meta AS $session ) {
        $start = Factory::getDate($packageInfo->start)->toSql();
        $end = Factory::getDate($packageInfo->end)->toSql();
        $cancel_before_date = $start;
        $cutoff = $start;

        // start and ending usability of these events
        $registration_start_date = Factory::getDate()->toSql();

        $title = $info->prefix . ' ' . $packageInfo->title . ' (' . $session . ')';
        $alias = strtolower(preg_replace('/[^\S]+/', '_', implode('-', [$info->prefix, 'sd', $packageInfo->title, $session])));

        $eventId = $this->Insert(
          mainCategoryId: $packageInfo->category, 
          itemAlias: $alias, 
          title: $title,
          description: $packageInfo->description ? $packageInfo->description : $packageInfo->title,
          article_id: $info->termsArticleId,
          cancel_before_date: $cancel_before_date,
          cut_off_date: $cutoff,
          event_date: $start,
          event_end_date: $end,
          publish_down: $end,
          individual_price: 0,
          registration_start_date: $registration_start_date,
          registration_access: $this->gid_registered
        );
  
        if ($eventId == 0) {
          $log[] =  "Skipping existing: $title";
  
          // So the alias exists, let's pull the event id from the database
          $eventId = ClawEvents::getEventId($packageInfo->alias, true);
          if ( $eventId != 0) {
            $packageInfo->eventId = $eventId;
            $packageInfo->save();
            $log[] = "Updated: $title at event id $eventId";
          }
  
        } else {
          $count++;
          $log[] =  "Added: $title at event id $eventId";
          $packageInfo->eventId = $eventId;
          $packageInfo->save();
        }
  
      }
    }

    $log[] = "Deployed $count speed dating packages.";

    return '<p>'.implode('</p><p>', $log).'</p>';
  }

  public function Packages(): string
  {
    $log = [];
    $count = 0;

    // Ignore server-specific timezone information
    date_default_timezone_set('etc/UTC');

    $eventConfig = new EventConfig($this->eventAlias, []);
    $info = $eventConfig->eventInfo;
    $packageInfos = $eventConfig->packageInfos;

    // Base times to offset by "time" parameter for each event
    $cancel_before_date = $info->cancelBy;
    $startDate = $info->modify('Wed 9AM');
    $endDate = $info->modify('next Monday midnight');;

    // start and ending usability of these events
    $registration_start_date = Factory::getDate()->toSql();
    $publish_down = $info->modify('+8 days');

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($packageInfos as $packageInfo) {
      if ( $packageInfo->eventId > 0 ) {
        $log[] =  "Already deployed: $packageInfo->title @ $packageInfo->eventId";
        continue;
      }

      $name = str_replace('_', '-', $packageInfo->eventPackageType->name);
      $packageInfo->alias = strtolower($info->prefix . '-' . $name);

      $start = $startDate;
      $end = $endDate;
      $cutoff = $endDate;

      $accessGroup = $this->gid_registered;
      $reg_start_date = $registration_start_date;

      $price_text = '';

      switch ( $packageInfo->packageInfoType ) {
        case PackageInfoTypes::combomeal:
        case PackageInfoTypes::main:
          $packageInfo->start = $startDate;
          $packageInfo->end = $endDate;

          if ( $packageInfo->bundleDiscount > 0 ) {
            $price_text = '$' . $packageInfo->fee . ' (attendee) / $' . $packageInfo->fee - $packageInfo->bundleDiscount . ' (volunteer)';
          }
        break;
        
        case PackageInfoTypes::addon:
          $start = Factory::getDate($packageInfo->start)->toSql();
          $end = Factory::getDate($packageInfo->end)->toSql();
  
          $origin = new DateTimeImmutable($start);
          $target = new DateTimeImmutable($end);
          $interval = $origin->diff($target);
  
          // If the event is less than 8 hours, then the cutoff is 3 hours before the event
          if ($interval->h <= 8) {
            $cutoff = Factory::getDate($packageInfo->start);
            $cutoff = $cutoff->modify('-3 hours')->toSql();
          }

          if ( $packageInfo->bundleDiscount > 0 ) {
            $price_text = '$' . $packageInfo->fee . ' (attendee) / $' . $packageInfo->fee - $packageInfo->bundleDiscount . ' (volunteer)';
          }
        break;

        case PackageInfoTypes::daypass:
          $start = Factory::getDate($packageInfo->start)->toSql();
          $end = Factory::getDate($packageInfo->end)->toSql();
          $reg_start_date = $startDate->toSql();
        break;

        case PackageInfoTypes::passes:
          $start = Factory::getDate($packageInfo->start)->toSql();
          $end = Factory::getDate($packageInfo->end)->toSql();
          $cutoff = '0000-00-00 00:00:00';
          // Remove any non-ascii char from title
          $name = preg_replace('/[^\S]+/', '-', $packageInfo->title);
          $packageInfo->alias = strtolower($info->prefix . '-' . $name);
          $accessGroup = $this->gid_public;
          $reg_start_date = $startDate->toSql();
        break;

        case PackageInfoTypes::equipment:
          $start = Factory::getDate($packageInfo->start)->toSql();
          $end = Factory::getDate($packageInfo->end)->toSql();
          $cutoff = $startDate->toSql();
        break;
        
        case PackageInfoTypes::coupononly:
          continue 2;
        break;

        default:
          continue 2;
        break;
      }

      $eventId = $this->Insert(
        mainCategoryId: $packageInfo->category, 
        itemAlias: $packageInfo->alias, 
        title: $info->prefix . ' ' . $packageInfo->title,
        description: $packageInfo->description ? $packageInfo->description : $packageInfo->title,
        article_id: $info->termsArticleId,
        cancel_before_date: $cancel_before_date->toSql(),
        cut_off_date: $cutoff,
        event_date: $start,
        event_end_date: $end,
        publish_down: $publish_down->toSql(),
        individual_price: $packageInfo->fee,
        price_text: $price_text,
        registration_start_date: $reg_start_date,
        registration_access: $accessGroup
      );

      if ($eventId == 0) {
        $log[] =  "Skipping existing: $packageInfo->title";

        // So the alias exists, let's pull the event id from the database
        $eventId = ClawEvents::getEventId($packageInfo->alias, true);
        if ( $eventId != 0) {
          $packageInfo->eventId = $eventId;
          $packageInfo->save();
          $log[] = "Updated: $packageInfo->title at event id $eventId";
        }

      } else {
        $count++;
        $log[] =  "Added: $packageInfo->title at event id $eventId";
        $packageInfo->eventId = $eventId;
        $packageInfo->save();
      }

      // TODO: still want friendly redirects?
      $suffix = $packageInfo->eventPackageType->toLink();
      if ( $suffix != '' ) {
        $fromLink = strtolower($info->prefix . '-reg-' . $suffix);
        $toLink = EventBooking::buildRegistrationLink($this->eventAlias, $packageInfo->eventPackageType);
        $redirect = new Redirects($this->db, '/'.$fromLink, $toLink, $fromLink);
        $redirect->insert();
      }
    }

    // Special link cases
    // addons
    $suffix = EventPackageTypes::addons->toLink();
    $fromLink = strtolower($info->prefix . '-reg-' . $suffix);
    $toLink = EventBooking::buildRegistrationLink($this->eventAlias, EventPackageTypes::addons);
    $redirect = new Redirects($this->db, '/'.$fromLink, $toLink, $fromLink);
    $redirect->insert();
    // vip2
    $suffix = EventPackageTypes::vip2->toLink();
    $fromLink = strtolower($info->prefix . '-reg-' . $suffix);
    $toLink = EventBooking::buildRegistrationLink($this->eventAlias, EventPackageTypes::vip2);
    $redirect = new Redirects($this->db, '/'.$fromLink, $toLink, $fromLink);
    $redirect->insert();


    $log[] = "Deployed $count packages.";

    return '<p>'.implode('</p><p>', $log).'</p>';
  }

  public function Sponsorships(): string
  {
    $log = [];
    $count = 0;

    // Ignore server-specific timezone information
    date_default_timezone_set('etc/UTC');

    $eventConfig = new EventConfig($this->eventAlias, []);
    $info = $eventConfig->eventInfo;
    $packageInfos = $eventConfig->packageInfos;

    // Map Eventbooking configured categories to supported sponsorships
    $sponsorshipCategories = ClawEvents::getCategoryIds([
      'sponsorships-advertising',
      'sponsorships-logo',
      'sponsorships-master-sustaining',
      'sponsorships-black',
      'sponsorships-blue',
      'sponsorships-gold',
      'donations-leather-heart'
    ], true);

    $componentParams = ComponentHelper::getParams('com_claw');
    $user_email_body = $componentParams->get('sponsorship_registration_email', '');

    // Base times to offset by "time" parameter for each event
    $cancel_before_date = $info->cancelBy;
    $startDate = $info->modify('Wed 9AM');
    $endDate = $info->modify('next Monday midnight');;

    // start and ending usability of these events
    $registration_start_date = Factory::getDate()->toSql();
    $publish_down = $info->modify('+8 days');
 
    $accessGroup = $this->gid_public;

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($packageInfos as $packageInfo) {
      if ( $packageInfo->eventId > 0 ) {
        $log[] =  "Already deployed: $packageInfo->title @ $packageInfo->eventId";
        continue;
      }

      $packageInfo->alias = strtolower($info->prefix . '_spo_' . preg_replace("/[^A-Za-z0-9]+/", '_', $packageInfo->title));

      $start = $startDate;
      $end = $endDate;
      $cutoff = $endDate;

      $reg_start_date = $registration_start_date;

      switch ( $packageInfo->category ) {
        // We need advertising submitted no later than 3 weeks before the event
        case $sponsorshipCategories['sponsorships-advertising']:
          $cutoff = $startDate->modify('-3 weeks')->toSql();
          break;
        
        case $sponsorshipCategories['sponsorships-logo']:
          $cutoff = $startDate->modify('-1 week')->toSql();
          break;

        // Buffer until next event
        case $sponsorshipCategories['sponsorships-master-sustaining']:
          $cutoff = $startDate->modify('+6 months')->toSql();
          $end = $cutoff;
        break;

        // Blue, black, gold are all the same
        case $sponsorshipCategories['sponsorships-black']:
        case $sponsorshipCategories['sponsorships-blue']:
        case $sponsorshipCategories['sponsorships-gold']:
          $cutoff = $startDate->modify('-1 week')->toSql();
        break;

        // Leather heart donations are available until the end of the event
        case $sponsorshipCategories['donations-leather-heart']:
          $cutoff = $endDate;
        break;

        default:
          die('Invalid sponsorship category');
        break;
      }

      $eventId = $this->Insert(
        mainCategoryId: $packageInfo->category, 
        itemAlias: $packageInfo->alias, 
        title: $info->prefix . ' ' . $packageInfo->title,
        description: $packageInfo->description ? $packageInfo->description : $packageInfo->title,
        article_id: $info->termsArticleId,
        cancel_before_date: $cancel_before_date->toSql(),
        cut_off_date: $cutoff,
        event_date: $start,
        event_end_date: $end,
        publish_down: $publish_down->toSql(),
        individual_price: $packageInfo->fee,
        registration_start_date: $reg_start_date,
        registration_access: $accessGroup,
        user_email_body: $user_email_body,
      );

      if ($eventId == 0) {
        $log[] =  "Skipping existing: $packageInfo->title";

        // So the alias exists, let's pull the event id from the database
        $eventId = ClawEvents::getEventId($packageInfo->alias, true);
        if ( $eventId != 0) {
          $packageInfo->eventId = $eventId;
          $packageInfo->save();
          $log[] = "Updated: $packageInfo->title at event id $eventId";
        }

      } else {
        $count++;
        $log[] =  "Added: $packageInfo->title at event id $eventId";
        $packageInfo->eventId = $eventId;
        $packageInfo->save();
      }
    }

    $log[] = "Deployed $count packages.";

    return '<p>'.implode('</p><p>', $log).'</p>';
  }

  public function DiscountBundles(): string
  {
    $log = [];
    $count = 0;

    $mainEventConfig = new EventConfig($this->eventAlias, [PackageInfoTypes::main]);
    $mainPackages = $mainEventConfig->packageInfos;

    $addonsEventConfig = new EventConfig($this->eventAlias, [PackageInfoTypes::addon, PackageInfoTypes::combomeal]);
    $addonsPackages = $addonsEventConfig->packageInfos;

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($mainPackages as $packageInfo) {
      if ( $packageInfo->eventId == 0 ) {
        $log[] =  "Skipping Discount Bundle (Event ID 0): $packageInfo->title";
        continue;
      }

      if ( !$packageInfo->isVolunteer ) {
        $log[] =  "Skipping Discount Bundle (Not Volunteer): $packageInfo->title";
        continue;
      }

      /** @var \ClawCorpLib\Lib\PackageInfo */
      foreach ( $addonsPackages AS $addon ) {
        if ( $addon->eventId == 0 ) {
          $log[] =  "Skipping Discount Bundle (Event ID 0): $addon->title";
          continue;
        }

        if ( $addon->bundleDiscount == 0 ) {
          $log[] =  "Skipping Discount Bundle (No Discount): $addon->title";
          continue;
        }

        $log[] = $this->addDiscountBundle([$packageInfo, $addon], $addon->bundleDiscount);

        $count++;
      }
    }

    $log[] = "Updated $count discount bundles.";

    return '<p>'.implode('</p><p>', $log).'</p>';
  }

  /**
   * Helper function for creating discount bundles among events by $ amount
   * @param array Array of PackageInfo objects
   * @param int Dollar amount
   * @return True if added, False on error or duplicate (by title)
   */
  private function addDiscountBundle(array $packageInfos, int $dollarAmount): string
  {
    if ( count($packageInfos) < 2 ) return "Skipping discount bundle: Not enough events";

    $eventIds = [];
    $titles = [];

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ( $packageInfos AS $packageInfo ) {
      if ( $packageInfo->eventId == 0 ) return "Skipping discount bundle: Invalid event ID";
      $eventIds[] = $packageInfo->eventId;
      $titles[] = $packageInfo->title;
    }

    $db = $this->db;

    // Check for existing discount
    $query = $db->getQuery(true);
    $query->select('discount_id')
      ->from('#__eb_discount_events')
      ->where('event_id IN ('.implode(',',$eventIds).')')
      ->group('discount_id')
      ->having('COUNT(DISTINCT event_id) = '.count($eventIds));
    $db->setQuery($query);
    $result = $db->loadResult();

    if ( $result != null ) return "Skipping duplicate discount: $result";

    $title = implode('-',$titles);

    $query = $db->getQuery(true);

    $data = (object)[
      'id' => 0,
      'title' => $title,
      'event_ids' => implode(',',$eventIds),
      'discount_amount' => $dollarAmount,
      'from_date' => $query->nullDate(false),
      'to_date' => $query->nullDate(false),
      'times' => 0,
      'used' => 0,
      'published' => 1,
      'number_events' => 0,
      'discount_type' => 1
    ];

    $result = $db->insertObject('#__eb_discounts', $data, 'id');

    if ( $result === false ) return "Error adding discount: $title";

    foreach ( $eventIds AS $eventId ) {
        $discount = (object)[
            'id' => 0,
            'discount_id' => $data->id,
            'event_id' => $eventId
        ];

        $result = $db->insertObject('#__eb_discount_events', $discount, 'id');
        if ( $result === false ) return "Error adding discount events: $title";
    }

    return "Added discount: $title";
  }


}