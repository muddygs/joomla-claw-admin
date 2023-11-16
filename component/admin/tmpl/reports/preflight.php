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
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\Checkin;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Lib\Registrant;
use Joomla\CMS\Factory;
use Joomla\CMS\User\UserHelper;
use Joomla\Database\DatabaseDriver;

\ClawCorpLib\Helpers\Bootstrap::rawHeader([], ['/media/com_claw/css/print_letter.css']);

$eventAlias = Aliases::current();
$clawEvents = new ClawEvents($eventAlias);
$events = $clawEvents->getEvents();
$eventInfo = $clawEvents->getEvent()->getInfo();

$mealPhrases = ['beef','chicken','vege', 'vegan', 'sea bass', 'fish', 'ravioli'];
$shirtSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', 'None'];

$db = Factory::getContainer()->get('DatabaseDriver');

?>
<h1 class="text-center"><?= $eventInfo->description ?> Pre-Flight Registration Check</h1>
<?php
$mainEventIds = [];

/** @var \ClawCorpLib\Lib\ClawEvent */
foreach ( $events AS $event )
{
  if (!$event->isMainEvent ) continue;

  $mainEventIds[] = $event->eventId;

  echo "<h2>Checking {$event->description} ({$event->eventId})</h2>\n";
  $query = $db->getQuery(true);
  $query->select(['id','user_id'])
    ->from($db->qn('#__eb_registrants'))
    ->where($db->qn('event_id') . '=' . $db->q($event->eventId))
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
    foreach ( $output AS $key => $value )
    {
      ?>
        <th><?= $key ?></th>
      <?php
    }
    ?>
      </tr>
    </thead>
    <tbody>
  <?php

  foreach ( $registrantIds AS $row )
  {
    $registrant = new Registrant($eventAlias, $row->user_id);

    $output = (object)[
      'id' => $row->id,
      'errors' => '',
      'regid' => '',
      'registration_code' => '',
      'shifts' => 0,
    ];
  

    try {
      $registrant->loadCurrentEvents();
    } catch ( Exception $e ) {
      $output->errors = "Error loading current events";
      dataRow($output);
      continue;
    }

    // Try record checking

    // Load raw record
    $record = Registrant::loadRegistrantRow($row->id);
    $invoice = explode('-', $record->invoice_number);
    if ( count($invoice) < 2 ) {
      $output->errors = "<span class=\"text-danger\">Invalid invoice number</span>";
      dataRow($output);
      continue;
    }

    
    $baseInvoice = implode('-', [$invoice[0], $invoice[1]]);
    $checkin = new Checkin($baseInvoice, false);

    $output->regid = implode('<br/>',[$registrant->badgeId, $checkin->r->legalName, $checkin->r->email]);

    $output->shifts = '<pre>'.$checkin->r->shifts.'</pre>';

    if ( ! $checkin->isValid && $checkin->getUid() == 0) {
      $output->errors = "<span class=\"text-danger\">Checkin record not found</span>";
      dataRow($output);
      continue;
    }

    $errors = [];

    $output->registration_code = $checkin->r->registration_code;

    // If $output->registration_code is > 10 characters, generate a new code
    if ( strlen($output->registration_code) > 10 ) {
      $oldcode = $output->registration_code;
      $output->registration_code = getUniqueCodeForRegistrationRecord($db);
      updateRegistrationCode($db, $oldcode, $output->registration_code);

      $errors[] = "<span class=\"text-info\">Registration code too long, generated new code</span>";
    }

    $output->registration_code = "<pre>{$output->registration_code}</pre>";

    /** @var \ClawCorpLib\Lib\RegistrantRecord */
    foreach ( $registrant->records() AS $record )
    {
      if ( !str_starts_with($record->registrant->invoice_number, $eventInfo->prefix) ) {
        $errors[] = "<span class=\"text-danger\">Invoice mismatch {$record->registrant->invoice_number}</span><br/>";
      }
    }
    
    if ( $checkin->r->error != '' ) {
      // put text-danger around checkin errors first
      $checkinErrors = explode("\n", $checkin->r->error);
      $checkinErrors = array_map(function($e) {
        return str_starts_with($e, 'Badge not') ? $e : "<span class=\"text-danger\">{$e}</span>";
      }, $checkinErrors);
      $errors = array_merge($errors, $checkinErrors);
    }

    // Dinner selection valid?
    reset($checkin->r->dinners);
    $meal = current($checkin->r->dinners);

    if ( $meal != '' ) {
      $mealPhraseFound = false;
      foreach ( $mealPhrases AS $m ) {
        if ( stristr($meal, $m) !== false ) {
          $mealPhraseFound = true;
          break;
        }
      }

      if ( !$mealPhraseFound ) {
        $errors[] = "<span class=\"text-danger\">Invalid dinner selection({$meal})</span>";
      } else {
        $errors[] = "<span class=\"text-success\">Dinner: {$meal}</span>";
      }
    }

    if ( !in_array($checkin->r->shirtSize, $shirtSizes) ) {
      $errors[] = "<span class=\"text-danger\">Invalid shirt size({$checkin->r->shirtSize})</span>";
    }

    $output->errors = implode('<br/>', $errors);

    if ( !$checkin->isValid ) {
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

if ( count($duplicateUserIds) > 0 ) {
  echo "<h2 class=\"text-danger\">Duplicate User IDs</h2>\n";
  echo "<pre>" . implode("\n", $duplicateUserIds) . "</pre>\n";
} else {
  echo "<h2 class=\"text-success\">No duplicate user ids found</h2>\n";
}

\ClawCorpLib\Helpers\Bootstrap::rawFooter();

function dataRow($output)
{
  ?>
  <tr>
  <?php
  foreach ( $output AS $value )
  {
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

		while (true)
		{
			$uniqueCode = UserHelper::genRandomPassword($length);
			$query->clear()
				->select('COUNT(*)')
				->from('#__eb_registrants')
				->where($db->quoteName($fieldName) . ' = ' . $db->quote($uniqueCode));
			$db->setQuery($query);
			$total = $db->loadResult();

			if (!$total)
			{
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