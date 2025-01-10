<?php

use ClawCorpLib\Helpers\Bootstrap;

if ($this->onsiteActive):
?>
  <div class="container">
    <ul>
      <li>Register at a reduced rate. If you do not perform your shifts, we will charge the card on file up to the cost of an Attendee Registration package rate.</li>
      <li>Volunteers choosing to do 3 shifts &mdash; check out for $1.</li>
      <li>All on-site volunteer shifts are assigned at the Volunteer Assignments Desk</li>
    </ul>
    <div class="d-grid gap-2">
      <a href="<?= $this->registrationLinks['volunteer3'] ?>" class="btn btn-danger btn-lg" role="button">
        Click To Register As A Volunteer (3 Shifts)
      </a>
    </div>
  </div>

<?php
else:
?>

  <h2>Volunteer Registration Information</h2>
  <p>We are proud that nearly half of the people at CLAW are volunteers. With more than 150 events and exhibitors
    in 4 days, the volunteers are an essential element that makes CLAW possible.</p>
  <h3>Point System for Volunteer Shifts</h3>
  <ol>
    <ul>
      <li>Each shift has a designated point value:</li>
      <ul>
        <li><b>Regular shifts:</b> 1 point</li>
        <li><b>Priority shifts:</b> 2 points (marked as <b>x2</b> during selection)
        <li><b>Critical shifts:</b> 3 points (marked as <b>x3</b> during selection)
      </ul>
      <li>The point value reflects the importance or intensity of the shift</li>
      <li>Volunteer shifts are 4-5 hours each regardless of assigned points</li>
    </ul>
  </ol>
  <h3>Volunteer Package Options and Fees</h3>
  <ul>
    <li>Volunteers are vital to the success of the event. Signing up to volunteer is a promise that you will attend all shifts on time and ready to work. Failure to attend some or all of your volunteer shifts will result in charges to your credit card, up to the cost of a full attendee package ($269).
    <li>3-Point Volunteer Package &mdash; $1 fee (non-refundable)</li>
    <li>2-Point Volunteer Package &mdash; $79 fee</li>
    <li>6-8 Point Super Volunteer Package &mdash; $1 fee (requires pre-approval). Super Volunteers may receive shared accommodations at a designated CLAW hotel.</li>
    <li>Some volunteer roles &mdash; such as supervisors, bootblacks, entertainers, photographers, and cashier &mdash; require you to register as a <strong>Recruited Volunteer</strong>.
      If you are interested in one of these positions, please complete the survey <a href="https://forms.gle/qoJ61i9qQZ1bCvLv5" target="_blank">here</a> instead of registering now. Guest Services will contact you to discuss available opportunities.</li>
    <li>Volunteers with complimentary or reduced-cost accommodations (including recruited and super volunteers) who do not attend shifts may also be charged for the cost of their accommodations.</li>
  </ul>

  <h3>Volunteer Benefits</h3>
  <ul>
    <li>Volunteer Packages include the standard attendee package benefits, plus:</li>
    <ul>
      <li><strong>Food and Drinks:</strong> Free food and non-alcoholic drinks in the Volunteer Hospitality Suite all weekend</li>
      <li><strong>Discounts</strong> on event meals: Calculated at checkout</li>
    </ul>
  </ul>

  <h3>Other Volunteer Opportunities and FAQ</h3>

  <ul>
    <li>For additional volunteer details, check out the
      <a href="https://www.clawinfo.org/volunteer-faq">Volunteer FAQ</a>
    </li>
  </ul>

  <hr />

<?php
endif;

$content = [
  'battery-full' => [
    'Volunteer',
    '$1<br><a href="' . $this->registrationLinks['volunteer3'] . '" role="button" class="btn btn-danger">Volunteer for 3 Shifts</a>'
  ],
  #'battery-half' => [
  #'Volunteer',
  #'$89<br><a href="' . $this->registrationLinks['volunteer2'] . '" role="button" class="btn btn-danger">Volunteer for 2 Shifts</a>'
  #],
  'user-tag' => [
    'Recruited Volunteer',
    'If you are an approved Recruited Volunteer, please enter your coupon code above and click Start Registration or contact <a href="/planning/guest-services?category_id=19">guest services</a> to obtain your registration coupon.'
  ],
];

$tags = [
  ['<h4 class="fw-bold mb-0">', '</h4>'],
  ['<p>', '</p>']
];

Bootstrap::writeGrid($content, $tags);
