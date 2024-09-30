<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use ClawCorpLib\Enums\BadgeOrientation;
use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Lib\Checkin;
use ClawCorpLib\Lib\CheckinRecord;

require_once(JPATH_LIBRARIES . '/claw/External/barcode/vendor/autoload.php');

$ts = '?ts=' . time();

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset=UTF-8>
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />
  <title>READY TO PRINT</title>
  <link href="/media/com_claw/css/primaryid_badge.css<?= $ts ?>" rel="stylesheet" />
  <script src="/templates/shaper_helixultimate/js/bootstrap.bundle.min.js"></script>
  <script src="/templates/shaper_helixultimate/js/lazysizes.min.js"></script>
  <script src="/media/com_claw/js/print_badge.js<?= $ts ?>"></script>
  <link href="/templates/shaper_helixultimate/css/bootstrap.min.css" rel="stylesheet" />
  <link href="/templates/shaper_helixultimate/css/font-awesome.min.css" rel="stylesheet">
</head>

<body>
  <div class="noprint d-grid gap-2">
    <a class="btn btn-primary btn-lg" href="javascript:window.print();">PRINT</a>
    <a class="btn btn-danger btn-lg" href="javascript:window.close();">CLOSE</a>
  </div>
  <?php foreach ($this->registrationCodes as $registrationCode):
    $c = new Checkin($registrationCode);

    if (!$c->isValid) continue;

    if ($this->checkinRecord) $c->doCheckin();

    $r = $c->r;

    $image = 'attendee.svg';
    $orientation = BadgeOrientation::portrait->name;
    $dayPassOrientation = BadgeOrientation::landscape->name;

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
        break;
      case EventPackageTypes::claw_board:
        $image = 'board.svg';
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
      case EventPackageTypes::day_pass_fri:
        $image = 'friday.svg';
        $orientation = $dayPassOrientation;
        break;
      case EventPackageTypes::day_pass_sat:
        $image = 'saturday.svg';
        $orientation = $dayPassOrientation;
        break;
      case EventPackageTypes::day_pass_sun:
        $image = 'sunday.svg';
        $orientation = $dayPassOrientation;
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

    if ($this->primacy == true) {
      badgeBack($r);
    }

    if ($this->checkinRecord) $c->doCheckin();
    $c->doMarkPrinted();
  endforeach;

  if ($this->primacy == false) {
    for (end($this->registrationCodes); key($this->registrationCodes) !== null; prev($this->registrationCodes)) {
      $code = current($this->registrationCodes);
      $c = new Checkin($code);

      $r = $c->r;

      badgeBack($r);
    }
  }

  ?>
</body>

</html>

<?php
function badgeFront(CheckinRecord $r, string $orientation, string $frontImage): void
{
  // https://github.com/picqer/php-barcode-generator
  $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
  $bc = base64_encode($generator->getBarcode(strtoupper($r->registration_code), $generator::TYPE_CODE_39, 1, 38, 'black'));

  if ($r->photoAllowed == false) {
    // 'x' in front of basename indicates the "no photo" badge image
    $frontImage = dirname($frontImage) . '/x' . pathinfo($frontImage, PATHINFO_BASENAME);
  }

  // Convenience variables
  $regCode = $r->registration_code;
  $badgename = $r->badge;
  $pronouns = $r->pronouns;
  $regid = $r->badgeId;
  $leatherHeart = $r->leatherHeartSupport ? '' : 'd-none';

?>
  <div class="label" style="position:relative;" id="<?= $regCode ?>">
    <img class="graphic" src="<?= $frontImage ?>" />
    <div class="badgename<?= $orientation ?>">
      <?= $badgename ?>
    </div>
    <div class="pronouns<?= $orientation ?>">
      <?= $pronouns ?>
    </div>
    <div class="regid<?= $orientation ?>">
      <?= $regid ?>
    </div>
    <div class="barcode<?= $orientation ?>">
      <img src="data:image/svg+xml;base64,<?= $bc ?>" />
    </div>
    <div class="heart <?= $leatherHeart ?>" style="color:red">
      <i class="fa fa-2x fa-heart"></i>
    </div>
  </div>
  <div class="page-break"></div>
<?php
}

function badgeBack(CheckinRecord $r): void
{
  $s = nl2br($r->shifts);
  // Convenience variables
  $regCode = $r->registration_code;

  $noPhotoClass = $r->photoAllowed == true ? '' : 'nophoto';
  $noPhoto = $r->photoAllowed == true ? '' : 'No';

  $coc = $r->cocSigned ? 'Yes' : 'No';

  $buffet = $r->getMealString($r->buffets);
  $brunch = $r->getMealString($r->brunches);
  $dinner = $r->getMealString($r->dinners);

?>
  <div class="label" id="<?php echo $regCode ?>b">
    <ul class="flex-container">
      <li class="regid">
        <table style="width:100%">
          <tr>
            <td style="width:50%"><span style="margin-left:0.5mm;"><?php echo $r->badgeId ?></span></td>
            <td style="width:50%; text-align:right;"><span style="margin-right:0.5mm;">Shirt: <?php echo $r->shirtSize ?></span></td>
          </tr>
        </table>
      </li>
      <li class="infoline <?php echo $noPhotoClass ?>">Dinner</li>
      <li class="infoline <?php echo $noPhotoClass ?>">Brunch</li>
      <li class="infoline <?php echo $noPhotoClass ?>">Buffets</li>
      <li class="infoline <?php echo $noPhotoClass ?>">Photo</li>
      <li class="infoline <?php echo $noPhotoClass ?>">COC Signed</li>
      <li class="value <?php echo $noPhotoClass ?>"><?php echo $dinner ?></li>
      <li class="value <?php echo $noPhotoClass ?>"><?php echo $brunch ?></li>
      <li class="value <?php echo $noPhotoClass ?>"><?php echo $buffet ?></li>
      <li class="value <?php echo $noPhotoClass ?>"><?php echo $noPhoto ?></li>
      <li class="value <?php echo $noPhotoClass ?>"><?php echo $coc ?><br /><?php echo $r->id ?></li>
      <li class="shifts <?php echo $noPhotoClass ?>"><?php echo $s ?></li>
    </ul>
  </div>
  <div class="page-break"></div>
<?php
}
