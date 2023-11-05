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
<h1>Meal Counts</h1>

<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Meal</th>
        <th>Count</th>
      </tr>
    </thead>
    <tbody>
<?php

foreach ( $this->items AS $item):
?>
      <tr>
        <td><?php echo $item->description; ?></td>
        <td class="fw-bold"><?php echo $item->count; ?></td>
      </tr>

      <?php if ( count($item->subcount) ):
        foreach ( $item->subcount AS $subitem ):
      ?>
      <tr>
        <td><?php echo '&nbsp;&nbsp&rarr; ' . $subitem->field_value; ?></td>
        <td class="fst-italic"><?php echo '&nbsp;&nbsp&rarr; ' . $subitem->value_count; ?></td>
      </tr>
      <?php
        endforeach;
      endif;
endforeach;

?>
    </tbody>
  </table>
</div>
<?php
\ClawCorpLib\Helpers\Bootstrap::rawFooter();

