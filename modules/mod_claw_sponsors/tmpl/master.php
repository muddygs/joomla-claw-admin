<?php

/**
 * @package     ClawCorp.Module.Sponsors
 * @subpackage  mod_claw_sponsors
 *
 * @copyright   (C) 2024 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use ClawCorpLib\Enums\SponsorshipType;

$masterCells = count($sponsorsByType[SponsorshipType::Legacy_Master->value]) * 2 +
  count($sponsorsByType[SponsorshipType::Master->value]);
$masterwidth = 100.0;
if ($masterCells > 10) $masterwidth = round(100 * (10 / $masterCells));

?>
<style>
  @media screen and (max-width: 576px) {
    .mastersponsor2x {
      width: 100px;
    }

    .mastersponsor {
      width: 50px;
    }
  }

  @media screen and (min-width: 577px) {
    .mastersponsor2x {
      width: 150px;
    }

    .mastersponsor {
      width: 75px;
    }
  }

  @media screen and (min-width: 992px) {
    .mastersponsor2x {
      width: <?= $masterwidth * 2 ?>px;
    }

    .mastersponsor {
      width: <?= $masterwidth ?>px;
    }
  }
</style>

<div class="container">
  <div class="row">
    <div class="col-12">
      <div class="d-flex flex-column mb-2" id="master_sponsors">
        <div class="w-100 justify-content-center">
          <div class="text-white bg-danger master_sponsor_header">
            <h3 style="text-align:center; font-variant:all-petite-caps; font-size:14pt;">Master Sponsors</h3>
          </div>
        </div>
        <div class="d-flex flex-wrap justify-content-center">
          <?php
          foreach ([SponsorshipType::Legacy_Master->value, SponsorshipType::Master->value] as $type) {
            $class = match ($type) {
              SponsorshipType::Legacy_Master->value => 'mastersponsor2x',
              SponsorshipType::Master->value => 'mastersponsor',
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
