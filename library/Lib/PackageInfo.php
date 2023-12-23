<?php

namespace ClawCorpLib\Lib;

use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Enums\PackageInfoTypes;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;

/**
 * Wrapper for #__claw_packages table
 *  
 * @package ClawCorpLib\Lib
 * @since 24.4.1
 */
class PackageInfo
{
    public EbPublishedState $published = EbPublishedState::published;
    public string $description = '';
    public string $alias = '';
    public EventPackageTypes $eventPackageType = EventPackageTypes::none;
    public PackageInfoTypes $packageInfoType = PackageInfoTypes::none;
    public string $couponKey = '';
    public float $couponValue = 0.0;
    public float $fee = 0.0;
    public int $eventId = 0;
    public int $category = 0;
    public int $minShifts = 0;
    public bool $requiresCoupon = false;
    public array $couponAccessGroups = []; // JSON
    public bool $authNetProfile = false;
    public ?Date $start = null;
    public ?Date $end = null;
    public bool $isVolunteer = false;
    public int $bundleDiscount = 0;
    public string $badgeValue = '';
    public bool $couponOnly = false;
    public array $meta = []; // JSON

    public function __construct(
      public EventInfo $eventInfo,
      public int $id = 0
    ) {
      $this->alias = $eventInfo->alias;

      if ( $id > 0 ) {
        $this->loadPackageInfo($id);
      }
    }

    public function toSqlObject()
    {
      $result = new \stdClass();

      $result->id = $this->id;
      $result->published = $this->published->value;
      $result->description = $this->description;
      $result->alias = $this->alias;
      $result->eventPackageType = $this->eventPackageType->value;
      $result->packageInfoType = $this->packageInfoType->value;
      $result->couponKey = $this->couponKey;
      $result->couponValue = $this->couponValue;
      $result->fee = $this->fee;
      $result->eventId = $this->eventId;
      $result->category = $this->category;
      $result->minShifts = $this->minShifts;
      $result->requiresCoupon = $this->requiresCoupon ? 1 : 0;
      $result->couponAccessGroups = json_encode($this->couponAccessGroups);
      $result->authNetProfile = $this->authNetProfile ? 1 : 0;
      $result->start = $this->start->toSql();
      $result->end = $this->end->toSql();
      $result->isVolunteer = $this->isVolunteer ? 1 : 0;
      $result->bundleDiscount = $this->bundleDiscount;
      $result->badgeValue = $this->badgeValue;
      $result->couponOnly = $this->couponOnly ? 1 : 0;
      $result->meta = json_encode($this->meta);

      return $result;
    }

    public function loadPackageInfo(int $id) {
      $db = Factory::getContainer()->get('DatabaseDriver');

      $query = $db->getQuery(true);
      $query->select('*')
        ->from('#__claw_packages')
        ->where('id = :id')
        ->bind(':id', $id);
      $db->setQuery($query);
      $result = $db->loadObject();

      if ( $result == null ) return;

      $this->id = $result->id;
      $this->published = EbPublishedState::from($result->published);
      $this->description = $result->description;
      $this->alias = $result->alias;
      $this->eventPackageType = EventPackageTypes::FindValue($result->eventPackageType);
      $this->packageInfoType = PackageInfoTypes::FindValue($result->packageInfoType);
      $this->couponKey = $result->couponKey;
      $this->couponValue = $result->couponValue;
      $this->fee = $result->fee;
      $this->eventId = $result->eventId;
      $this->category = $result->category;
      $this->minShifts = $result->minShifts;
      $this->requiresCoupon = $result->requiresCoupon;
      $this->couponAccessGroups = json_decode($result->couponAccessGroups);
      $this->authNetProfile = $result->authNetProfile;
      $this->start = new Date($result->start);
      $this->end = new Date($result->end);
      $this->isVolunteer = $result->isVolunteer;
      $this->bundleDiscount = $result->bundleDiscount;
      $this->badgeValue = $result->badgeValue;
      $this->couponOnly = $result->couponOnly;
      $this->meta = json_decode($result->meta);
    }

    public function getCartLink(string $class = '', string $baseURL = '/claw-all-events'): string
    {
      if ( 0 == $this->id || 0 == $this->eventId ) {
        die("PackageInfo::getCartLink() called with no package/event ID");
      }

      if ('' == $class) {
        $class = 'btn btn-primary eb-register-button eb-colorbox-addcart cboxElement';
      }
  
      $pt = microtime(true);
      $eid = $this->eventId;
  
      $url = <<< HTML
          <a class="$class" href="$baseURL?task=cart.add_cart&id=$eid&pt=$pt">$this->description</a>
  HTML;
      return $url;
    }

    public function getEventRow(): ?object
    {
      if ( 0 == $this->id || 0 == $this->eventId) {
        die("PackageInfo::getCartLink() called with no package/event ID");
      }

      $db = Factory::getContainer()->get('DatabaseDriver');
      $query = $db->getQuery(true);
      $query->select('*')
        ->from('#__eb_events')
        ->where('id = :id')
        ->bind(':id', $this->eventId);
      $db->setQuery($query);
  
      $results = $db->loadObject();
  
      return $results;
    }

    public function save(): bool
    {
      $db = Factory::getContainer()->get('DatabaseDriver');

      $data = $this->toSqlObject();

      if ( $this->id == 0 ) {
        $db->insertObject('#__claw_packages', $data);
        $this->id = $db->insertid();
      } else {
        $db->updateObject('#__claw_packages', $data, 'id');
      }

      return true;
    }
}