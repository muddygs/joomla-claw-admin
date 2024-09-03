<?php

use ClawCorpLib\Helpers\Bootstrap;
use ClawCorpLib\Enums\EventPackageTypes;

$vipEventId = $this->eventConfig->getPackageInfo(EventPackageTypes::vip)->eventId;

$content = [
  'ticket-alt' => ['Attendee Package', 'Includes over 150 events and exhibitors'],
  'utensils' => ['Reserved Seating at Meals', 'Optional reserved seating at all meals (purchase of separate meal tickets required)'],
  'kaaba' => ['Hospitality Suite Access', ''],
  'concierge-bell' => ['Skip the Line', 'Personal delivery of badge and registration materials'],
  'glass-whiskey' => ['Drink Ticket Basket', 'Welcome basket at check-in, including $100 in beverage tickets'],
  'envelope-open-text' => ['President\'s Reception', 'You and your guest are invited'],
];

$tags = [
  ['<h4 class="fw-bold mb-0">', '</h4>'],
  ['<p><i>', '</i></p>']
];

?>

<h2 class="pb-2">VIP Package</h2>
<hr>
<p>The VIP Package includes the following:</p>

<?php
Bootstrap::writeGrid($content, $tags);

if ($this->onsiteActive):
?>
  <div class="d-grid">
    <a role="button" href="javascript:;" class="btn btn-danger btn-lg">Come to Onsite Guest Services for Priority Registration</a>
  </div>

<?php
else:
?>
  <p class="text-center">Click 'Express' for quickest checkout. For more options (meals, equipment rental, and speed dating) use 'Add Ons' button.</p>
  <p class="text-center"><strong><i class="fa fa-2x fa-info"></i>&nbsp;Meals will show in your cart but are discounted at checkout</strong></p>
  <div class="row">
    <div class="col-12 col-lg-4 text-center">
      &nbsp;
    </div>
    <div class="col-12 col-lg-4 text-center">
      <a role="button" href="<?= $this->registrationLinks['vip'] ?>" class="btn btn-danger btn-lg w-100">Express VIP Checkout</a><br />
      All Meals Included<br /><span class="h3">$925</span>
    </div>
    <div class="col-12 col-lg-4 text-center">
      &nbsp;
    </div>
  </div>
<?php
endif;
