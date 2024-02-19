<?php

namespace ClawCorpLib\Lib;

use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Enums\PackageInfoTypes;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\Database\DatabaseDriver;

/**
 * Wrapper for #__claw_packages row
 * TODO: Reimplement as a Table class
 *  
 * @package ClawCorpLib\Lib
 * @since 24.4.1
 */
class PackageInfo
{
    public EbPublishedState $published = EbPublishedState::published;
    public string $title = '';
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
    public array|object $meta = []; // JSON

    private DatabaseDriver $db;

    public function __construct(
      public EventInfo $eventInfo,
      public int $id
    ) {
      if ( $this->id == 0 ) {
        throw new \Exception("PackageInfo::__construct() called with no package ID");
      }

      $this->db = Factory::getContainer()->get('DatabaseDriver');

      $this->alias = $eventInfo->alias;
      $this->fromSqlRow();
    }

    public function toSqlObject()
    {
      $result = new \stdClass();

      $result->id = $this->id;
      $result->published = $this->published->value;
      $result->title = $this->title;
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
      $result->start = is_null($this->start) ? $this->db->getNullDate() : $this->start->toSql();
      $result->end = is_null($this->end) ? $this->db->getNullDate() : $this->end->toSql();
      $result->isVolunteer = $this->isVolunteer ? 1 : 0;
      $result->bundleDiscount = $this->bundleDiscount;
      $result->badgeValue = $this->badgeValue;
      $result->couponOnly = $this->couponOnly ? 1 : 0;
      $result->meta = json_encode($this->meta);

      return $result;
    }

    private function fromSqlRow() {
      $db = $this->db;
      $id = $this->id;

      $nullDate = $db->getNullDate();

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
      $this->title = $result->title ?? '';
      $this->description = $result->description ?? '';
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
      $this->start = $result->start === $nullDate ? null : new Date($result->start);
      $this->end = $result->end === $nullDate ? null : new Date($result->end);
      $this->isVolunteer = $result->isVolunteer;
      $this->bundleDiscount = $result->bundleDiscount;
      $this->badgeValue = $result->badgeValue;
      $this->couponOnly = $result->couponOnly;
      $this->meta = json_decode($result->meta);
    }

    public function save(): bool
    {
      $db = $this->db;

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