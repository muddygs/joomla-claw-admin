<?php

/**
 * @package     ClawCorp.Module.Vendors
 * @subpackage  mod_claw_vendors
 *
 * @copyright   (C) 2024 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;

?>
<div class="d-flex flex-row flex-wrap justify-content-center mb-3">
  <?php

  foreach ($vendors as $row) {
    $name = $row->name;

    $img = '';

    if ($row->logo !== '') {
      $i = HTMLHelper::cleanImageURL($row->logo);
      $img = $i->url;
    }

    $img = "<img src=\"$img\" class=\"card-img-top mx-auto d-block vendorlogo mt-1 mb-1\" alt=\"$name\" title=\"$name\">";
    $link = $row->link;

    $urlopen = '';
    $urlclose = '';

    if (!empty($link)) {
      $urlopen = "<a href=\"$link\" target=\"_blank\" rel=\"noopener\">";
      $urlclose = "</a>";
    }

  ?>
    <div class="p-2 vendorcard">
      <div class="card h-100 border border-warning" style="background-color:#444;">
        <?= $urlopen ?><?= $img ?><?= $urlclose ?>
        <div class="card-body border-top border-warning">
          <h5 class="card-title"><?= $urlopen ?><?= $name ?><?= $urlclose ?></h5>
          <p class="card-text d-none d-lg-block" style="font-size:0.8rem;margin-bottom:0 !important;"><?= $row->description ?></p>
        </div>
      </div>
    </div>
  <?php
  }
  ?>
</div>
