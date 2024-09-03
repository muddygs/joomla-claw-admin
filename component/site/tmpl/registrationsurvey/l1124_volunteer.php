<?php

use ClawCorpLib\Helpers\Bootstrap;

if ($this->onsiteActive):
?>
  <div class="container">
    <ul>
      <li>Register at a reduced rate. If you do not perform your shifts, we will charge the card on file up to
        the cost of an Attendee Registration package rate.</li>
      <li>Volunteers choosing to do three shifts - check out for $1.</li>
      <li>All on-site volunteer shifts are assigned at the Volunteer Assignments Desk</li>
    </ul>
    <div class="d-grid gap-2">
      <a href="<?= $this->registrationLinks['vol3'] ?>" class="btn btn-danger btn-lg" role="button">
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
  <p><span class="badge rounded-pill bg-danger">New</span>Volunteer Packages no longer require deposit. Standard volunteer
    options are:
  <ul>
    <li>Volunteer for 3-shifts for a $1 package fee</li>
  </ul>
  <p>If you are interested in a leadership or recruited volunteer position, you may also find more
    information about leadership
    opportunities <a href="https://clawinfo.org/planning/leadership-opportunities" target="_blank">here.</a>
  </p>

  <ul>

    <li>Volunteer shifts are 4-5 hours each</li>
    <li>Volunteer Packages include all the benefits of the attendee package, plus all of the following:</li>
    <ul>
      <li><span style="color:#ffae00">Food and Drinks:</span> Free food and non-alcoholic drinks in the Volunteer Hospitality Suite at the host hotel all weekend long</li>
      <li><span style="color:#ffae00">Discounts</span> on event meals (calculated at checkout)</li>
    </ul>
    <li>There are many volunteers that should register as a Recruited Volunteer, such as supervisors, bootblacks, entertainers, photographers, and cashiers. If you are interested
      in one of these positions, please complete the survey <a href="https://forms.gle/qoJ61i9qQZ1bCvLv5" target="_blank">here</a> and do not register at this time. Guest Services will contact you to discuss opportunities.</li>
    <li>All volunteers pay a reduced-rate registration fee. This puts your credit card on file.</li>
    <li>Volunteers are vital to the success of the event. Signing up to volunteer is a promise that you will attend all shifts on time and ready to work. Failure to attend some or all of your volunteer shifts will result in charges to your credit card, up to the cost of a full attendee package ($249).
      Volunteers with complimentary or reduced cost accommodations (including recruited and super volunteers) who do not attend shifts may also be charged for the cost of their accommodations.</li>
    <li>The volunteer FAQ is available <a
        href="https://www.clawinfo.org/volunteer-faq">here</a>
    </li>
  </ul>
  <hr>

<?php
endif;

$content = [
  'battery-full' => [
    'Volunteer',
    '$1<br><a href="' . $this->registrationLinks['vol3'] . '" role="button" class="btn btn-danger">Volunteer for 3 Shifts</a>'
  ],
  #'battery-half' => [
  #'Volunteer',
  #'$89<br><a href="' . $this->registrationLinks['vol2'] . '" role="button" class="btn btn-danger">Volunteer for 2 Shifts</a>'
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
