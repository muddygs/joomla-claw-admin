<?php

namespace ClawCorpLib\Helpers;

use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Enums\PackageInfoTypes;
use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Lib\Ebmgmt;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Lib\PackageInfo;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\User\UserFactoryInterface;

class Deploy
{
  public const PACKAGEINFO = 1;
  public const SPEEDDATING = 2;
  public const EQUIPMENTRENTAL = 3;
  public const SPONSORSHIPS = 4;
  public const SPA = 5;

  private int $public_acl = 0;
  private int $registered_acl = 0;
  private DatabaseDriver $db;
  private EventInfo $eventInfo;
  private Date $registration_start_date;

  public function __construct(
    public string $eventAlias,
    public int $type
  ) {
    try {
      $this->eventInfo = new EventInfo($this->eventAlias);
    } catch (\Exception) {
      throw new \InvalidArgumentException('Invalid for deployment - Event alias: ' . $this->eventAlias);
    }

    $this->registration_start_date = Factory::getDate('now', $this->eventInfo->timezone);

    $this->setDefaultAcls();
    /** @var \Joomla\Database\DatabaseDriver */
    $this->db = Factory::getContainer()->get('DatabaseDriver');

    date_default_timezone_set('etc/UTC');
  }

  public function deploy(): string
  {
    switch ($this->type) {
      case self::PACKAGEINFO:
        $log = $this->Packages();
        $log .= $this->VolunteerDiscountBundles();
        $log .= $this->MetaDiscountBundles();
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

      case self::SPA:
        return $this->Spa();
        break;

      default:
        die('Invalid deploy type');
        break;
    }
  }

  private function Insert(
    int $mainCategoryId,
    string $itemAlias,
    string $title,
    string $description,
    int $article_id,
    ?Date $cancel_before_date,
    ?Date $cut_off_date,
    Date $event_date,
    Date $event_end_date,
    ?Date $publish_down,
    float $individual_price,
    Date $registration_start_date,
    string $registration_access,
    string $price_text = '',
    string $user_email_body = '',
    string $payment_methods = '2',
    int $enable_cancel_registration = 1,
    int $event_capacity = 0,
    string $notification_emails = '',
  ): int {
    $insert = new ebMgmt(
      eventAlias: $this->eventAlias,
      mainCategoryId: $mainCategoryId,
      itemAlias: $itemAlias,
      title: $title,
      description: $description
    );

    $nullDate = $this->db->getNullDate();

    $insert->set('registration_start_date', $this->useLocalTimeAsUtcSql($registration_start_date, true));

    $insert->set('article_id', $article_id);
    $insert->set('cancel_before_date', $cancel_before_date ? $this->useLocalTimeAsUtcSql($cancel_before_date) : $nullDate);
    $insert->set('cut_off_date', $cut_off_date ? $this->useLocalTimeAsUtcSql($cut_off_date, true) : $nullDate);
    $insert->set('event_date', $this->useLocalTimeAsUtcSql($event_date));
    $insert->set('event_end_date', $this->useLocalTimeAsUtcSql($event_end_date));
    $insert->set('publish_down', $publish_down ? $this->useLocalTimeAsUtcSql($publish_down) : $nullDate);

    $insert->set('individual_price', $individual_price);
    $insert->set('price_text', $price_text);
    $insert->set('payment_methods', $payment_methods); // Credit Card
    $insert->set('registration_access', $registration_access);
    $insert->set('user_email_body', $user_email_body);
    $insert->set('user_email_body_offline', $user_email_body);
    $insert->set('enable_cancel_registration', $enable_cancel_registration);
    $insert->set('event_capacity', $event_capacity);
    $insert->set('notification_emails', $notification_emails);

    $eventId = $insert->insert();

    return $eventId;
  }

  private function useLocalTimeAsUtcSql(Date $date, bool $topOfHour = false): string
  {
    $d = clone $date;

    if ($topOfHour) {
      $timestamp = $d->getTimestamp();
      $timestamp -= $timestamp % 3600;
      $d->setTimezone(new \DateTimeZone($this->eventInfo->timezone))->setTimestamp($timestamp);
    }

    return $d->toSql(true);
  }

  public function SpeedDating(): string
  {
    $log = [];
    $count = 0;

    $eventConfig = new EventConfig($this->eventAlias, [PackageInfoTypes::speeddating]);
    $packageInfos = $eventConfig->packageInfos;

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($packageInfos as $packageInfo) {
      if ($packageInfo->published != EbPublishedState::published) continue;

      foreach ($packageInfo->meta as $metaKey => $metaRow) {
        $role = $metaRow->role;
        $event_capacity = $metaRow->limit;
        $eventId = $metaRow->eventId;

        if ($eventId > 0) {
          $log[] =  "Already deployed: $packageInfo->title $role @ $eventId";
          continue;
        }

        $start = $packageInfo->start;
        $end = $packageInfo->end;
        $cancel_before_date = $start;
        $cutoff = $start;

        $title = $this->eventInfo->prefix . ' ' . $packageInfo->title . ' (' . $role . ')';
        $alias = strtolower(preg_replace('/[^\S]+/', '_', implode('-', [$this->eventInfo->prefix, 'sd', $packageInfo->title, $role])));

        $eventId = $this->Insert(
          mainCategoryId: $packageInfo->category,
          itemAlias: $alias,
          title: $title,
          description: $packageInfo->description ? $packageInfo->description : $packageInfo->title,
          article_id: $this->eventInfo->termsArticleId,
          cancel_before_date: $cancel_before_date,
          cut_off_date: $cutoff,
          event_date: $start,
          event_end_date: $end,
          publish_down: $end,
          individual_price: 0,
          registration_start_date: $this->registration_start_date,
          registration_access: $this->registered_acl,
          event_capacity: $event_capacity,
        );

        if ($eventId == 0) {
          $log[] =  "Skipping existing: $title";

          // So the alias exists, let's pull the event id from the database
          $eventId = ClawEvents::getEventId($alias, true);
          if ($eventId != 0) {
            $packageInfo->meta->$metaKey->eventId = $eventId;
            $packageInfo->save();
            $log[] = "Updated: $title at event id $eventId";
          }
        } else {
          $count++;
          $log[] =  "Added: $title at event id $eventId";
          $packageInfo->meta->$metaKey->eventId = $eventId;
          $packageInfo->save();
        }
      }
    }

    $log[] = "Deployed $count speed dating packages.";

    return '<p>' . implode('</p><p>', $log) . '</p>';
  }

  public function Spa(): string
  {
    $log = [];
    $count = 0;

    $eventConfig = new EventConfig($this->eventAlias, [PackageInfoTypes::spa]);
    $eventInfo = $eventConfig->eventInfo;
    $packageInfos = $eventConfig->packageInfos;

    $userEmails = [];

    // Load default email for EB from DB to merge with therapist email
    $query = $this->db->getQuery(true);
    $query->select('config_value')
      ->from('#__eb_configs')
      ->where($this->db->qn('config_key') . '=' . $this->db->q('notification_emails'));
    $this->db->setQuery($query);
    $defaultEmail = $this->db->loadResult();

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($packageInfos as $packageInfo) {
      if ($packageInfo->published != EbPublishedState::published) continue;
      // The $userid in meta points to a therapist - one event/therapist/time
      $index = 0;

      foreach ($packageInfo->meta as $metaKey => $metaRow) {
        $eventId = $metaRow->eventId;

        if ($eventId > 0) {
          $log[] =  "Already deployed: $packageInfo->title ($metaRow->userid) @ $eventId";
          $index++;
          continue;
        }

        if (!array_key_exists($metaRow->userid, $userEmails)) {
          $userFactory = Factory::getContainer()->get(UserFactoryInterface::class);
          $user = $userFactory->loadUserById($metaRow->userid);
          $userEmails[$metaRow->userid] = $user->email;
        }

        $start = $packageInfo->start;
        $end = $packageInfo->end;
        $cancel_before_date = $start;
        $cutoff = $start;

        $alias = strtolower(preg_replace('/[^\S]+/', '_', implode('-', [$eventInfo->prefix, 'spa', $packageInfo->title, $index++])));

        $eventId = $this->Insert(
          mainCategoryId: $packageInfo->category,
          itemAlias: $alias,
          title: $eventInfo->prefix . ' ' . $packageInfo->title,
          description: $packageInfo->description ? $packageInfo->description : $packageInfo->title,
          article_id: $eventInfo->termsArticleId,
          cancel_before_date: $cancel_before_date,
          cut_off_date: $cutoff,
          event_date: $start,
          event_end_date: $end,
          publish_down: $end,
          individual_price: $packageInfo->fee,
          registration_start_date: $this->registration_start_date,
          registration_access: $this->registered_acl,
          event_capacity: 1,
          notification_emails: is_null($defaultEmail) ? $userEmails[$metaRow->userid] : implode(',', [$defaultEmail, $userEmails[$metaRow->userid]]),
        );

        if ($eventId == 0) {
          $log[] =  "Skipping existing: $packageInfo->title";

          // So the alias exists, let's pull the event id from the database
          $eventId = ClawEvents::getEventId($alias, true);
          if ($eventId != 0) {
            $packageInfo->meta->$metaKey->eventId = $eventId;
            $packageInfo->save();
            $log[] = "Updated: $packageInfo->title at event id $eventId";
          }
        } else {
          $count++;
          $log[] =  "Added: $packageInfo->title at event id $eventId";
          $packageInfo->meta->$metaKey->eventId = $eventId;
          $packageInfo->save();
        }
      }
    }

    $log[] = "Deployed $count packages.";

    return '<p>' . implode('</p><p>', $log) . '</p>';
  }

  public function Packages(): string
  {
    $log = [];
    $count = 0;

    $eventConfig = new EventConfig($this->eventAlias, []);
    $packageInfos = $eventConfig->packageInfos;

    // Base times to offset by "time" parameter for each event
    $cancel_before_date = $this->eventInfo->cancelBy;
    $startDateWed = $this->eventInfo->modify('Wed 9AM');
    $endDate = $this->eventInfo->modify('next Monday midnight');;

    $publish_down = $this->eventInfo->modify('+8 days');

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($packageInfos as $packageInfo) {
      if ($packageInfo->eventId > 0) {
        $this->createRedirect($this->eventInfo, $packageInfo);
        $log[] =  "Already deployed: $packageInfo->title @ $packageInfo->eventId";
        continue;
      }

      if ($packageInfo->published != EbPublishedState::published) {
        $log[] =  "Skipping unpublished: $packageInfo->title";
        continue;
      }

      $name = str_replace('_', '-', $packageInfo->eventPackageType->name);
      $packageInfo->alias = strtolower($this->eventInfo->prefix . '-' . $name);

      $start = $startDateWed;
      $end = $endDate;
      $cutoff = $endDate;

      $accessGroup = $packageInfo->group_id > 0 ? $packageInfo->group_id : $this->registered_acl;
      $reg_start_date = $this->registration_start_date;

      $price_text = '';
      $enable_cancel_registration = '1';

      switch ($packageInfo->packageInfoType) {
        case PackageInfoTypes::combomeal:
        case PackageInfoTypes::main:
          $packageInfo->start = $startDateWed;
          $packageInfo->end = $endDate;

          if ($packageInfo->bundleDiscount > 0) {
            $price_text = '$' . $packageInfo->fee . ' (attendee) / $' . $packageInfo->fee - $packageInfo->bundleDiscount . ' (volunteer)';
          }
          break;

        case PackageInfoTypes::addon:
          $interval = $packageInfo->start->diff($packageInfo->end);

          // If the event is less than 8 hours, then the cutoff is 3 hours before the event
          if ($interval->h <= 8) {
            $cutoff = Factory::getDate($packageInfo->start);
            $cutoff = $cutoff->modify('-3 hours');
          }

          if ($packageInfo->bundleDiscount > 0) {
            $price_text = '$' . $packageInfo->fee . ' (attendee) / $' . $packageInfo->fee - $packageInfo->bundleDiscount . ' (volunteer)';
          }
          break;

        case PackageInfoTypes::daypass:
          $start = $packageInfo->start;
          $end = $packageInfo->end;
          $reg_start_date = $startDateWed;
          break;

        case PackageInfoTypes::passes:
          $start = $packageInfo->start;
          $end = $packageInfo->end;
          $cutoff = null;
          $cancel_before_date = null;
          // Remove any non-ascii char from title
          $name = preg_replace('/[^\S]+/', '-', $packageInfo->title);
          $packageInfo->alias = strtolower($this->eventInfo->prefix . '-' . $name);
          $accessGroup = $this->public_acl;
          $reg_start_date = $startDateWed;
          $enable_cancel_registration = '0';
          break;

        case PackageInfoTypes::equipment:
          $start = $packageInfo->start;
          $end = $packageInfo->end;
          $cutoff = $startDateWed;
          $name = preg_replace('/[^\S]+/', '-', $packageInfo->title);
          $packageInfo->alias = strtolower($this->eventInfo->prefix . '-' . $name);
          break;

        default:
          continue 2;
          break;
      }

      $eventId = $this->Insert(
        mainCategoryId: $packageInfo->category,
        itemAlias: $packageInfo->alias,
        title: $this->eventInfo->prefix . ' ' . $packageInfo->title,
        description: $packageInfo->description ? $packageInfo->description : $packageInfo->title,
        article_id: $this->eventInfo->termsArticleId,
        cancel_before_date: $cancel_before_date,
        cut_off_date: $cutoff,
        event_date: $start,
        event_end_date: $end,
        publish_down: $publish_down,
        individual_price: $packageInfo->fee,
        price_text: $price_text,
        registration_start_date: $reg_start_date,
        registration_access: $accessGroup,
        enable_cancel_registration: $enable_cancel_registration
      );

      if ($eventId == 0) {
        $log[] =  "Skipping existing: $packageInfo->title";

        // So the alias exists, let's pull the event id from the database
        $eventId = ClawEvents::getEventId($packageInfo->alias, true);
        if ($eventId != 0) {
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

      $this->createRedirect($this->eventInfo, $packageInfo);
    }

    // Special friendly redirects cases
    // addons
    $suffix = EventPackageTypes::addons->toLink();
    $fromLink = strtolower($this->eventInfo->prefix . '-reg-' . $suffix);
    $toLink = EventBooking::buildRegistrationLink($this->eventAlias, EventPackageTypes::addons);
    $redirect = new Redirects($this->db, '/' . $fromLink, $toLink, $fromLink);
    $redirect->insert();

    $log[] = "Deployed $count packages.";

    return '<p>' . implode('</p><p>', $log) . '</p>';
  }

  private function createRedirect(EventInfo $eventInfo, PackageInfo $packageInfo)
  {
    $suffix = $packageInfo->eventPackageType->toLink();
    if ($suffix != '') {
      $fromLink = strtolower($eventInfo->prefix . '-reg-' . $suffix);
      $toLink = EventBooking::buildRegistrationLink($this->eventAlias, $packageInfo->eventPackageType);
      $redirect = new Redirects($this->db, '/' . $fromLink, $toLink, $fromLink);
      $redirect->insert();
    }
  }

  public function Sponsorships(): string
  {
    $log = [];
    $count = 0;

    $eventConfig = new EventConfig($this->eventAlias, [PackageInfoTypes::sponsorship]);
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
    $cancel_before_date = $this->eventInfo->cancelBy;
    $startDate = $this->eventInfo->modify('Wed 9AM');
    $endDate = $this->eventInfo->modify('next Monday midnight');;

    // start and ending usability of these events
    $publish_down = $this->eventInfo->modify('+8 days');

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($packageInfos as $packageInfo) {
      if ($packageInfo->eventId > 0) {
        $log[] =  "Already deployed: $packageInfo->title @ $packageInfo->eventId";
        continue;
      }

      if ($packageInfo->published != EbPublishedState::published) {
        $log[] =  "Skipping unpublished sponsorship: $packageInfo->title";
        continue;
      }

      $packageInfo->alias = strtolower($this->eventInfo->prefix . '_spo_' . preg_replace("/[^A-Za-z0-9]+/", '_', $packageInfo->title));

      $end = clone ($endDate);
      $cutoff = clone ($startDate);

      switch ($packageInfo->category) {
          // We need advertising submitted no later than 3 weeks before the event
        case $sponsorshipCategories['sponsorships-advertising']:
          $cutoff->modify('-3 weeks');
          break;

        case $sponsorshipCategories['sponsorships-logo']:
          $cutoff->modify('-1 week');
          break;

          // Buffer until next event
        case $sponsorshipCategories['sponsorships-master-sustaining']:
          $cutoff->modify('+6 months');
          $end = $cutoff;
          $publish_down = $cutoff;
          break;

          // Blue, black, gold are all the same
        case $sponsorshipCategories['sponsorships-black']:
        case $sponsorshipCategories['sponsorships-blue']:
        case $sponsorshipCategories['sponsorships-gold']:
          $cutoff->modify('-1 week');
          break;

          // Leather heart donations are available until the end of the event
        case $sponsorshipCategories['donations-leather-heart']:
          $cutoff = clone ($endDate);
          break;

        default:
          var_dump($packageInfo);
          die('Invalid sponsorship category');
          break;
      }

      $eventId = $this->Insert(
        mainCategoryId: $packageInfo->category,
        itemAlias: $packageInfo->alias,
        title: $this->eventInfo->prefix . ' ' . $packageInfo->title,
        description: $packageInfo->description ? $packageInfo->description : $packageInfo->title,
        article_id: $this->eventInfo->termsArticleId,
        cancel_before_date: $cancel_before_date,
        cut_off_date: $cutoff,
        event_date: $startDate,
        event_end_date: $end,
        publish_down: $publish_down,
        individual_price: $packageInfo->fee,
        registration_start_date: $this->registration_start_date,
        registration_access: $this->registered_acl,
        user_email_body: $user_email_body,
        payment_methods: '2,5' // Credit Card, Invoice
      );

      if ($eventId == 0) {
        $log[] =  "Skipping existing: $packageInfo->title";

        // So the alias exists, let's pull the event id from the database
        $eventId = ClawEvents::getEventId($packageInfo->alias, true);
        if ($eventId != 0) {
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

    return '<p>' . implode('</p><p>', $log) . '</p>';
  }

  public function MetaDiscountBundles(): string
  {
    $log = [];
    $count = 0;

    $eventConfig = new EventConfig($this->eventAlias, []);
    $mainPackages = $eventConfig->packageInfos;

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($mainPackages as $packageInfo) {
      if (
        !is_array($packageInfo->meta)
        || count($packageInfo->meta) == 0
        || $packageInfo->isVolunteer
        || $packageInfo->packageInfoType != PackageInfoTypes::main
        || $packageInfo->published != EbPublishedState::published
      ) {
        continue;
      }

      /** @var \ClawCorpLib\Lib\PackageInfo */
      foreach ($packageInfo->meta as $eventId) {
        // Retrieve the addon PackageInfo since $addon is an Event Booking event id
        $addon = null;

        /** @var \ClawCorpLib\Lib\PackageInfo */
        foreach ($mainPackages as $subPackageInfo) {
          if ($subPackageInfo->eventId == $eventId) {
            $addon = $subPackageInfo;
            break;
          }
        }

        if (is_null($addon)) continue;

        list($success, $log[]) = $this->addDiscountBundle($addon->fee, $packageInfo, $addon);

        if ($success) $count++;
      }
    }

    $log[] = "Updated $count meta discount bundles.";

    return '<p>' . implode('</p><p>', $log) . '</p>';
  }

  public function VolunteerDiscountBundles(): string
  {
    $log = [];
    $count = 0;

    $mainEventConfig = new EventConfig($this->eventAlias, [PackageInfoTypes::main]);
    $mainPackages = $mainEventConfig->packageInfos;

    $addonsEventConfig = new EventConfig($this->eventAlias, [PackageInfoTypes::addon, PackageInfoTypes::combomeal]);
    $addonsPackages = $addonsEventConfig->packageInfos;

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($mainPackages as $packageInfo) {
      if ($packageInfo->published != EbPublishedState::published) {
        continue;
      }

      if (!$packageInfo->isVolunteer) {
        continue;
      }

      if ($packageInfo->eventId == 0) {
        $log[] =  "Skipping Discount Bundle (Event ID 0): $packageInfo->title";
        continue;
      }

      /** @var \ClawCorpLib\Lib\PackageInfo */
      foreach ($addonsPackages as $addon) {
        if ($addon->published != EbPublishedState::published) {
          continue;
        }

        if ($addon->eventId == 0) {
          $log[] =  "Skipping Discount Bundle (Event ID 0): $addon->title";
          continue;
        }

        if ($addon->bundleDiscount == 0) {
          $log[] =  "Skipping Discount Bundle (No Discount): $addon->title";
          continue;
        }

        list($success, $log[]) = $this->addDiscountBundle($addon->bundleDiscount, $packageInfo, $addon);

        if ($success) $count++;
      }
    }

    $log[] = "Updated $count discount bundles.";

    return '<p>' . implode('</p><p>', $log) . '</p>';
  }

  /**
   * Helper function for creating discount bundles among events by $ amount
   * @param array Array of PackageInfo objects
   * @param int Dollar amount
   * @return array [bool success, log message]
   */
  private function addDiscountBundle(int $dollarAmount, \ClawCorpLib\Lib\PackageInfo ...$packageInfos): array
  {
    if (count($packageInfos) < 2) return "Skipping discount bundle: Not enough events";

    $eventIds = [];
    $titles = [];

    foreach ($packageInfos as $packageInfo) {
      if ($packageInfo->eventId == 0) return [false, "Skipping discount bundle: Invalid event ID"];
      $eventIds[] = $packageInfo->eventId;
      $titles[] = $packageInfo->title;
    }

    $db = $this->db;

    // Check for existing discount
    $query = $db->getQuery(true);
    $query->select('discount_id')
      ->from('#__eb_discount_events')
      ->where('event_id IN (' . implode(',', $eventIds) . ')')
      ->group('discount_id')
      ->having('COUNT(DISTINCT event_id) = ' . count($eventIds));
    $db->setQuery($query);
    $result = $db->loadResult();

    if ($result != null) return [false, "Skipping duplicate discount: $result"];

    $title = implode('-', $titles);

    $query = $db->getQuery(true);

    $data = (object)[
      'id' => 0,
      'title' => $title,
      'event_ids' => implode(',', $eventIds),
      'discount_amount' => $dollarAmount,
      'from_date' => $query->nullDate(false),
      'to_date' => $query->nullDate(false),
      'times' => 0,
      'used' => 0,
      'published' => EbPublishedState::published->value,
      'number_events' => 0,
      'discount_type' => 1
    ];

    $result = $db->insertObject('#__eb_discounts', $data, 'id');

    if ($result === false) return [false, "Error adding discount: $title"];

    foreach ($eventIds as $eventId) {
      $discount = (object)[
        'id' => 0,
        'discount_id' => $data->id,
        'event_id' => $eventId
      ];

      $result = $db->insertObject('#__eb_discount_events', $discount, 'id');
      if ($result === false) return [false, "Error adding discount events: $title"];
    }

    return [true, "Added discount: $title (\$$dollarAmount)"];
  }

  /**
   * Sets internal variables for public and registered groups 
   * @return void  */
  private function setDefaultAcls()
  {
    $this->public_acl = Config::getGlobalConfig('packageinfo_public_acl', 0);
    $this->registered_acl = Config::getGlobalConfig('packageinfo_registered_acl', 0);

    if (0 == $this->public_acl || 0 == $this->registered_acl) {
      throw new \Exception('Invalid ACL id');
    }
  }
}
