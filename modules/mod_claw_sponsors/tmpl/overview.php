<?php

/**
 * @package     ClawCorp.Site
 * @subpackage  mod_claw_sponsors
 *
 * @copyright   (C) 2024 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use ClawCorpLib\Enums\SponsorshipType;

defined('_JEXEC') or die;

$typeOrdering = [
    SponsorshipType::Legacy_Master->value => [
        'heading' => 'Legacy Master Sponsors',
        'class' => 'legacymaster'
    ],
    SponsorshipType::Master->value => [
        'heading' => 'Master Sponsors',
        'class' => 'master'
    ],
    SponsorshipType::Legacy_Sustaining->value => [
        'heading' => 'Legacy Sustaining Sponsors',
        'class' => 'legacysus'
    ],
    SponsorshipType::Sustaining->value => [
        'heading' => 'Sustaining Sponsors',
        'class' => 'sus'
    ],
    SponsorshipType::Sponsor->value => [
        'heading' => 'Event Sponsors',
        'class' => 'sus'
    ],
    SponsorshipType::Media->value => [
        'heading' => 'Media Sponsors',
        'class' => 'sus'
    ],
];

foreach ( $typeOrdering AS $sponsorshipType => $sponsorshipTypeData ) {
  if (!count($sponsors[$sponsorshipType])) return;
  $heading = $sponsorshipTypeData['heading'];
  $class = $sponsorshipTypeData['class'];

  ?>
    <h1 style="text-align:center;" class="m-3"><?= $heading ?></h1>
    <div class="d-flex flex-row flex-wrap justify-content-center mb-3">
      <?php
      foreach ($sponsors[$sponsorshipType] as $row):
        $name = $row->name;
        $url = $row->link;
        $click = empty($url) ? '' : "style=\"cursor:pointer;\" onClick=\"javascript:window.open('$url','_blank')\"";

      ?>
        <div class="m-2 p-2 <?= $class ?>" style="background-color:#111;" <?= $click ?>>
          <div class="mb-1">
            <img src="<?= $row->logo_large ?>" class="img-fluid mx-auto d-block <?= $class ?>logo" alt="<?= $name ?>" title="<?= $name ?>" />
          </div>
          <p class="<?=$class?>name text-center" style="margin-bottom:0 !important;"><?=$name?></p>
        </div>
      <?php
      endforeach;
      ?>
    </div>
<?php
}

