<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
\defined('_JEXEC') or die('Restricted Access');

use ClawCorpLib\Helpers\Bootstrap;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

$tags = [
  ['<h4 class="fw-bold mb-0">', '</h4>'],
  ['<p>', '</p>']
];

$eventSchedule = Route::_('index.php?option=com_claw&view=schedules');
$sponsors = Route::_('index.php?option=com_claw&view=sponsors');
$vendors = Route::_('index.php?option=com_claw&view=vendors');
$locations = Route::_('index.php?option=com_claw&view=locations');
$shifts = Route::_('index.php?option=com_claw&view=shifts');


?>
<h1 class="display-6 fw-bold">CLAW Dashboard</h1>

<details open>
  <summary>
    <span class="fw-bold">Event Management:</span> Configuration of website viewable data
  </summary>
  <?php
  $content = [
    'ticket-alt' => ['Event Schedule', '<a href="' . $eventSchedule . '" role="button" class="btn btn-danger">Launch</a>'],
    'splotch' => ['Sponsors', '<a href="' . $sponsors . '" role="button" class="btn btn-danger">Launch</a>'],
    'shopping-basket' => ['Vendors', '<a href="' . $vendors . '" role="button" class="btn btn-danger">Launch</a>'],
    'map-signs' => ['Locations', '<a href="' . $locations . '" role="button" class="btn btn-danger">Launch</a>'],
  ];

  Bootstrap::writeGrid($content, $tags, false);
  ?>
</details>

<details>
  <summary>
    <span class="fw-bold">Reports: </span> Reports for the current event.
  </summary>
  <?php
  $content = [
    'stopwatch' => ['Speed Dating', '<button class="btn btn-danger" value="speeddating" name="layout">Launch</button>'],
    'globe' => ['Volunteer Overview', '<button class="btn btn-danger" value="volunteer_overview" name="layout">Launch</button>'],
    'list' => ['Volunteer Detail', '<button class="btn btn-danger" value="volunteer_detail" name="layout">Launch</button>'],
    'tshirt' => ['Shirts', '<button class="btn btn-danger" value="shirts" name="layout">Launch</button>'],
    'utensils' => ['Meals', '<button class="btn btn-danger" value="meals" name="layout">Launch</button>'],
    'paint-brush' => ['Artshow', '<button class="btn btn-danger" value="csv_artshow" name="layout">Launch</button>'],
    'spa' => ['Spa', '<button class="btn btn-danger" value="spa" name="layout">Launch</button>'],
  ];

  ?>
  <form action="<?= Route::_('index.php?option=com_claw&view=reports&format=raw') ?>" target="_blank" method="post" name="adminForm" id="reports-form" class="form-validate">
    <?php
    echo $this->form->renderField('report_event');
    Bootstrap::writeGrid($content, $tags, false);
    ?>
    <?= HTMLHelper::_('form.token'); ?>
  </form>
</details>

<details>
  <summary>
    <span class="fw-bold">Yapp Exports: </span> Exports for the current event.
  </summary>
  <?php
  $content = [
    'stopwatch' => ['Schedule', '<a href="/administrator/index.php?option=com_claw&view=reports&layout=csv_schedule&format=raw" role="button" class="btn btn-info" target="_blank">Export</a>'],
    'globe' => ['Sponsors', '<a href="/administrator/index.php?option=com_claw&view=reports&layout=csv_sponsors&format=raw" role="button" class="btn btn-info" target="_blank">Export</a>'],
    'store' => ['Vendors', '<a href="/administrator/index.php?option=com_claw&view=reports&layout=csv_vendors&format=raw" role="button" class="btn btn-info" target="_blank">Export</a>'],
    'user-tag' => ['Presenters', '<a href="/administrator/index.php?option=com_claw&view=reports&layout=csv_presenters&format=raw" role="button" class="btn btn-info" target="_blank">Export</a>'],
    'list' => ['Classes', '<a href="/administrator/index.php?option=com_claw&view=reports&layout=csv_classes&format=raw" role="button" class="btn btn-info" target="_blank">Export</a>'],
    'file-archive' => ['Zip Presenter Images', '<a href="/administrator/index.php?option=com_claw&view=reports&layout=zip_presenters&format=raw" role="button" class="btn btn-info" target="_blank">Export</a>'],
  ];

  Bootstrap::writeGrid($content, $tags, false);

  ?>
</details>

<details>
  <summary>
    <span class="fw-bold">Skills &amp; Education: </span> Management of skills and education presenters and classes
  </summary>
  <?php
  $content = [
    'ticket-alt' => ['Presenters', '<a href="/administrator/index.php?option=com_claw&view=presenters" role="button" class="btn btn-danger">Launch</a>'],
    'user-tag' => ['Classes', '<a href="/administrator/index.php?option=com_claw&view=skills" role="button" class="btn btn-danger">Launch</a>'],
    'user-friends' => ['Presenters Export', '<a href="/administrator/index.php?option=com_claw&view=reports&layout=csv_presenters&published_only=0&format=raw" role="button" class="btn btn-info" target="_blank">Export</a>'],
    'list' => ['Classes Export', '<a href="/administrator/index.php?option=com_claw&view=reports&layout=csv_classes&published_only=0&format=raw" role="button" class="btn btn-info" target="_blank">Export</a>'],
  ];

  Bootstrap::writeGrid($content, $tags, false);
  ?>

</details>

<details>
  <summary>
    <span class="fw-bold">Administration Tools: </span> Event specific administration tools
  </summary>
  <?php
  $content = [
    'ticket-alt' => ['Coupon Generator', '<a href="/administrator/index.php?option=com_claw&view=coupongenerator&layout=edit" role="button" class="btn btn-danger">Launch</a>'],
    'user-tag'   => ['Refunds', '<a href="/administrator/index.php?option=com_claw&view=refunds&layout=edit" role="button" class="btn btn-danger">Launch</a>'],
    'plane-departure' => ['Preflight', '<a href="/administrator/index.php?option=com_claw&view=reports&layout=preflight&format=raw" role="button" class="btn btn-info" target="_blank">Launch</a>'],
  ];

  Bootstrap::writeGrid($content, $tags, false);

  ?>
</details>

<details>
  <summary>
    <span class="fw-bold">Event Configuration: </span> Configure events and deploy to Events Booking
  </summary>
  <?php
  $content = [
    'globe'      => ['Events', '<a href="/administrator/index.php?option=com_claw&view=eventinfos" role="button" class="btn btn-success">Launch</a>'],
    'ticket-alt' => ['Packages', '<a href="/administrator/index.php?option=com_claw&view=packageinfos" role="button" class="btn btn-danger">Launch</a>'],
    'people-carry' => ['Shifts', '<a href="' . $shifts . '" role="button" class="btn btn-danger">Launch</a>'],
    'user-tag'   => ['Speed Dating', '<a href="/administrator/index.php?option=com_claw&view=speeddatinginfos" role="button" class="btn btn-danger">Launch</a>'],
    'truck-loading'   => ['Rentals', '<a href="/administrator/index.php?option=com_claw&view=equipmentrentals" role="button" class="btn btn-danger">Launch</a>'],
    'dollar-sign'   => ['Sponsorships', '<a href="/administrator/index.php?option=com_claw&view=sponsorships" role="button" class="btn btn-danger">Launch</a>'],
    'spa'   => ['Spa', '<a href="/administrator/index.php?option=com_claw&view=spainfos" role="button" class="btn btn-danger">Launch</a>'],
    'copy'       => ['Event Copy', '<a href="/administrator/index.php?option=com_claw&view=eventcopy&layout=edit" role="button" class="btn btn-warning">Launch</a>'],
  ];

  Bootstrap::writeGrid($content, $tags, false);
  ?>
</details>
