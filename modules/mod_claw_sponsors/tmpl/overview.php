<?php

/**
 * @package     ClawCorp.Module.Sponsors
 * @subpackage  mod_claw_sponsors
 *
 * @copyright   (C) 2024 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use ClawCorpLib\Enums\SponsorshipType;

defined('_JEXEC') or die;

$currentType = null;

?>
<style>
  .masterlogo,
  .mastername {
    max-width: 150px;
  }

  .mastername,
  .susname {
    border-top: 2px solid #ffae00;
  }

  .legacymasterlogo {
    max-width: 300px;
  }

  .suslogo,
  .susname {
    max-width: 100px;
  }

  .legacysuslogo {
    max-width: 200px;
  }
</style>
<?php

/** @var \ClawCorpLib\Lib\Sponsor $sponsorItem */
foreach ($sponsorsByType as $type => $sponsorItems) {
  /** @var \ClawCorpLib\Enum\SponsorshipType $type */
  $class = match ($type) {
    SponsorshipType::Legacy_Master->value => 'legacymaster',
    SponsorshipType::Master->value => 'master',
    SponsorshipType::Legacy_Sustaining->value => 'legacysus',
    default => 'sus'
  };

  $heading = $sponsorItems[0]->type->toString() . ' Sponsor';

?>
  <h1 style="text-align:center;" class="m-3"><?= $heading ?></h1>
  <div class="d-flex flex-row flex-wrap justify-content-center mb-3">
    <?php

    /** @var \ClawCorpLib\Lib\Sponsor */
    foreach ($sponsorItems as $sponsorItem) {
      $name = $sponsorItem->name;
      $url = $sponsorItem->link;
      $click = empty($url) ? '' : "style=\"cursor:pointer;\" onClick=\"javascript:window.open('$url','_blank')\"";
    ?>
      <div class="m-2 p-2 <?= $class ?>" style="background-color:#111;" <?= $click ?>>
        <div class="mb-1">
          <img src="<?= $sponsorItem->logo_large ?>" class="img-fluid mx-auto d-block <?= $class ?>logo" alt="<?= $name ?>" title="<?= $name ?>" />
        </div>
        <p class="<?= $class ?>name text-center" style="margin-bottom:0 !important;"><?= $name ?></p>
      </div>
    <?php
    }

    ?>
  </div>
<?php
}
