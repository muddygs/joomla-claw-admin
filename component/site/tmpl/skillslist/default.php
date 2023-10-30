<?php

use ClawCorpLib\Helpers\Bootstrap;

defined('_JEXEC') or die;

// Get menu heading information
echo $this->params->get('heading') ?? '';

if ( $this->list_type == 'simple' )
{
  echo $this->loadTemplate('simple');
  return;
}

// Set up the bootstrap tabs
$activeTab = 'Overview';
$tabs = [];
foreach ( $this->list->tabs AS $tab ) {
  $buttonActive = strtolower(str_replace(' ', '', $tab['name']));
  if ( $buttonActive == $this->urlTab ) {
    $activeTab = $tab['name'];
  }
  $tabs[] = $tab['name'];
}

$guid = Bootstrap::writePillTabList($tabs, $activeTab);

?>
<div class="tab-content" id="pills-tab-<?php echo $guid ?>Content">
<?php
    foreach ($this->list->tabs AS $tab) {
      $this->tabId = strtolower(str_replace(' ', '', $tab['name']));
      $active = $this->tabId == $this->urlTab ? 'show active' : '';

    ?>
      <div class="tab-pane fade <?= $active ?>" id="pills-<?= $this->tabId ?>" role="tabpanel" aria-labelledby="pills-<?= $this->tabId ?>-tab">
        <?php
          if ( $this->tabId == 'overview' ) {
            echo $this->loadTemplate('simple');
          } else {
            echo $this->loadTemplate('detailed');
          }
        ?>
      </div>
    <?php
    }
?>
</div>
