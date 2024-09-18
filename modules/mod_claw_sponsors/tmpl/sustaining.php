<?php

/**
 * @package     COM_CLAW
 * @subpackage  mod_claw_sponsors
 *
 * @copyright   (C) 2024 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use ClawCorpLib\Enums\SponsorshipType;

$sustainingCells = count($sponsorsByType[SponsorshipType::Legacy_Sustaining->value]) * 2 +
  count($sponsorsByType[SponsorshipType::Sustaining->value]);

?>
<style>
  @media screen and (max-width: 576px) {
    .sustainingsponsor2x {
      width: 100px;
    }

    .sustainingsponsor {
      width: 50px;
    }
  }

  @media screen and (min-width: 577px) {
    .sustainingsponsor2x {
      width: 100px;
    }

    .sustainingsponsor {
      width: 50px;
    }
  }

  @media screen and (min-width: 992px) {
    .sustainingsponsor2x {
      width: calc(2*1080px / <?= $sustainingCells ?>);
      max-width: 150px;
    }

    .sustainingsponsor {
      width: calc(1080px / <?= $sustainingCells ?>);
      max-width: 75px;
    }
  }
</style>

<div class="container">
  <div class="row">
    <div class="col-12">
      <div class="d-flex flex-column mb-3 justify-content-center" id="sustaining_sponsors">
        <div class="w-100 justify-content-center">
          <div class="flex-fill text-white bg-danger sponsor_header">
            <h3 style="text-align:center; font-variant:all-petite-caps; font-size:14pt;">Sustaining Sponsors</h3>
          </div>
        </div>

        <div class="d-flex flex-wrap justify-content-center">
          <?php
          foreach ([SponsorshipType::Legacy_Sustaining->value, SponsorshipType::Sustaining->value] as $type) {
            $class = match ($type) {
              SponsorshipType::Legacy_Sustaining->value => 'sustainingsponsor2x',
              SponsorshipType::Sustaining->value => 'sustainingsponsor',
            };

            /** @var \ClawCorpLib\Lib\Sponsor */
            foreach ($sponsorsByType[$type] as $sponsor) {
              $logo = $sponsor->logo_small;
              $url = $sponsor->link;
          ?>
              <div class="<?= $class ?>">

                <?php
                if (!empty($url)) {
                ?>
                  <a href="<?= $sponsor->link ?>" target="_blank" rel="noopener">
                  <?php
                }
                  ?>
                  <img src="<?= $logo ?>" class="img-fluid mx-auto d-block <?= $class ?>logo" alt="<?= $sponsor->name ?>" title="<?= $sponsor->name ?>" />
                  <?php
                  if (!empty($url)) {
                  ?>
                  </a>
                <?php
                  }
                ?>
              </div>
          <?php
            }
          }
          ?>
        </div>
      </div>
    </div>
  </div>
</div>
