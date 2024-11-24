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

\ClawCorpLib\Helpers\Bootstrap::rawHeader([], ['/media/com_claw/css/print_letter.css']);

?>
<?php
foreach (array_keys($this->items['therapists']) as $userid) {
?>
  <h1 class="text-center"><?= $this->items['therapists'][$userid] ?></h1>
  <table class="table table-striped table-bordered">
    <thead class="table-dark">
      <tr>
        <th scope="col" class="col-3">Day</th>
        <th scope="col" class="col-3">Start Time</th>
        <th scope="col" class="col-3">End Time</th>
        <th scope="col" class="col-3">Registrant</th>
      </tr>
    </thead>
    <tbody>
      <?php

      foreach ($this->items['days'] as $day) {
        if (!array_key_exists($userid, $this->items[$day])) continue;
        foreach ($this->items[$day][$userid] as $event) {
      ?>
          <tr>
            <td><?= $day ?></td>
            <td><?= $event['start_time'] ?></td>
            <td><?= $event['end_time'] ?></td>
            <?php
            if (empty($event['registrant'])):
            ?>
              <td>Open</td>
            <?php
            else:
            ?>
              <td class="table-info">
                <?= $event['registrant']['name'] ?> / <?= $event['registrant']['email'] ?>
              </td>
            <?php
            endif;
            ?>
          </tr>
      <?php
        }
      }
      ?>
    </tbody>
  </table>
<?php
}
\ClawCorpLib\Helpers\Bootstrap::rawFooter();
