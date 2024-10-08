<?php

/**
 * @package     ClawCorp.Module.RegButtons
 * @subpackage  mod_claw_regbuttons
 *
 * @copyright   (C) 2024 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Bootstrap;

$content = [];
if ($registration) {
  $content['ticket-alt'] = ['<a href="' . $registration . '">Registration</a>'];
}
if ($schedule) {
  $content['calendar'] = ['<a href="' . $schedule . '">Event Schedule</a>'];
}
if ($skills) {
  $content['chalkboard'] = ['<a href="' . $skills . '">Skills Schedule</a>'];
}
if ($vendormart) {
  $content['shopping-cart'] = ['<a href="' . $vendormart . '">VendorMart</a>'];
}
if ($silentauction) {
  $content['trophy'] = ['<a href="' . $silentauction . '">Silent Auction</a>'];
}
if ($mobileapp) {
  $content['mobile'] = ['<a href="' . $mobileapp . '">CLAW Yapp App</a>'];
}
if ($hotels) {
  $content['hotel'] = ['<a href="' . $hotels . '">Hotel Reservations</a>'];
}
if ($local) {
  $content['map-marker'] = ['<a href="' . $local . '">Supporting Restaurants</a>'];
}

$tags = [
  ['<h4 class="fw-bold mb-0">', '</h4>'],
];

Bootstrap::writeGrid($content, $tags);

if ($infotext) {
  echo $infotext;
}
