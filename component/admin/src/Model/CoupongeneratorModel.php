<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\EbCouponAssignments;
use ClawCorpLib\Enums\EbCouponTypes;
use ClawCorpLib\Enums\PackageInfoTypes;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\Coupons;
use ClawCorpLib\Lib\EventConfig;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\FormModel;

class CoupongeneratorModel extends FormModel
{
  /**
   * The prefix to use with controller messages.
   *
   * @var    string
   * @since  1.6
   */
  protected $text_prefix = 'COM_CLAW_COUPONGENERATOR';

  /**
   * Method to get the record form.
   *
   * @param   array    $data      Data for the form.
   * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
   *
   * @return  Form|boolean  A Form object on success, false on failure
   */
  public function getForm($data = [], $loadData = false)
  {
    // Get the form.
    $form = $this->loadForm('com_claw.coupongenerator', 'coupongenerator', ['control' => 'jform', 'load_data' => $loadData]);

    if (empty($form)) {
      return false;
    }

    return $form;
  }


  /**
   * Returns array of authorized packages
   * 
   * @param array $input of jform data
   * @return array [packageInfoId => [title => string]] 
   */
  public function packageOptions(array $input): array
  {
    // TODO: Validate?
    $eventAlias = $input['event'] ?? Aliases::current();

    $identity = Factory::getApplication()->getIdentity();

    if (!$identity || !$identity->id) {
      return [];
    }

    $groups = $identity->getAuthorisedGroups();
    $e = new EventConfig($eventAlias, []);

    $options = [];

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($e->packageInfos as $event) {
      if (count(array_intersect($groups, $event->couponAccessGroups)) > 0) {
        if ($event->couponValue < 1) continue;

        if ($event->packageInfoType == PackageInfoTypes::main || $event->packageInfoType == PackageInfoTypes::coupononly) {
          $options[$event->id] = $event->title . ' (' . $event->couponKey . ') - $' . $event->couponValue;
        }
      }
    }

    return $options;
  }

  /**
   * HTMX Handler
   * Returns array of objects for the addon checkboxes. [id] => {code, description}
   * 
   * @param array $input 
   * @return array
   */
  public function addonCheckboxes(array $input): array
  {
    $eventAlias = $input['event'] ?? Aliases::current();

    $identity = Factory::getApplication()->getIdentity();

    if (!$identity || !$identity->id) {
      return '';
    }

    $groups = $identity->getAuthorisedGroups();
    $e = new EventConfig($eventAlias, []);

    $addons = [];

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($e->packageInfos as $event) {
      $c = $event->couponKey;

      if (count(array_intersect($groups, $event->couponAccessGroups)) > 0) {
        if ($event->couponValue < 1) continue;

        if ($event->packageInfoType == PackageInfoTypes::addon) {
          $description = $event->title . ' (' . $c . ') - $' . $event->couponValue;

          $addons[$event->id] = (object)[
            'description' => $description,
            'code' => $c,
          ];
        }
      }
    }

    return $addons;
  }

  /**
   * HTMX Handler
   * Returns the coupon value for the selected package and addons.
   * @param array $input Form data
   * @return float Coupon value
   */
  public function couponValueFloat(array $input): float
  {
    $eventAlias = $input['event'] ?? Aliases::current();
    $eventConfig = new EventConfig($eventAlias, []);
    $package = (int)$input['packageid'] ?? 0;

    if (!$package) return 0;

    // Event
    $value = $eventConfig->packageInfos[$package] ?? null;
    $result = is_null($value) ? 0 : $value->couponValue;

    // Addons
    if ($result) {
      foreach ($input as $key => $packageId) {
        if (str_starts_with($key, 'addon-') && strlen($key) === 7) {
          $packageId = (int)$packageId;
          if ($packageId > 0) {
            $value = $eventConfig->packageInfos->get($packageId);
            $result += $value == null ? 0 : $value->couponValue;
          }
        }
      }
    }

    return round($result, 2);
  }


  /**
   * HTMX Handler
   * Object with error (bool), message (string), and array of name/code/email objects (on no error)
   * @param array $input Form data
   * @return object Coupon generation results 
   */
  public function createCoupons(array $input): object
  {
    $eventAlias = $input['event'] ?? '';

    $result = (object)[
      'error' => false,
      'msg' => '',
      'coupons' => []
    ];

    try {
      $eventConfig = new EventConfig($eventAlias, []);
    } catch (\Exception) {
      $result->error = true;
      $result->msg = 'Invalid event selection.';
      return $result;
    }

    $packageId = (int)$input['packageid'];
    /** @var \ClawCorpLib\Lib\PackageInfo */
    $packageInfo = $eventConfig->getPackageInfoByProperty('id', $packageId, false);

    if (is_null($packageInfo)) {
      $result->error = true;
      $result->msg = 'Invalid package selection.';
      return $result;
    }

    $value = $this->couponValueFloat($input);

    if (0 == $value) {
      $result->error = true;
      $result->msg = 'Coupon value cannot be zero.';
      return $result;
    }

    // Check if we are overriding the email address (emailOverride only exists if the checkbox is checked)
    $allowOverride = false;
    if ($this->emailOverride() && ($input['emailOverride'] ?? 0) == 1) {
      $allowOverride = true;
    }

    $emailStatus = $this->emailStatus($input);
    $quantity = (int)$input['quantity'];

    $emailError = $allowOverride ? '' : match (true) {
      $quantity != 1 && sizeof($emailStatus->emails) > 1 => 'When specifying multiple emails, only quantity = 1 allowed.',
      $emailStatus->error => $emailStatus->msg,
      default => ''
    };

    if ($emailError) {
      $result->error = true;
      $result->msg = $emailError;
      return $result;
    }

    $eventIds = [];

    $prefix = $packageInfo->couponKey;

    // If a coupon only package, we need to get the linked event id
    if ($packageInfo->packageInfoType == PackageInfoTypes::coupononly) {
      $otherEvent = $eventConfig->getPackageInfoByProperty('eventPackageType', $packageInfo->eventPackageType);
      $eventIds[] = $otherEvent->eventId;
    } else {
      $eventIds[] = $packageInfo->eventId;
    }

    foreach ($input as $key => $packageId) {
      if (!str_starts_with($key, 'addon-') || strlen($key) != 7) continue;

      $packageInfoAddon = $eventConfig->getPackageInfoByProperty('id', (int)$packageId, false);

      if (is_null($packageInfoAddon)) continue;

      $eventIds[] = $packageInfoAddon->eventId;
      $prefix .= $packageInfoAddon->couponKey;
    }

    $admin = Factory::getApplication()->getIdentity()->username;

    foreach ($emailStatus->emails as $i => $email) {
      $note = $eventConfig->eventInfo->prefix . '_' . $admin . '_' . $emailStatus->names[$i];

      $coupon = new Coupons($value, $prefix, $note, $email);
      $coupon->setCouponType(EbCouponTypes::voucher);
      $coupon->setCouponAssignment(EbCouponAssignments::selected_events);
      $coupon->setCouponEventIds($eventIds);

      $note = $coupon->getNote();

      for ($x = 1; $x <= $quantity; $x++) {
        if ($quantity > 1) {
          $coupon->setNote($note . '-' . $x);
        }

        $nextCode = $coupon->insertCoupon();

        $result->coupons[] = (object)[
          'name' => $emailStatus->names[$i],
          'code' => $nextCode,
          'email' => $email,
        ];
      }
    }

    return $result;
  }

  /**
   * Check if the user has permission to override email address validation
   * @return bool 
   */
  public function emailOverride(): bool
  {
    $app = Factory::getApplication();
    $user  = $app->getIdentity();
    return $user->authorise('core.admin', 'com_claw');
  }

  /**
   * Returns an error/message object after assessing email input requirements
   * 
   * @param array $input 
   * @return object 
   */
  public function emailStatus(array &$input): object
  {
    $result = (object)[
      'error' => false,
      'msg' => '',
      'names' => [],
      'emails' => [],
    ];

    if (!array_key_exists('owner-fields', $input)) {
      $result->error = true;
      $result->msg = 'Malformed HTML.';

      return $result;
    }

    $eventSelection = $input['event'] ?? '';

    if (!in_array($eventSelection, EventConfig::getActiveEventAliases())) {
      $result->msg = 'Please select an event.';
      return $result;
    }

    $emails = [];
    $names = [];

    if (array_key_exists('htmxChangedField', $input)) {
      // Convert "jform[owner-fields][owner-fields0][owner_email]" to "owner-fields0"
      $key = explode('][', $input['htmxChangedField'])[1];
      $email = trim($input['owner-fields'][$key]['owner_email']);

      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $result->error = true;
        return $result;
      }

      $names[] = trim($input['owner-fields'][$key]['owner_name']);
      $emails[] = $email;
    } else {
      foreach (array_keys($input['owner-fields']) as $key) {
        $email = trim($input['owner-fields'][$key]['owner_email']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $result->error = true;
          $result->msg = 'Invalid email address: ' . $email;
          return $result;
        }

        $names[] = trim($input['owner-fields'][$key]['owner_name']);
        $emails[] = $email;
      }
    }

    // Markup in the coupon note field
    $regex = ':' . implode('|:', $emails);

    $db = $this->getDatabase();
    $query = $db->getQuery(true);
    $query->select('id,code,used,note')
      ->from('#__eb_coupons')
      ->where('published=1')
      ->where('note REGEXP ' . $db->quote($regex))
      ->setLimit(10);
    $db->setQuery($query);
    $coupons = $db->loadObjectList('id');

    /* Cases:
       - No coupon found
       - Coupon found, unused and not assigned to an event
       - Coupon for current registration used or unused
    */

    if ($coupons == null || empty($coupons)) {
      $result->msg = '<p class="text-info">No coupon(s) found by email(s).</p>';
      $result->emails = $emails;
      $result->names = $names;
      return $result;
    }

    $events = new EventConfig($eventSelection);
    $mainEventIds = $events->getMainEventIds();

    if (empty($mainEventIds)) {
      $result->error = true;
      $result->msg = '<p class="text-danger">No main packages found. Cannot validate emails.</p>';
      return $result;
    }

    $query = $db->getQuery(true);
    $query->select('*')
      ->from('#__eb_coupon_events')
      ->where('event_id IN (' . implode(',', $mainEventIds) . ')');
    $db->setQuery($query);
    $eventAssignments = $db->loadObjectList();

    if ($eventAssignments == null || sizeof($eventAssignments) == 0) {
      $result->msg = '<p class="text-warning">Coupon found but not assigned to a specific main event.</p>';
      return $result;
    }

    foreach ($coupons as $c) {
      $email = explode(':', $c->note)[1];

      foreach ($eventAssignments as $e) {
        // Main event?
        if (!in_array($e->event_id, $mainEventIds)) continue;

        // Is main event. Used already?
        if ($e->coupon_id == $c->id) {
          $result->error = true;

          if ($c->used > 0) {
            $result->msg .= $c->code . ' for ' . $email . ' has been used<br>';
          } else {
            $result->msg .= 'Unused coupon ' . $c->code . ' exists for ' . $email . '<br>';
          }
        }
      }
    }

    if (!$result->error) {
      $result->msg = '<p class="text-info">Email(s) validated.</p>';
    }
    $result->emails = $emails;
    $result->names = $names;

    return $result;
  }
}
