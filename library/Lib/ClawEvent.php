<?php

namespace ClawCorpLib\Lib;

\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use ClawCorpLib\Enums\EventPackageTypes;

class ClawEvent
{
  public string $description = '';
  public string $alias = '';
  public EventPackageTypes $eventPackageType = EventPackageTypes::none;
  public bool $isMainEvent = false;
  public string $couponKey = '';
  public int $couponValue = 0;
  public int $fee = 0;
  public int $eventId = 0;
  public int $category = 0;
  public int $minShifts = 0;
  public bool $requiresCoupon = false;
  public array $couponAccessGroups = [];
  public bool $isAddon = false;
  public bool $authNetProfile = false;
  public string $start = '';
  public string $end = '';
  public bool $isVolunteer = false;
  public int $bundleDiscount = 0;
  public string $badgeValue = '';
  public bool $couponOnly = false;
  public array $meta = []; // Used for combo events as a list of event ids

  // TODO: Rewrite with named parameters and remove above public properties
  public function __construct(object $e, bool $quiet = false)
  {
    $requiredKeys = [
      'alias',
      'category',
      'eventPackageType',
      'couponAccessGroups',
      'couponValue',
      'description',
      'fee',
      'isMainEvent',
      'requiresCoupon',
    ];

    $optionalKeys = [
      'authNetProfile',
      'badgeValue',
      'bundleDiscount',
      'couponKey',
      'couponOnly',
      'end',
      'isAddon',
      'isVolunteer',
      'start',
      'meta'
    ];

    foreach ($requiredKeys as $k) {
      if (!property_exists($e, $k)) {
        var_dump($e);
        die("ClawEvent definition failed on $k");
      }

      if ( 'category' == $k && gettype($e->$k) != 'integer') {
        $e->$k = ClawEvents::getCategoryId($e->$k);
      }

      $this->$k = $e->$k;
    }

    foreach ($optionalKeys as $k) {
      if (property_exists($e, $k)) $this->$k = $e->$k;
    }

    if ($this->isMainEvent) {
      if (!property_exists($e, 'minShifts'))
        die("ClawEvent definition failed on minShifts");
      if (gettype($e->minShifts) != 'integer')
        die("ClawEvent definition failed on minShifts (not integer)");

      $this->minShifts = $e->minShifts;
    }

    $this->eventId = ClawEvents::getEventIdByAlias($this->alias, $quiet);
  }

  public function getEventRow(): ?object
  {
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

  public function getCartLink(string $class = '', string $baseURL = '/claw-all-events'): string
  {
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
}
