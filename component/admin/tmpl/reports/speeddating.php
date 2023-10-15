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
<h1 class="text-center">Speed Dating Report</h1>
<?php

foreach($this->items as $item ):
?>
<h2 class="text-center"><?= $item->title ?></h2>
<table class="table table-striped table-bordered">
<thead class="thead-dark">
  <tr>
    <th scope="col" class="col-1">#</th>
    <th scope="col" class="col-2">First</th>
    <th scope="col" class="col-2">Last</th>
    <th scope="col" class="col-3">Email</th>
    <th scope="col" class="col-2">Status</th>
    <th scope="col" class="col-2">Badge #</th>
  </tr>
</thead>
<tbody>
<?php
  $id = 1;
	foreach($item->registrants as $r)
	{
    $records = $r->records();
    $record = reset($records);
		$confirmed = $record->registrant->published==1 ? 'Confirmed' : 'Waitlist';
?>
  <tr>
    <td><?php echo $id; ?></td>
    <td class="text-capitalize"><?php echo $record->registrant->first_name; ?></td>
    <td class="text-capitalize"><?php echo $record->registrant->last_name; ?></td>
    <td class="text-lowercase"><?php echo $record->registrant->email; ?></td>
    <td><?php echo $confirmed; ?></td>
    <td><?php echo $r->badgeId; ?></td>
  </tr>
<?php
    $id++;
	}
?>
</tbody>
</table>
<div class="page-break"></div>
<?php
endforeach;

\ClawCorpLib\Helpers\Bootstrap::rawFooter();
