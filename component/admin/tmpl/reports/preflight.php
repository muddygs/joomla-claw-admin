<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
\defined('_JEXEC') or die('Restricted Access');


/* Tries to parse all the current event registrations to find errors */

use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Enums\PackageInfoTypes;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\Checkin;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Lib\Registrant;
use Joomla\CMS\Factory;
use Joomla\CMS\User\UserHelper;
use Joomla\Database\DatabaseDriver;

\ClawCorpLib\Helpers\Bootstrap::rawHeader([], ['/media/com_claw/css/print_letter.css']);

$eventAlias = Aliases::current(true);
$clawEvents = new EventConfig($eventAlias);
$packageInfos = $clawEvents->packageInfos;
$eventInfo = $clawEvents->eventInfo;

$mealPhrases = ['beef', 'chicken', 'vege', 'vegan', 'sea bass', 'fish', 'ravioli'];
$shirtSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', 'None'];

$db = Factory::getContainer()->get('DatabaseDriver');

// start script timer
$start = microtime(true);



// echo "<pre>Stage 1: Mem usage is: ", memory_get_usage(), "\n</pre>";


?>
<h1 class="text-center"><?= $eventInfo->description ?> Pre-Flight Registration Check</h1>
<?php
$mainEventIds = [];

/** @var \ClawCorpLib\Lib\PackageInfo */
foreach ($packageInfos as $packageInfo) {
  if (
    $packageInfo->packageInfoType != PackageInfoTypes::main ||
    $packageInfo->published != EbPublishedState::published ||
    $packageInfo->eventId == 0
  ) continue;

  $mainEventIds[] = $packageInfo->eventId;

  if ($packageInfo->eventId == 0) {
    dd($packageInfo);
  }

  echo "<h2>Checking {$packageInfo->title} ({$packageInfo->eventId})</h2>\n";
  $query = $db->getQuery(true);
  $query->select(['id', 'user_id'])
    ->from($db->qn('#__eb_registrants'))
    ->where($db->qn('event_id') . '=' . $db->q($packageInfo->eventId))
    ->where($db->qn('published') . '=' . $db->q(EbPublishedState::published->value))
    ->order($db->qn('id'));

  $db->setQuery($query);
  $registrantIds = $db->loadObjectList();

  echo "<pre>Count: " . count($registrantIds) . "</pre>\n";

  $output = (object)[
    'id' => 0,
    'errors' => [],
    'regid' => '',
    'registration_code' => '',
    'shifts' => 0,
  ];

?>
  <table class="table table-striped table-bordered">
    <thead class="thead-dark">
      <tr>

        <?php
        foreach ($output as $key => $value) {
        ?>
          <th><?= $key ?></th>
        <?php
        }
        ?>
      </tr>
    </thead>
    <tbody>
      <?php

      foreach ($registrantIds as $row) {
        // echo "<pre>Registrant Ids Loop: Mem usage is: ", memory_get_usage(), "\n</pre>";

        $output = (object)[
          'id' => $row->id,
          'errors' => '',
          'regid' => '',
          'registration_code' => '',
          'shifts' => 0,
        ];

        if ($row->user_id == 0) {
          $output->errors = "<span class=\"text-danger\">User ID is 0</span>";
          dataRow($output);
          continue;
        }

        $registrant = new Registrant($eventAlias, $row->user_id);

        try {
          $registrant->loadCurrentEvents();
        } catch (Exception $e) {
          $output->errors = "Error loading current events";
          dataRow($output);
          continue;
        }

        // Try record checking

        // Load raw record
        $record = Registrant::loadRegistrantRow($row->id);
        $invoice = explode('-', $record->invoice_number);
        if (count($invoice) < 2) {
          $output->errors = "<span class=\"text-danger\">Invalid invoice number</span>";
          dataRow($output);
          continue;
        }

        if (stristr($invoice[0], $eventInfo->prefix) === false) {
          $output->errors = "<span class=\"text-danger\">Invalid invoice number: {$record->invoice_number}</span>";
          dataRow($output);
          continue;
        }

        $baseInvoice = implode('-', [$invoice[0], $invoice[1]]);

        try {
          $checkin = new Checkin($baseInvoice, false);
        } catch (\Exception $e) {
          $output->errors = '<span class="text-danger">' . $e->getMessage() . '</span>';
          dataRow($output);
          continue;
        }

        $output->regid = implode('<br/>', [$registrant->badgeId, $checkin->r->legalName, $checkin->r->email]);

        $output->shifts = '<pre>' . $checkin->r->shifts . '</pre>';

        if (! $checkin->isValid && $checkin->getUid() == 0) {
          $output->errors = "<span class=\"text-danger\">Checkin record not found</span>";
          dataRow($output);
          continue;
        }

        $errors = [];

        $output->registration_code = $checkin->r->registration_code;

        // If $output->registration_code is > 10 characters, generate a new code
        if (strlen($output->registration_code) > 10) {
          $oldcode = $output->registration_code;
          $output->registration_code = getUniqueCodeForRegistrationRecord($db);
          updateRegistrationCode($db, $oldcode, $output->registration_code);

          $errors[] = "<span class=\"text-info\">Registration code too long, generated new code</span>";
        }

        $output->registration_code = "<pre>{$output->registration_code}</pre>";

        /** @var \ClawCorpLib\Lib\RegistrantRecord */
        foreach ($registrant->records() as $record) {
          if (!str_starts_with($record->registrant->invoice_number, $eventInfo->prefix)) {
            $errors[] = "<span class=\"text-danger\">Invoice mismatch {$record->registrant->invoice_number}</span><br/>";
          }
        }

        if ($checkin->r->error != '') {
          // put text-danger around checkin errors first
          $checkinErrors = explode("\n", $checkin->r->error);
          $checkinErrors = array_map(function ($e) {
            return str_starts_with($e, 'Badge not') ? $e : "<span class=\"text-danger\">{$e}</span>";
          }, $checkinErrors);
          $errors = array_merge($errors, $checkinErrors);
        }

        // Dinner selection valid?
        reset($checkin->r->meals[$eventInfo->eb_cat_dinners]);
        $meal = current($checkin->r->meals[$eventInfo->eb_cat_dinners]);

        if ($meal != '') {
          $mealPhraseFound = false;
          foreach ($mealPhrases as $m) {
            if (stristr($meal, $m) !== false) {
              $mealPhraseFound = true;
              break;
            }
          }

          if (!$mealPhraseFound) {
            $errors[] = "<span class=\"text-danger\">Invalid dinner selection({$meal})</span>";
          } else {
            $errors[] = "<span class=\"text-success\">Dinner: {$meal}</span>";
          }
        }

        if (!in_array($checkin->r->shirtSize, $shirtSizes)) {
          $errors[] = "<span class=\"text-danger\">Invalid shirt size({$checkin->r->shirtSize})</span>";
        }

        $output->errors = implode('<br/>', $errors);

        if (!$checkin->isValid) {
          dataRow($output);
          continue;
        }

        // Valid record dump
        dataRow($output);
      }

      ?>
    </tbody>
  </table>
<?php
}

// echo "<pre>Post Loop: Mem usage is: ", memory_get_usage(), "\n</pre>";

// Now check for duplicate user ids across main events
$query = $db->getQuery(true);
$query->select(['user_id'])
  ->from($db->qn('#__eb_registrants'))
  ->where($db->qn('event_id') . ' IN (' . implode(',', $mainEventIds) . ')')
  ->where($db->qn('published') . '=' . $db->q(EbPublishedState::published->value))
  ->group($db->qn('user_id'))
  ->having('COUNT(*) > 1');
$db->setQuery($query);
$duplicateUserIds = $db->loadColumn();

if (count($duplicateUserIds) > 0) {
  echo "<h2 class=\"text-danger\">Duplicate User IDs</h2>\n";
  echo "<pre>" . implode("\n", $duplicateUserIds) . "</pre>\n";
} else {
  echo "<h2 class=\"text-success\">No duplicate user ids found</h2>\n";
}

// Verify all Event Booking events exist and that they are configured the same
?>
<h2>Event Booking Events</h2>
<table class="table table-striped table-bordered">
  <thead class="thead-dark">
    <tr>
      <td>Event ID</td>
      <td>Title</td>
      <td>Coupon Key</td>
      <td>Published</td>
      <td>Fee</td>
    </tr>
  </thead>
  <tbody>
    <?php

    // Tracking for duplicate coupon keys
    $couponKeys = [];

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($packageInfos as $packageInfo) {
      $eventRow = (object)[];

      // Load the EventBooking database item for this event
      if ($packageInfo->eventId == 0) {
        $eventRow->title = "<span class=\"text-danger\">{$packageInfo->title} Not deployed</span>";
        $eventRow->published = EbPublishedState::any;
      } else {
        $eventRow = ClawEvents::loadEventRow($packageInfo->eventId);

        if ($packageInfo->couponKey != '') $couponKeys[] = $packageInfo->couponKey;

        if (property_exists($packageInfo, 'fee') && $packageInfo->fee != $eventRow->individual_price) {
          $fee = "<span class=\"text-danger\">{$packageInfo->fee} != {$eventRow->individual_price}</span>";
        } else {
          $fee = $packageInfo->fee ?? 0;
        }
      }

      if ($packageInfo->published != EbPublishedState::published) {
        $eventRow->published = EbPublishedState::any;
      }


      $published = $eventRow->published == EbPublishedState::published->value ?
        'PUBLISHED' :
        "<span class=\"text-danger\">UNPUBLISHED</span>";

    ?>
      <tr>
        <td><?= $packageInfo->eventId ?></td>
        <td><?= $eventRow->title ?></td>
        <td><?= $packageInfo->couponKey ?></td>
        <td><?= $published ?></td>
        <td><?= $fee ?></td>
      </tr>
    <?php
    }
    ?>
  </tbody>
</table>
<?php

// Check for duplicate coupon values
$duplicateCouponKeys = array_unique(array_diff_assoc($couponKeys, array_unique($couponKeys)));

if (count($duplicateCouponKeys) > 0) {
  echo "<h2 class=\"text-danger\">Duplicate Coupon Keys</h2>\n";
  echo "<pre>" . implode("\n", $duplicateCouponKeys) . "</pre>\n";
} else {
  echo "<h2 class=\"text-success\">No duplicate coupon keys found</h2>\n";
}

// script run time
$end = microtime(true);
$elapsed = $end - $start;
echo "<h2>Script run time: {$elapsed} seconds</h2>\n";

\ClawCorpLib\Helpers\Bootstrap::rawFooter();

function dataRow($output)
{
?>
  <tr>
    <?php
    foreach ($output as $value) {
    ?>
      <td><?= $value ?></td>
    <?php
    }
    ?>
  </tr>
<?php
}

/**
 * Method to get unique code for a field in #__eb_registrants table
 *
 * @param   string  $fieldName
 * @param   int     $length
 *
 * @return string
 */
function getUniqueCodeForRegistrationRecord(DatabaseDriver $db, $fieldName = 'registration_code', $length = 10)
{
  $query = $db->getQuery(true);

  while (true) {
    $uniqueCode = UserHelper::genRandomPassword($length);
    $query->clear()
      ->select('COUNT(*)')
      ->from('#__eb_registrants')
      ->where($db->quoteName($fieldName) . ' = ' . $db->quote($uniqueCode));
    $db->setQuery($query);
    $total = $db->loadResult();

    if (!$total) {
      break;
    }
  }

  return $uniqueCode;
}

function updateRegistrationCode(DatabaseDriver $db, $oldcode, $newcode)
{
  $query = $db->getQuery(true);
  $query->update($db->qn('#__eb_registrants'))
    ->set($db->qn('registration_code') . '=' . $db->q($newcode))
    ->where($db->qn('registration_code') . '=' . $db->q($oldcode));
  $db->setQuery($query);
  $db->execute();
}
