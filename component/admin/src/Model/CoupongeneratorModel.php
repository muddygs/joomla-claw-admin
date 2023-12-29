<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2022 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\EbCouponAssignments;
use ClawCorpLib\Enums\EbCouponTypes;
use ClawCorpLib\Enums\PackageInfoTypes;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\Coupons;
use ClawCorpLib\Lib\EventConfig;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\Input\Json;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
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
   *
   * @since   1.6
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
   * AJAX Handler
   * Returns the HTML for the package selection dropdown and the addon checkboxes.
   * The JSON encoded results is an array where the first element is the package selection dropdown
   * and the second element is the addon checkboxes.
   * 
   * @param Json $json 
   * @return array 
   */
  public function populateCodeTypes(Json $json): array
  {
    // $groups = array_keys(Helpers::getUserGroupsByName());
    $eventAlias = $json->get('jform[event]', Aliases::current(), 'string');

    $identity = Factory::getApplication()->getIdentity();

    if (!$identity || !$identity->id) {
      return ['', ''];
    } else {
      $groups = $identity->getAuthorisedGroups();
    }

    $e = new EventConfig($eventAlias, []);

    $events = [0 => 'Select Package'];
    $addons = '';

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($e->packageInfos as $event) {
      $c = $event->couponKey;

      if ( count(array_intersect($groups, $event->couponAccessGroups)) > 0 ) {
        if ( $event->couponValue < 1 ) continue;

        if ( $event->packageInfoType == PackageInfoTypes::main || $event->packageInfoType == PackageInfoTypes::coupononly ) {
          $events[$c] = $event->title . ' (' . $c . ') - $' . $event->couponValue;
        } else if ( $event->packageInfoType == PackageInfoTypes::addon ) {
          $description = $event->title . ' (' . $c . ') - $' . $event->couponValue;

          $addons .= <<< HTML
<div class="form-check">
  <input class="form-check-input" type="checkbox" value="$c" id="addon-$c" name="addon-$c" onclick="updateTotalValue()">
  <label class="form-check-label" for="addon-$c">$description</label>
</div>
HTML;
        }
      }
    }

    $packageSelectionHtml = HTMLHelper::_('select.genericlist', $events, 'package');

    return [$packageSelectionHtml, $addons];
  }

  /**
   * AJAX Handler
   * Returns the coupon value for the selected package and addons.
   * @param Json $json Form data
   * @return float Coupon value
   */
  public function couponValue(Json $json): float
  {
    $eventAlias = $json->get('jform[event]', Aliases::current(), 'string');
    $events = new EventConfig($eventAlias, []);

    $package = $json->get('jform[packagetype]', '', 'string');

    if ($package == '') return 0;
  
    // Event
    $value = $events->getEventByCouponCode($package);
    $result = $value == null ? 0 : $value->couponValue;

    // Addons
    if ($result) {
      foreach (array_keys($json->getArray()) as $key) {
        if (substr($key, 0, 6) == 'addon-' && strlen($key) == 7) {
          $a = substr($key, 6, 1);
          $value = $events->getEventByCouponCode($a);
          $result += $value == null ? 0 : $value->couponValue;
        }
      }
    }
  
    return $result;
  }

  /**
   * AJAX Handler
   * Emits HTML (in a table) to display with all the coupons generated.
   * @param Json $json Form data
   * @param bool $allowOverride Form input to ignore duplicate email addresses
   * @return void 
   */
  public function createCoupons(Json $json)
  {
    // Check if we are overriding the email address (emailOverride only exists if the checkbox is checked)
    $allowOverride = false;
    if ( $json->exists('emailOverride') ) {
      $allowOverride = true;
    }

    $emailStatus = $this->emailStatus($json);

    $emails = array_filter(explode("\n", str_replace("\r", "", $json->getString('jform[email]',''))));
    $names = array_filter(explode("\n", str_replace("\r", "", $json->getString('jform[name]',''))));
  
    $emails = array_map('trim', $emails);
    $names = array_map('trim', $names);
  
    $q = $json->getUint('quantity',1);

  ?>
  <h1>Discount Code Results</h1>
  <?php
  
    if ( !$allowOverride && !count($emails) ):
  ?>
  <p class="text-error">Email address must be provided.</p>
  <?php
      return;
    endif;
  
    if ( !$allowOverride && $emailStatus->error )
    {
  ?>
  <p class="text-error">Email address in use. Contact an administrator for assistant.</p>
  <?php
      return;
    }
  
    if ( $allowOverride && !$json->exists('emailOverride') && $emailStatus->error )
    {
  ?>
  <p class="text-error">Email address in use. Select "Ignore email" if you want to override.</p>
  <?php
      return;
    }
  
  
  
    if ( $q != 1 && sizeof($emails) > 1)
    {
  ?>
      <p class="text-error">When specifying multiple emails, only quantity = 1 allowed.</p>
  <?php
      return;
    }
  
    if ( sizeof($emails) != sizeof($names) )
    {
  ?>
      <p class="text-error">Name and email lists must match 1 to 1.</p>
  <?php
      return;
    }
  
  
    $e = new EventConfig($json->getString('jform[event]',Aliases::current()), []);
  
    $value = $this->couponValue($json);
  
    if (0 == $value) return;
  
  ?>
  <div class="table-responsive">
  <table class="table table-dark table-striped table-bordered">
    <thead>
    <tr>
      <th style="padding:3px;">Name</th>
      <th style="padding:3px;">Discount Code</th>
      <th style="padding:3px;">Email</th>
    </tr>
    </thead>
    <tbody>
  <?php
  
    $prefix = '';
    $eventIds = [];

    $packageType = $json->get('jform[packagetype]','');
  
    if ( $packageType != '' ) {
      $prefix .= $packageType;
      $eventIds[] = $e->getEventByCouponCode($packageType)->eventId;
    }
  
    foreach (array_keys($json->getArray()) as $key) {
      if (substr($key, 0, 6) == 'addon-' && strlen($key) == 7) {
        $a = substr($key, 6, 1);
  
        $eventIds[] = $e->getEventByCouponCode($a)->eventId;
        $prefix .= $a;
      }
    }
  
    $admin = Factory::getApplication()->getIdentity()->username;
  
  
    foreach ( $emails AS $i => $email )
    {
      $note = $e->eventInfo->prefix . '_' . $admin . '_' . $names[$i];
  
      $coupon = new Coupons($value, $prefix, $note, $email);
      $coupon->setCouponType(EbCouponTypes::voucher);
      $coupon->setCouponAssignment(EbCouponAssignments::selected_events);
      $coupon->setCouponEventIds($eventIds);
  
      $note = $coupon->getNote();
  
      for ($x = 1; $x <= $q; $x++) {
        if ($q > 1) {
          $coupon->setNote($note . '-' . $x);
        }
  
        $nextCode = $coupon->insertCoupon();
  ?>
      <tr>
        <td style="padding:3px;"><?php echo $names[$i] ?></td>
        <td style="padding:3px;" data-coupon="<?php echo $nextCode ?>"><?php echo $nextCode ?></td>
        <td style="padding:3px;"><?php echo $email ?></td>
      </tr>
  <?php
      }
    }
  ?>
    </tbody>
    </table>
  </div>
  <?php
  }

  /**
   * AJAX Handler
   * Returns a error/message object after accessing email input requirements
   * 
   * @param Json $json 
   * @return object 
   */
  public function emailStatus(Json $json): object
  {
    $result = (object)[
      'error' => false,
      'msg' => ''
    ];

    if ( !$json->exists('jform[email]') || trim($json->getString('jform[email]','')) == '')
    {
      $result->error = true;
      $result->msg = 'Email address must be provided.';
      
      return $result;
    }
  
    $emails = array_filter(explode("\n", str_replace("\r", "", $json->getString('jform[email]',''))));
    $emails = array_map('trim', $emails);
    $regex = ':' . implode('|:', $emails );
  
    $db = $this->getDatabase();
    $query = $db->getQuery(true);
    $query->select('id,code,used,note')
      ->from('#__eb_coupons')
      ->where('published=1')
      ->where('note REGEXP '.$db->quote($regex))
      ->setLimit(10);
    $db->setQuery($query);
    $coupons = $db->loadObjectList('id');
  
    /* Cases:
       - No coupon found
       - Coupon found, unused and not assigned to an event
       - Coupon for current registration used or unused
    */
  
    if ( $coupons == null || sizeof($coupons) == 0 )
    {
      $result->error = false;
      $result->msg = 'No coupon(s) found by email(s).';
      return $result;
    }
  
    $eventSelection = $json->getString('jform[event]','');
  
    if ( !in_array($eventSelection, Aliases::active()) )
    {
      $result->msg = 'Please select an event.';
      return $result;
    }
  
    $events = new EventConfig($eventSelection);
    $mainEventIds = $events->getMainEventIds();

    $query = $db->getQuery(true);
    $query->select('*')
      ->from('#__eb_coupon_events')
      ->where('event_id IN ('.implode(',', $mainEventIds).')');
    $db->setQuery($query);
    $eventAssignments = $db->loadObjectList();
  
    if ( $eventAssignments == null || sizeof($eventAssignments) == 0 ) {
      $result->msg = 'Coupon found but not assigned to a specific main event';
      return $result;
    }
  
    foreach ( $coupons AS $c ) {
      $email = explode(':',$c->note)[1];
  
      foreach ( $eventAssignments AS $e ) {
        // Main event?
        if ( !in_array($e->event_id, $mainEventIds) ) continue;
  
        // Is main event. Used already?
        if ( $e->coupon_id == $c->id ) {
          $result->error = true;
  
          if ( $c->used > 0 ) {
            $result->msg .= $c->code.' for '.$email.' has been used<br>';
          } else {
            $result->msg .= 'Unused coupon '.$c->code.' exists for '.$email.'<br>';
          }
        }
      }
    }
  
    return $result;
  }
}
