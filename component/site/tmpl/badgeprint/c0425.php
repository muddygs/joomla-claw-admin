<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use ClawCorpLib\Enums\BadgeOrientation;
use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Lib\Checkin;
use ClawCorpLib\Checkin\Record;

// Importing from Event Booking installed library
$path = JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/vendor/chillerlan/php-qrcode/src';
JLoader::registerNamespace('chillerlan\\QRCode', $path);
$path = JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/vendor/chillerlan/php-settings-container/src';
JLoader::registerNamespace('chillerlan\\Settings', $path);

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\QRCode;

//require_once(JPATH_LIBRARIES . '/claw/External/barcode/vendor/autoload.php');

ClawCorpLib\Helpers\Bootstrap::rawHeader(['/media/com_claw/js/print_badge.js'], ['/media/com_claw/css/primaryid_badge.css']);
$ordering = $this->printOrderings[$this->type];
$checkinCache = [];

?>
<div class="noprint d-grid gap-2">
  <a class="btn btn-primary btn-lg" href="javascript:window.print();">PRINT</a>
  <a class="btn btn-danger btn-lg" href="javascript:window.close();">CLOSE</a>
</div>
<?php foreach ($this->registrationCodes as $registrationCode):
  try {
    $checkinCache[$registrationCode] = new Checkin($registrationCode);
  } catch (\Exception) {
    continue;
  }

  if ($this->checkinRecord) $checkinCache[$registrationCode]->doCheckin();

  $r = $checkinCache[$registrationCode]->r;

  $image = 'attendee.svg';
  $orientation = BadgeOrientation::portrait->name;

  switch ($r->eventPackageType) {
    case EventPackageTypes::volunteer1:
    case EventPackageTypes::volunteer2:
    case EventPackageTypes::volunteer3:
    case EventPackageTypes::volunteersuper:
      $image = 'volunteer.svg';
      break;
    case EventPackageTypes::claw_staff:
      $image = 'coordinator.svg';
      break;
    case EventPackageTypes::claw_board:
      $image = 'board.svg';
      break;
    case EventPackageTypes::event_staff:
      $image = 'staff.svg';
      break;
    case EventPackageTypes::event_talent:
      $image = 'volunteer.svg';
      break;
    case EventPackageTypes::educator:
      $image = 'educator.svg';
      break;
    case EventPackageTypes::vendor_crew:
      $image = 'vendorcrew.svg';
      break;
    case EventPackageTypes::vendor_crew_extra:
      $image = 'vendorcrewextra.svg';
      break;
    case EventPackageTypes::vip:
      $image = 'vip.svg';
      break;
    case EventPackageTypes::attendee:
      break;
    default:
      $checkinCache[$registrationCode]->doMarkPrinted();
      continue 2;
      # code...
      break;
  }

  if ($ordering == 'sequential') {
    badgeFront($r->toRecord(), $orientation, $this->imagePath . $image);
    badgeBack($r->toRecord(), $this->imagePath);
  } else {
    badgeFront($r->toRecord(), $orientation, $this->imagePath . $image);
  }

  $checkinCache[$registrationCode]->doMarkPrinted();
endforeach;

// Print backs in forward order
if ($ordering == 'fb') {
  reset($this->registrationCodes);
  for (key($this->registrationCodes); key($this->registrationCodes) !== null; next($this->registrationCodes)) {
    $code = current($this->registrationCodes);
    badgeBack($checkinCache[$code]->r->toRecord(), $this->imagePath);
  }
}

// Print backs in reverse order
if ($ordering == 'fbr') {
  reset($this->registrationCodes);
  for (end($this->registrationCodes); key($this->registrationCodes) !== null; prev($this->registrationCodes)) {
    $code = current($this->registrationCodes);
    badgeBack($checkinCache[$code]->r->toRecord(), $this->imagePath);
  }
}

ClawCorpLib\Helpers\Bootstrap::rawFooter();

function badgeFront(Record $r, string $orientation, string $frontImage): void
{
  $options = new QROptions([
    'version'          => 1, // Max 20 characters w/ECC_M
    'outputType'       => QRCode::OUTPUT_MARKUP_SVG,
    'eccLevel'         => QRCode::ECC_M,
    'outputBase64'     => true,
    'quietzoneSize'    => 2,
  ]);

  $qr = (new QRCode($options))->render($r->registration_code);

  $nophotoImage = dirname($frontImage) . '/nophoto.svg';

?>
  <div class="label <?= $orientation ?>" style="position:relative;" id="<?= $r->registration_code ?>">
    <img class="graphic" src="<?= $frontImage ?>" />
    <div class="badgename">
      <?= $r->badge ?>
    </div>
    <div class="pronouns">
      <?= $r->pronouns ?>
    </div>
    <div class="regid">
      <?= $r->badgeId ?>
    </div>
    <div class="type">
      <?= $r->staff_type ?>
    </div>
    <div class="barcode">
      <img class="qrcode" src="<?= $qr; ?>" alt="QR Code" />
    </div>
    <?php if ($r->leatherHeartSupport): ?>
      <div class="heart" style="color:red">
        <i class="fa fa-2x fa-heart"></i>
      </div>
    <?php endif; ?>
    <?php if (false == $r->photoAllowed): ?>
      <div class="nophoto">
        <img src="<?= $nophotoImage ?>" alt="nophoto" />
      </div>
    <?php endif; ?>

  </div>
  <div class="page-break"></div>
<?php
}

function badgeBack(Record $r): void
{
  $s = nl2br($r->shifts);
  // Convenience variables
  $regCode = $r->registration_code;

  $noPhoto = $r->photoAllowed ? '' : 'No';

  $coc = $r->cocSigned ? 'Yes' : 'No';

?>
  <div class="label" id="<?= $regCode ?>b">
    <ul class="flex-container">
      <li class="header">
        <table style="width:100%">
          <tr>
            <td style="width:50%"><span style="margin-left:0.5mm;"><?= $r->badgeId ?></span></td>
            <td style="width:50%; text-align:right;"><span style="margin-right:0.5mm;">Shirt: <?= $r->shirtSize ?></span></td>
          </tr>
        </table>
      </li>
      <li class="infoline">Dinner</li>
      <li class="infoline">Brunch</li>
      <li class="infoline">Buffets</li>
      <li class="infoline">Photo</li>
      <li class="infoline">COC Signed</li>
      <li class="value"><?= $r->dinner ?></li>
      <li class="value"><?= $r->brunch ?></li>
      <li class="value"><?= $r->buffets ?></li>
      <li class="value"><?= $noPhoto ?></li>
      <li class="value"><?= $coc ?><br /><?= $r->id ?></li>
      <li class="shifts"><?= $s ?></li>
    </ul>
  </div>
  <div class="page-break"></div>
<?php
}
