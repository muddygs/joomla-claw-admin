<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

if (empty($tabs)) {
  Factory::getApplication()->enqueueMessage('No content to display', 'warning');
  return;
}

if (count($tabs) == 1) {
  echo $tabContents[0];
  return;
}

$guid = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 1, 8);

// Write the tabs
?>
<ul class="nav nav-pills nav-fill mb-3" id="pills-tab-<?= $guid ?>" role="tablist">
  <?php
  foreach ($tabs as $i => $title) {
    $active = $i == $tabActive ? 'active' : '';
    $aria = $i == $tabActive ? 'true' : 'false';
    $tabName = strtolower($title);
    $tabName = preg_replace("/[^\w]/", '', $tabName);
  ?>
    <li class="nav-item" role="presentation">
      <button class="nav-link <?= $active ?>" id="pills-<?= $tabName ?>-tab" data-bs-toggle="pill" data-bs-target="#pills-<?= $tabName ?>" role="tab" aria-controls="pills-<?= $tabName ?>" aria-selected="<?= $aria ?>"><?= $title ?></button>
    </li>
  <?php
  }
  ?>
</ul>
<?php


// Write the contents

reset($tabContents);


?>
<div class="tab-content" id="pills-tab-<?= $guid ?>Content">
<?php

foreach ($tabs as $i => $title) {
  $active = $i == $tabActive ? 'show active' : '';
  $tabName = strtolower($title);
  $tabName = preg_replace("/[^\w]/", '', $tabName);

  ?>
    <div class="tab-pane fade <?= $active ?>" id="pills-<?= $tabName ?>" role="tabpanel" aria-labelledby="pills-<?= $tabName ?>-tab">
      <?php
      echo current($tabContents);
      next($tabContents);
      ?>
    </div>
  <?php
}
?>
</div>

