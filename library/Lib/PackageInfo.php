<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Lib;

use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Enums\PackageInfoTypes;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\Database\DatabaseDriver;

/**
 * Wrapper for #__claw_packages row
 *  
 * @package ClawCorpLib\Lib
 * @since 24.4.1
 */
class PackageInfo
{
  public EbPublishedState $published = EbPublishedState::published;
  public string $title = '';
  public string $description = '';
  public string $eventAlias = '';
  public string $alias = '';
  public EventPackageTypes $eventPackageType = EventPackageTypes::none;
  public PackageInfoTypes $packageInfoType = PackageInfoTypes::none;
  public int $acl_id = 0;
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
  public bool $badgeOverride = false;
  public ?Date $mtime = null;

  private DatabaseDriver $db;

  public function __construct(
    public int $id
  ) {
    if ($this->id == 0) {
      throw new \Exception("PackageInfo::__construct() called with no package ID");
    }

    $this->db = Factory::getContainer()->get('DatabaseDriver');

    $this->fromSqlRow();
  }

  private function toSqlObject()
  {
    $result = new \stdClass();

    $result->id = $this->id;
    $result->published = $this->published->value;
    $result->eventAlias = $this->eventAlias;
    $result->title = $this->title;
    $result->description = $this->description;
    $result->alias = $this->alias;
    $result->eventPackageType = $this->eventPackageType->value;
    $result->packageInfoType = $this->packageInfoType->value;
    $result->group_id = $this->acl_id;
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
    $result->mtime = (new Date())->toSql();

    return $result;
  }

  private function fromSqlRow()
  {
    $nullDate = $this->db->getNullDate();

    $query = $this->db->getQuery(true);
    $query->select('*')
      ->from('#__claw_packages')
      ->where('id = :id')
      ->bind(':id', $this->id);
    $this->db->setQuery($query);
    $result = $this->db->loadObject();

    if ($result == null) {
      throw new \RuntimeException("PackageInfo $this->id does not exist.");
    }

    $this->id = $result->id;
    $this->published = EbPublishedState::tryFrom($result->published) ?? EbPublishedState::any;
    $this->eventAlias = $result->eventAlias;
    $this->title = $result->title ?? '';
    $this->description = $result->description ?? '';
    $this->alias = $result->alias;
    $this->eventPackageType = EventPackageTypes::FindValue($result->eventPackageType);
    $this->packageInfoType = PackageInfoTypes::FindValue($result->packageInfoType);
    $this->acl_id = $result->group_id; // TODO: update db schema
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
    $this->meta = json_decode($result->meta) ?? [];
  }

  public function save(): bool
  {
    $data = $this->toSqlObject();
    return $this->db->updateObject('#__claw_packages', $data, 'id');
  }
}
