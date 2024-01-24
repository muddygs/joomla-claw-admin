<?php

namespace ClawCorpLib\Lib;

use ClawCorpLib\Enums\EbRecordIndexType;
use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Helpers\EventBooking;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Lib\Registrant;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

\defined('_JEXEC') or die;

/** @package ClawCorpLib\Lib 
 * $submit - overrides default submit button with error message(s)
 * $show_error (repeated top and bottom of form)
 * $show_warning
 * $warning_msg
 * $clawlink (replaced BACK button default link)
 * $non_invoice_event
 * $invoice_event
 */
class EbCart
{
  public string $submit = '';
  public bool $show_error = false;
  public bool $show_warning = false;
  public string $warning_msg = '';
  public string $options_link = '';
  public bool $non_invoice_event = false;
  public bool $invoice_event = false;
  public string $regtype = '';
  public string $referrer = '';

  public function __construct(
    private array $items,
    private string $btnPrimary
  ) {
    $this->submit = <<< HTML
<input type="submit" class="{$this->btnPrimary}" name="btn-submit" id="btn-submit" value="${!${''} = Text::_('EB_PROCESS_REGISTRATION')}">
HTML;

    $this->options_link = EventBooking::getRegistrationLink();

    /** @var Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();

    if ($app->getIdentity() == null || $app->getIdentity()->id == 0) {
      $app->enqueueMessage('You must be signed in to use this resource. Please use the Registration menu.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      $app->redirect('https://www.clawinfo.org/', 'You must be signed in to use this resource. Please use the Registration menu.', $msgType = 'error');
    }

    $package_event = 0;
    $shift_count = 0;
    $requires_main_event = false;

    $clawEventAlias = ClawEvents::eventIdtoAlias($this->items[0]->id);
    $eventConfig = new EventConfig($clawEventAlias, []);

    $onsiteActive = $eventConfig->eventInfo->onsiteActive;
    $mainEventIds = $eventConfig->getMainEventIds();

    $registrantData = new registrant($clawEventAlias, $app->getIdentity()->id);
    $registrantData->loadCurrentEvents(EbRecordIndexType::eventid);
    $records = $registrantData->records();

    $shiftPrefix = $eventConfig->eventInfo->shiftPrefix;
    $shiftCategories = array_merge($eventConfig->eventInfo->eb_cat_shifts, $eventConfig->eventInfo->eb_cat_supershifts);
    $invoiceCategories = ClawEvents::getCategoryIds(Aliases::invoiceCategories);
    $mainRequiredCategories = ClawEvents::getCategoryIds(Aliases::categoriesRequiringMainEvent);
    $mainRequiredEventIds = $eventConfig->getMainRequiredEventIds();

    /** @var ClawCorpLib\Lib\RegistrantRecord */
    foreach ($records as $r) {
      if (substr_compare($r->event->alias, $shiftPrefix, 0, strlen($shiftPrefix)) === 0) {
        // TODO: For onsite, we don't count -- later we block any shifts in the cart
        if (!$onsiteActive) $shift_count++;
        continue;
      }

      // attendee, educator, vendormart crew, staff/event/entertainer
      if (in_array($r->event->eventId, $mainEventIds)) {
        if (0 == $package_event) {
          $package_event = $r->event->eventId;
        } else {
          # TODO: queue up error message and redirect to helpdesk?
          $this->submit = '<div class="alert alert-danger">There is a problem with your registration due to multiple event registrations. Please contact guest services at https://www.clawinfo.org/help.</div>';
          $this->show_error = true;
          echo $this->submit;
          exit;
        }
      }
    }

    foreach ($this->items as $item) {
      // Collected and used to determine overlaps
      if (!array_key_exists($item->id, $records)) {
        $newRecord = (object) [
          'eventId' => $item->id,
          'alias' => $item->alias,
          'title' => $item->title,
          'event_date' => $item->event_date,
          'event_end_date' => $item->event_end_date,
          'category_id' => $item->main_category_id,
        ];

        $registrantData->addRecord($item->id, $newRecord);
      }

      // (by id) attendee, educator, vendormart crew, staff/event/entertainer
      if (in_array($item->id, $mainEventIds)) {
        if ($package_event != 0) {
          $this->submit = '<div class="alert alert-danger">Multiple package events are not allowed. Click the Modify Cart button above to fix your cart.</div>';
          $this->show_error = true;
          break;
        } else {
          $package_event = $item->id;
          $this->non_invoice_event = true;
          // $regType = Helpers::sessionGet('regtype');
          // if ($regType == '') {
          //   $tempE = $e->getEventByKey('eventId', $item->id);
          //   Helpers::sessionSet('regtype', $tempE->link);
          // }
        }
      }

      if (in_array($item->main_category_id, $invoiceCategories)) {
        $this->invoice_event = true;
      }

      if (in_array($item->id, $mainRequiredEventIds)) {
        $requires_main_event = true;
        $this->non_invoice_event = true;
        continue;
      }

      // if (in_array($item->main_category_id, $customNonInvoice)) {
      // 	$this->non_invoice_event = true;
      // 	continue;
      // }

      if (in_array($item->main_category_id, $shiftCategories)) {
        $shift_count++;
        $this->non_invoice_event = true;
        continue;
      }
    } // End cart items loop

    // Shift count check ignored for onsite
    if (!$onsiteActive) {
      /** @var ClawCorpLib\Lib\PackageInfo */
      foreach ($eventConfig->packageInfos as $event) {
        if ($event->eventId == $package_event && $shift_count < $event->minShifts) {
          $this->submit = '<div class="alert alert-danger">Please select at least ' . $event->minShifts . ' shifts. Click Modify Cart to add more shifts.</div>';
          $this->show_error = true;
          break;
        }
      }

      if ( !$package_event && $shift_count > 0 ) {
        $this->submit = '<div class="alert alert-danger">Please select package registration to go with your shifts. Click Modify Cart to add your package.</div>';
        $this->show_error = true;
      }

      // No shifts allowed for non-packages & VendorMart Crew
      if ($package_event == $eventConfig->getMainEventByPackageType(EventPackageTypes::vendor_crew)->eventId && $shift_count > 0) {
        $this->submit = '<div class="alert alert-danger">Your event package does not allow shift selection. Please modify your cart.</div>';
        $this->show_error = true;
      }
    } else {
      if ($shift_count > 0) {
        $this->submit = '<div class="alert alert-danger">Shift selection must be done at the Volunteer Assignment Desk. Please remove any shifts from your cart before proceeding.</div>';
        $this->show_error = true;
      }
    }

    if ( $requires_main_event && !$package_event ) {
      $this->submit = '<div class="alert alert-danger">Some items in your cart require a CLAW package. Click the Modify Cart button above to add an event registration.</div>';
      $this->show_error = true;
    }

    $overlap = $registrantData->checkOverlaps(ClawEvents::getCategoryIds(Aliases::overlapCategories()));
    if (count($overlap) > 0) {
      $this->submit = "<div class=\"alert alert-danger\">You have overlapping or touching events ({$overlap[0]->event->title} and {$overlap[1]->event->title}). Modify your cart to correct this error.</div>";
      $this->show_error = true;
    }

    $shiftCategoryCount = $registrantData->categoryCounts($shiftCategories);
    if (!$onsiteActive && !$this->show_error && $shiftCategoryCount > 1 && $package_event != $eventConfig->getPackageInfo(EventPackageTypes::volunteersuper)->eventId) {
      $this->submit = "<div class=\"alert alert-danger\">Shifts must all come from the same category (e.g., all Guest Services or Badge Check). Modify your cart to correct this error.</div>";
      $this->show_error = true;
    }

    $eventIds = array_keys($registrantData->records());

    #region Meal combo checks

    // Collect meal event ids
    $comboCount = 0;
    foreach ( [EventPackageTypes::combo_meal_1, EventPackageTypes::combo_meal_2, EventPackageTypes::combo_meal_3, EventPackageTypes::combo_meal_4] AS $meta ) {
      $mealEvent = $eventConfig->getPackageInfo($meta);
      if ( is_null($mealEvent) ) continue;

      if ( in_array( $mealEvent->eventId, $eventIds ) ) {
        $comboCount++;

        if ( count(array_intersect($eventIds, $mealEvent->meta)) ) {
          $this->submit = "<div class=\"alert alert-danger\">You cannot combine combo-pack meals with individual meals in the combo.</div>";
          $this->show_error = true;
        } 
      }
    }
    
    if ( $comboCount > 1 ) {
      $this->submit = "<div class=\"alert alert-danger\">You cannot combine combo-pack meals.</div>";
      $this->show_error = true;
    }

    #endregion

    if (true == $this->non_invoice_event && true == $this->invoice_event) {
      $this->warning_msg = '<div class="alert alert-warning">Sorry, due to payment type conflicts, you cannot do a registration in combination with VendorMart/Sponsorship and use the &quot;Invoice Payment&quot; option. If you wish to be billed by invoice, you will need to modify your cart.</div>';
      $this->show_warning = true;
    }

    if ($onsiteActive) $this->non_invoice_event = true;
  }
}
