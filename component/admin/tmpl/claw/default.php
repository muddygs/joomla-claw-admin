<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

 // No direct access to this file
\defined('_JEXEC') or die('Restricted Access');

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
    'people-carry' => ['Shifts','LINK'],
  ];
  
Bootstrap::writeGrid($content, $tags);

?>
<h2>Reports</h2>
<?php
$content = [
  'stopwatch' => ['Speed Dating','<a href="/administrator/index.php?option=com_claw&view=reports&layout=speeddating&format=raw" role="button" class="btn btn-danger" target="_blank">Launch</a>'],
  'globe' => ['Volunteer Overview','<a href="/administrator/index.php?option=com_claw&view=reports&layout=volunteer_overview&format=raw" role="button" class="btn btn-danger" target="_blank">Launch</a>'],
  'list' => ['Volunteer Detail','<a href="/administrator/index.php?option=com_claw&view=reports&layout=volunteer_detail&format=raw" role="button" class="btn btn-danger" target="_blank">Launch</a>'],
  'tshirt' => ['Shirts','<a href="/administrator/index.php?option=com_claw&view=reports&layout=shirts&format=raw" role="button" class="btn btn-danger" target="_blank">Launch</a>'],
  'utensils' => ['Meals','<a href="/administrator/index.php?option=com_claw&view=reports&tmpl=meals" role="button" class="btn btn-danger">Launch</a>'],
];

Bootstrap::writeGrid($content, $tags);

?>
<h2>Skills &amp; Education</h2>
<?php
$content = [
    'ticket-alt' => ['Presenters','<a href="/administrator/index.php?option=com_claw&view=presenters" role="button" class="btn btn-danger">Launch</a>'],
    'user-tag' => ['Classes','<a href="/administrator/index.php?option=com_claw&view=classes" role="button" class="btn btn-danger">Launch</a>'],
  ];
  
Bootstrap::writeGrid($content, $tags);
?>


<h2>Administration Tools</h2>
<?php
$content = [
    'ticket-alt' => ['Coupon Generator','<a href="/administrator/index.php?option=com_claw&view=coupongenerator&layout=edit" role="button" class="btn btn-danger">Launch</a>'],
    'user-tag'   => ['Refunds','<a href="/administrator/index.php?option=com_claw&view=refunds&layout=edit" role="button" class="btn btn-danger">Launch</a>'],
    'copy'       => ['Event Copy','<a href="/administrator/index.php?option=com_claw&view=eventcopy&layout=edit" role="button" class="btn btn-danger">Launch</a>'],
  ];

Bootstrap::writeGrid($content, $tags);

