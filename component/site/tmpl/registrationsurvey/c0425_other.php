<?php

use ClawCorpLib\Helpers\Bootstrap;

?>
<h4 class="fw-bold mb-0">VendorMart Crew Registration</h4>
<p>Enter your coupon
  code above and click Start Registration. If you do not have your coupon, please
  contact your department coordinator or submit a Guest Services ticket <a href="/help?category_id=7">here.</a></p>

<?php
$content = [
  'chalkboard-teacher' => [
    'Educator Registration',
    'Pre-approved educators only.<br><a href="' . $this->registrationLinks['educator'] . '" role="button" class="btn btn-danger">Start Registration</a>'
  ],
  'users-cog' => [
    'Coordinator Registration',
    'Pre-approved coordinators only.<br><a href="' . $this->registrationLinks['claw_staff'] . '" role="button" class="btn btn-danger">Start Registration</a>'
  ],
  'hands-helping' => [
    'Board Members Registration',
    'Pre-approved board members only.<br><a href="' . $this->registrationLinks['claw_board'] . '" role="button" class="btn btn-danger">Start Registration</a>'
  ],
];

$tags = [
  ['<h4 class="fw-bold mb-0">', '</h4>'],
  ['<p>', '</p>']
];

Bootstrap::writeGrid($content, $tags);

if (!$this->onsiteActive):
?>
  <p>Day, Night, and VendorMart passes will be available for registration starting Thursday, November 28, 2024.</p>
<?php endif;
