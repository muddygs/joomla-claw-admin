<?php

use ClawCorpLib\Helpers\Bootstrap;

if ($this->onsiteActive):
?>
  <div class="container">
    <div class="d-grid">
      <a href="<?= $this->registrationLinks['attendee'] ?>" class="btn btn-danger btn-lg" role="button">
        Click To Register As An Attendee ($299 onsite)
      </a>
    </div>
  </div>
<?php

else:

  $content = [
    'ticket-alt' => [
      'Attendee Registration',
      'For standard registration (not Volunteer, Educator, Recruited Volunteer, or VendorMart Crew)<br><a href="' . $this->registrationLinks['attendee'] . '" role="button" class="btn btn-danger">Start Registration</a>'
    ],
    'user-tag' => [
      'CLAW Nation',
      'Please enter your CLAW Nation coupon code above and click Start Registration or contact <a href="/planning/guest-services?category_id=10">guest services</a> to obtain your registration coupon - CLAW NATION ONLY.'
    ],
  ];

  $tags = [
    ['<h4 class="fw-bold mb-0">', '</h4>'],
    ['<p>', '</p>']
  ];

  Bootstrap::writeGrid($content, $tags);
endif;
