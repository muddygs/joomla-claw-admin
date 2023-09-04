<?php

use ClawCorpLib\Helpers\Bootstrap;
use ClawCorpLib\Lib\Aliases;

defined('_JEXEC') or die;

// Get menu heading information
echo $this->params->get('heading') ?? '';
$eventAlias = $this->params->get('event_alias') ?? Aliases::current();
$listType = $this->params->get('list_type') ?? 'simple';


if ( $this->list_type == 'simple' )
{
  echo $this->loadTemplate('simple');
  return;
}

// TODO: parse active from URI
$activeTab = 'Overview';

// Set up the bootstrap tabs
$tabs = [];
foreach ( $this->list->tabs AS $tab ) {
  $tabs[] = $tab['name'];
}

$guid = Bootstrap::writePillTabList($tabs, $activeTab);

?>
<div class="tab-content" id="pills-tab-<?php echo $guid ?>Content">
<?php
    foreach ($this->list->tabs AS $tab) {
      $active = $tab['name'] == $activeTab ? 'show active' : '';
      $this->tabId = strtolower(str_replace(' ', '', $tab['name']));

    ?>
      <div class="tab-pane fade <?= $active ?>" id="pills-<?= $this->tabId ?>" role="tabpanel" aria-labelledby="pills-<?= $this->tabId ?>-tab">
        <?php
          if ( $tab['name'] == 'Overview' ) {
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
