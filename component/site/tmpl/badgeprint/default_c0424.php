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
use ClawCorpLib\Lib\CheckinRecord;

// Importing from Event Booking installed library
$path = JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/vendor/chillerlan/php-qrcode/src';
JLoader::registerNamespace('chillerlan\\QRCode', $path);
$path = JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/vendor/chillerlan/php-settings-container/src';
JLoader::registerNamespace('chillerlan\\Settings', $path);
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\QRCode;

//require_once(JPATH_LIBRARIES . '/claw/External/barcode/vendor/autoload.php');

ClawCorpLib\Helpers\Bootstrap::rawHeader(['/media/com_claw/js/print_badge.js'], ['/media/com_claw/css/primaryid_badge.css']);

?>
  <div class="noprint d-grid gap-2">
    <a class="btn btn-primary btn-lg" href="javascript:window.print();">PRINT</a>
    <a class="btn btn-danger btn-lg" href="javascript:window.close();">CLOSE</a>
  </div>
  <?php foreach ($this->registrationCodes as $registrationCode):
    $c = new Checkin($registrationCode);

//    if (!$c->isValid) continue;

    if ($this->checkinRecord) $c->doCheckin();

    $r = $c->r;

    $image = 'attendee.svg';
    $orientation = BadgeOrientation::portrait->name;

    switch ($r->eventPackageType) {
      case EventPackageTypes::volunteer1:
      case EventPackageTypes::volunteer2:
      case EventPackageTypes::volunteer3:
      case EventPackageTypes::volunteersuper:
      case EventPackageTypes::event_talent:
        $image = 'volunteer.svg';
        break;
      case EventPackageTypes::claw_staff:
        $image = 'coordinator.svg';
        if ($r->overridePackage == 'Board member') $image = 'board.svg';
        break;
      case EventPackageTypes::event_staff:
        $image = 'staff.svg';
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
        $c->doMarkPrinted();
        continue 2;
        # code...
        break;
    }

    badgeFront($r, $orientation, $this->imagePath . $image);

    if ($this->primacy != true) {
      badgeBack($r, $this->imagePath);
    }

    if ( $this->checkinRecord ) $c->doCheckin();
    $c->doMarkPrinted();
  endforeach;

  if ($this->primacy) {
    for (end($this->registrationCodes); key($this->registrationCodes) !== null; prev($this->registrationCodes)) {
      $code = current($this->registrationCodes);
      $c = new Checkin($code);

      $r = $c->r;

      badgeBack($r, $this->imagePath);
    }
  }

ClawCorpLib\Helpers\Bootstrap::rawFooter();

function badgeFront(CheckinRecord $r, string $orientation, string $frontImage): void
{
  // https://github.com/picqer/php-barcode-generator
  // $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
  // $bc = base64_encode($generator->getBarcode(strtoupper($r->registration_code), $generator::TYPE_CODE_39, 1, 38, 'black'));

  $options = new QROptions([
    'version'          => 1, // Max 20 characters w/ECC_M
    'outputType'       => QRCode::OUTPUT_MARKUP_SVG,
    'eccLevel'         => QRCode::ECC_M,
    'outputBase64'     => true,
    'quietzoneSize'    => 2,
  ]);
  
  $qr = (new QRCode($options))->render($r->registration_code);

  if ($r->photoAllowed == false) {
    // 'x' in front of basename indicates the "no photo" badge image
    //$frontImage = dirname($frontImage) . '/x' . pathinfo($frontImage, PATHINFO_BASENAME);
  }
  $nophotoImage = dirname($frontImage) . '/nophoto.svg';
  
  // Convenience variables
  $regCode = $r->registration_code;
  $badgename = $r->badge;
  $pronouns = $r->pronouns;
  $regid = $r->badgeId;

?>
  <div class="label <?= $orientation ?>" style="position:relative;" id="<?= $regCode ?>">
    <img class="graphic" src="<?= $frontImage ?>" />
    <div class="badgename">
      <?= $badgename ?>
    </div>
    <div class="pronouns">
      <?= $pronouns ?>
    </div>
    <div class="regid">
      <?= $regid ?>
    </div>
    <div class="barcode">
      <img class="qrcode" src="<?= $qr; ?>" alt="QR Code" />
    </div>
    <?php if ( $r->leatherHeartSupport ): ?>
    <div class="heart" style="color:red">
      <i class="fa fa-2x fa-heart"></i>
    </div>
    <?php endif; ?>
    <?php if ($r->photoAllowed == false): ?>
    <div class="nophoto">
      <img src="<?= $nophotoImage ?>" alt="nophoto" />
    </div>
    <?php endif; ?>

  </div>
  <div class="page-break"></div>
<?php
}

function badgeBack(CheckinRecord $r, string $imagePath): void
{
  $s = nl2br($r->shifts);
  // Convenience variables
  $regCode = $r->registration_code;

  $noPhoto = $r->photoAllowed == true ? '' : 'No';

  $coc = $r->cocSigned ? 'Yes' : 'No';

  $buffet = $r->getMealString($r->buffets);
  $brunch = $r->getMealString($r->brunches);
  $dinner = $r->getMealString($r->dinners);

?>
  <div class="label" id="<?php echo $regCode ?>b">
    <ul class="flex-container">
      <li class="header">
        <table style="width:100%">
          <tr>
            <td style="width:50%"><span style="margin-left:0.5mm;"><?php echo $r->badgeId ?></span></td>
            <td style="width:50%; text-align:right;"><span style="margin-right:0.5mm;">Shirt: <?php echo $r->shirtSize ?></span></td>
          </tr>
        </table>
      </li>
      <li class="infoline">Dinner</li>
      <li class="infoline">Brunch</li>
      <li class="infoline">Buffets</li>
      <li class="infoline">Photo</li>
      <li class="infoline">COC Signed</li>
      <li class="value"><?php echo $dinner ?></li>
      <li class="value"><?php echo $brunch ?></li>
      <li class="value"><?php echo $buffet ?></li>
      <li class="value"><?php echo $noPhoto ?></li>
      <li class="value"><?php echo $coc ?><br /><?php echo $r->id ?></li>
      <li class="shifts"><?php echo $s ?></li>
    </ul>
  </div>
  <div class="page-break"></div>
<?php
}
