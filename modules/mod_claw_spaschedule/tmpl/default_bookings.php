<?php

/**
 * @package     ClawCorp.Module.Spaschedule
 * @subpackage  mod_claw_spaschedule
 *
 * @copyright   (C) 2024 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

?>
<h1>Your Bookings</h1>
<table class="table table-dark table-striped table-responsive">
  <thead>
    <tr>
      <th>Event Title</th>
      <th>First Name</th>
      <th>Email</th>
    </tr>
  </thead>
  <tbody>

    <?php

    /** @var \ClawCorpLib\Lib\PackageInfo $packageInfo */
    foreach ($bookings as $booking):
      $title = $booking['title'];
      $fname = $booking['fname'];
      $email = $booking['email'];
    ?>
      <tr>
        <th><?= $title ?></th>
        <th><?= $fname ?></th>
        <th><?= $email ?></th>
      </tr>
    <?php

    endforeach;

    ?>
  </tbody>
</table>
