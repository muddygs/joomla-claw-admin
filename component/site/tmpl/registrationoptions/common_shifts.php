<?php

use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Lib\ClawEvents;

if ($this->eventConfig->eventInfo->onsiteActive):
?>
  <div class="border border=info text-white p-3 mx-2 mb-2 rounded">
    <span style="font-size:large;">
      <i class="fa fa-info-circle fa-2x"></i>&nbsp;After you register,
      please go to the Volunteer Assignments Desk to get your shift assignments.
    </span>
    <br>Remember:
    <ul class="mt-2">
      <li>You must show up to your shift <u>15 minutes early</u></li>
      <li>Allow time between shifts for break and travel</li>
      <li>CLAW reserves the right to change your shifts (with sufficient notification)</li>
    </ul>
  </div>
<?php
else:
?>
  <div class="border border=info text-white p-3 mx-2 mb-2 rounded">
    <span style="font-size:large;"><i class="fa fa-info-circle fa-2x"></i>&nbsp;Select shifts from <u>one category</u>, then times that work for you. Please note the requirements listed for each shift.</span><br>Remember:
    <ul class="mt-2">
      <li>You must show up to your shift <u>15 minutes early</u></li>
      <li>Allow time between shifts for break and travel</li>
      <li>CLAW reserves the right to change your shifts (with sufficient notification)</li>
    </ul>
  </div>
  <?php

  $categoryIds = $this->eventConfig->eventInfo->eb_cat_shifts;
  if ($this->eventPackageType == EventPackageTypes::volunteersuper) {
    $categoryIds = array_merge($categoryIds, $this->eventConfig->eventInfo->eb_cat_supershifts);
  }
  $categoryInfo = ClawEvents::getRawCategories($categoryIds);

  ?>
  <div class="row row-cols-1 row-cols-sm-2 g-2 px-4 py-2">
    <?php
    foreach ($categoryInfo as $alias => $info):
      $url = $this->shiftsBaseUrl . $alias;
      $name = $info->name;
      $description = $info->meta_description;
    ?>
      <div class="col d-flex flex-wrap">
        <a href="<?= $url ?>" class="w-100 btn btn-outline-danger" role="button">
          <h2><?= $name ?></h2>
          <small class="text-center" style="color:#ffae00"><?= $description ?></small>
        </a>
      </div>
    <?php
    endforeach;
    ?>
  </div>
  <div class="clearfix"></div>
<?php

endif;
