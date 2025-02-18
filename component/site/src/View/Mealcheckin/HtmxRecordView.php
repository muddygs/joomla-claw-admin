<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\View\Mealcheckin;

\defined('_JEXEC') or die;

use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Enums\EventPackageTypes;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use ClawCorpLib\Lib\Checkin;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Lib\PackageInfo;

class HtmxRecordView extends BaseHtmlView
{
  public string $search; // set in MealcheckinController
  public int $mealPackageInfoId; // set in MealcheckinController

  // For the template
  public string $error = ''; // prepared error string

  public bool $isValid = false;

  public function display($tpl = null)
  {
    try {
      $packageInfo = new PackageInfo($this->mealPackageInfoId);
      if ($packageInfo->eventId == 0 || $packageInfo->published != EbPublishedState::published) {
        $this->error = 'Meal package not published or deployed.';

        $this->setLayout('htmx_mealcheckin_failure');
        parent::display($tpl);
        return;
      }
    } catch (\Exception $e) {
      $this->error = $e->getMessage();

      $this->setLayout('htmx_mealcheckin_failure');
      parent::display($tpl);
      return;
    }

    try {
      $checkin = new Checkin($this->search, true);
      $this->isValid = $checkin->isValid;
    } catch (\Exception) {
      $this->error = 'Unable to find attendee package.';

      $this->setLayout('htmx_mealcheckin_failure');
      parent::display();
      return;
    }

    $ticketEventId = $this->validateMeal($packageInfo, $checkin);

    if ($ticketEventId) {
      $checkin->issueMealTicket($packageInfo->eventId, $ticketEventId);
      $this->msg = $packageInfo->title . ' ticket issued for: ' . $checkin->r->badgeId;
      $this->dinner = '';

      $eventInfo = new EventInfo($packageInfo->eventAlias);

      if ($packageInfo->category == $eventInfo->eb_cat_dinners) {
        $this->dinner = $checkin->r->meals[$packageInfo->category][$packageInfo->eventId];
        $this->msg = $this->dinner . ': ' . $this->msg;
      }
      $this->setLayout('htmx_mealcheckin_success');
    } else {
      $this->setLayout('htmx_mealcheckin_failure');
    }

    parent::display();
  }

  private function validateMeal(PackageInfo $packageInfo, Checkin $checkin): int
  {
    // Handle mapping meal bundles
    // Meal bundles map to individual meals via the PackageInfo "meta" field
    // * Check that the meal bundle contains the target meal
    // * When we check in, the event id of the target meal is recorded to the meal bundle registration record

    $ticketEventId = $eventId = $packageInfo->eventId;
    if (array_key_exists($eventId, $checkin->r->mealIssueMapping)) $ticketEventId = $checkin->r->mealIssueMapping[$eventId];

    $this->error = '';

    if (!$checkin->isValid) {
      $this->error = 'Record error or invalid badge #/code.';
    } elseif (is_null($checkin->r)) {
      $this->error = 'Record error or invalid badge #/code.';
    } elseif (!$checkin->r->issued) {
      $this->error = 'Badge not issued.';
    } elseif (in_array($eventId, $checkin->r->issuedMealTickets)) {
      $this->error = "$packageInfo->title ticket already issued" .
        ($packageInfo->eventPackageType == EventPackageTypes::dinner ? ': ' . $checkin->r->meals[$packageInfo->category][$eventId] : '');
      $this->error = '';
    } elseif (
      !isset($checkin->r->meals[$packageInfo->category][$packageInfo->eventId])
      || empty($checkin->r->meals[$packageInfo->category][$packageInfo->eventId])
    ) {
      $this->error = $packageInfo->title . ' not assigned to this badge';
    }

    return empty($this->error) ? $ticketEventId : 0;
  }
}
