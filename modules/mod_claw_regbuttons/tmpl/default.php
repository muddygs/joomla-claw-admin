<?php
defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Bootstrap;

$content = [
    'ticket-alt' => ['<a href="/registration-survey">Registration</a>'],
    'calendar' => ['<a href="https://www.clawinfo.org/attending/claw-23-cle/schedule">Event Schedule</a>'],
    'chalkboard' => ['<a href="https://www.clawinfo.org/attending/claw-23-cle/skills">Skills Schedule</a>'],
    'shopping-cart' => ['<a href="https://www.clawinfo.org/attending/claw-23-cle/vendormart">VendorMart</a>'],
    'trophy' => ['<a href="https://auction.clawinfo.org/">Silent Auction</a>'],
    'mobile' => ['<a href="https://my.yapp.us/CLAW">CLAW Yapp App</a>'],
    'hotel' => ['<a href="https://www.clawinfo.org/planning/claw-cleveland-registration/c23-hotels-reservations">Hotel Reservations</a>'],
  ];

  $tags = [
    ['<h4 class="fw-bold mb-0">','</h4>'],
  ];

Bootstrap::writeGrid($content, $tags);
?>

<p class="small text-center"> For Day Passes, Night Passes, and VendorMart Passes, visit Registration to receive your badge or wrist band.</p>