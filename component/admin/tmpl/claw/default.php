<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2022 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

 // No direct access to this file
defined('_JEXEC') or die('Restricted Access');

use ClawCorpLib\Helpers\Bootstrap;

$tags = [
  ['<h4 class="fw-bold mb-0">','</h4>'],
  ['<p>','</p>']
];


?>
<h1>CLAW Dashboard</h1>

<h2>Event Management</h2>
<?php
$content = [
    'ticket-alt' => ['Event Schedule','LINK'],
    'splotch' => ['Sponsors','LINK'],
    'shopping-basket' => ['Vendors','LINK'],
    'map-signs' => ['Locations','LINK'],
  ];
  
Bootstrap::writeGrid($content, $tags);
?>
<h2>Volunteer Management</h2>
<?php
$content = [
  'people-carry' => ['Shifts','LINK'],
];

Bootstrap::writeGrid($content, $tags);

?>
<h2>Skills &amp; Education</h2>
<?php
$content = [
    'ticket-alt' => ['Presenter Submissions','LINK'],
    'user-tag' => ['Class Submissions','LINK'],
  ];
  
Bootstrap::writeGrid($content, $tags);
?>


<h2>Administration Tools</h2>
<?php
$content = [
    'ticket-alt' => ['Coupon Generator','<a href="/administrator/index.php?option=com_claw&view=coupongenerator&layout=edit" role="button" class="btn btn-danger">Launch</a>'],
    'user-tag' => ['Refunds','<a href="/administrator/index.php?option=com_claw&view=refunds&layout=edit" role="button" class="btn btn-danger">Launch</a>'],
    #'user-tag' => ['CLAW Nation','Please enter your CLAW Nation coupon code above and click Start Registration or contact <a href="/planning/guest-services?category_id=10">guest services</a> to obtain your registration coupon - CLAW NATION ONLY.'],
  ];

Bootstrap::writeGrid($content, $tags);

