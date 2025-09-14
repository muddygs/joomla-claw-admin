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

    foreach ($this->items['sizes'] as $size):
    ?>
      <tr>
        <td><?= $size ?></td>
        <td><?= $this->items['counters']->$size ?></td>
      </tr>
    <?php
    endforeach;
    ?>
  </tbody>
</table>

<h2>TOTAL ALL REGISTRATIONS: <?= $this->items['totalCount'] ?></h2>

<h2>Volunteer Registrations</h2>
<table class="table table-striped table-bordered">
  <thead class="thead-dark">
    <th>Size</th>
    <th>Count</th>
  </thead>
  <tbody>
    <?php

    foreach ($this->items['sizes'] as $size):
    ?>
      <tr>
        <td><?= $size ?></td>
        <td><?= $this->items['volcounters']->$size ?></td>
      </tr>
    <?php
    endforeach;
    ?>
  </tbody>
</table>
<h2>TOTAL VOLUNTEER REGISTRATIONS: <?php echo $this->items['volTotalCount'] ?></h2>

<?php
if (count($this->items['missing']) > 0):
?>
  <h2>Error/missing list:</h2>
  <ol>
    <?php
    echo '<li>' . implode('</li><li>', $this->items['missing']) . '</li>';
    ?>
  </ol>
<?php
endif;
\ClawCorpLib\Helpers\Bootstrap::rawFooter();
