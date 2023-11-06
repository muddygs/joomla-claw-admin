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
use Joomla\CMS\Router\Route;

$tags = [
  ['<h4 class="fw-bold mb-0">','</h4>'],
  ['<p>','</p>']
];


?>
<h1>CLAW Dashboard</h1>

<h2>Event Management</h2>
<?php
$eventSchedule = Route::_('index.php?option=com_claw&view=schedules');
$sponsors = Route::_('index.php?option=com_claw&view=sponsors');
$vendors = Route::_('index.php?option=com_claw&view=vendors');
$locations = Route::_('index.php?option=com_claw&view=locations');
$shifts = Route::_('index.php?option=com_claw&view=shifts');

$content = [
    'ticket-alt' => ['Event Schedule','<a href="' . $eventSchedule . '" role="button" class="btn btn-danger">Launch</a>'],
    'splotch' => ['Sponsors','<a href="' . $sponsors . '" role="button" class="btn btn-danger">Launch</a>'],
    'shopping-basket' => ['Vendors','<a href="' . $vendors . '" role="button" class="btn btn-danger">Launch</a>'],
    'map-signs' => ['Locations','<a href="' . $locations . '" role="button" class="btn btn-danger">Launch</a>'],
    'people-carry' => ['Shifts','<a href="' . $shifts . '" role="button" class="btn btn-danger">Launch</a>'],
  ];
  
Bootstrap::writeGrid($content, $tags, false, false);

?>
<hr/>
<h2>Reports</h2>
<?php
$content = [
  'stopwatch' => ['Speed Dating','<a href="/administrator/index.php?option=com_claw&view=reports&layout=speeddating&format=raw" role="button" class="btn btn-danger" target="_blank">Launch</a>'],
  'globe' => ['Volunteer Overview','<a href="/administrator/index.php?option=com_claw&view=reports&layout=volunteer_overview&format=raw" role="button" class="btn btn-danger" target="_blank">Launch</a>'],
  'list' => ['Volunteer Detail','<a href="/administrator/index.php?option=com_claw&view=reports&layout=volunteer_detail&format=raw" role="button" class="btn btn-danger" target="_blank">Launch</a>'],
  'tshirt' => ['Shirts','<a href="/administrator/index.php?option=com_claw&view=reports&layout=shirts&format=raw" role="button" class="btn btn-danger" target="_blank">Launch</a>'],
  'utensils' => ['Meals','<a href="/administrator/index.php?option=com_claw&view=reports&layout=meals&format=raw" role="button" class="btn btn-danger" target="_blank">Launch</a>'],
];

Bootstrap::writeGrid($content, $tags, false, false);

?>
<hr/>
<h2>Exports</h2>
<p>NOTE: Exports are for the current event only.</p>
<?php
$content = [
  'stopwatch' => ['Schedule','<a href="/administrator/index.php?option=com_claw&view=reports&layout=csv_schedule&format=raw" role="button" class="btn btn-info" target="_blank">Export</a>'],
  'globe' => ['Sponsors','<a href="/administrator/index.php?option=com_claw&view=reports&layout=csv_sponsors&format=raw" role="button" class="btn btn-info" target="_blank">Export</a>'],
  'user-tag' => ['Presenters','<a href="/administrator/index.php?option=com_claw&view=reports&layout=csv_presenters&format=raw" role="button" class="btn btn-info" target="_blank">Export</a>'],
  'list' => ['Classes','<a href="/administrator/index.php?option=com_claw&view=reports&layout=csv_classes&format=raw" role="button" class="btn btn-info" target="_blank">Export</a>'],
  'file-archive' => ['Zip Presenter Images','<a href="/administrator/index.php?option=com_claw&view=reports&layout=zip_presenters&format=raw" role="button" class="btn btn-info" target="_blank">Export</a>'],
];

Bootstrap::writeGrid($content, $tags, false, false);

?>
<hr/>
<h2>Skills &amp; Education</h2>
<?php
$content = [
  'ticket-alt' => ['Presenters','<a href="/administrator/index.php?option=com_claw&view=presenters" role="button" class="btn btn-danger">Launch</a>'],
  'user-tag' => ['Classes','<a href="/administrator/index.php?option=com_claw&view=classes" role="button" class="btn btn-danger">Launch</a>'],
];
  
Bootstrap::writeGrid($content, $tags, false, false);
?>

<hr/>
<h2>Administration Tools</h2>
<?php
$content = [
    'ticket-alt' => ['Coupon Generator','<a href="/administrator/index.php?option=com_claw&view=coupongenerator&layout=edit" role="button" class="btn btn-danger">Launch</a>'],
    'user-tag'   => ['Refunds','<a href="/administrator/index.php?option=com_claw&view=refunds&layout=edit" role="button" class="btn btn-danger">Launch</a>'],
    'copy'       => ['Event Copy','<a href="/administrator/index.php?option=com_claw&view=eventcopy&layout=edit" role="button" class="btn btn-danger">Launch</a>'],
  ];

Bootstrap::writeGrid($content, $tags, false, false);

