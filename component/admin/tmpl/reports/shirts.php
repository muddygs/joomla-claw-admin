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

\ClawCorpLib\Helpers\Bootstrap::rawHeader([], ['/media/com_claw/css/print_letter.css']);

?>
<h1><?= $this->items['eventInfo']->description ?> T-Shirt Size Summary</h1>
<h2>All Registrations</h2>
<table class="table table-striped table-bordered">
<thead class="thead-dark">
  <th>Size</th>
  <th>Count</th>
</thead>
<tbody>
<?php

foreach ( $this->items['sizes'] as $size ) {
?>
<tr>
  <td><?php echo $size ?></td>
  <td><?php echo $this->items['counters']->$size ?></td>
</tr>
<?php
}
?>
</tbody>
</table>

<h2>TOTAL ALL REGISTRATIONS: <?php echo $this->items['totalCount'] ?>

<h2>Volunteer Registrations</h2>
<table class="table table-striped table-bordered">
<thead class="thead-dark">
  <th>Size</th>
  <th>Count</th>
</thead>
<tbody>
<?php

foreach ( $this->items['sizes'] as $size ) {
?>
<tr>
  <td><?php echo $size ?></td>
  <td><?php echo $this->items['volcounters']->$size ?></td>
</tr>
<?php
}
?>
</tbody>
</table>
<h2>TOTAL VOLUNTEER REGISTRATIONS: <?php echo $this->items['volTotalCount'] ?>

<?php
\ClawCorpLib\Helpers\Bootstrap::rawFooter();
