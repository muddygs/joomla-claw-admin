<?php

/**
 * @package     ClawCorp.Module.Tabferret
 * @subpackage  mod_claw_tabferret
 *
 * @copyright   (C) 2024 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

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

// https://stackoverflow.com/a/13212994
$guid = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 1, 8);

$intervalMS = $config->interval * 1000;

?>
<div class="accordion" id="<?= $guid ?>">
  <?php foreach ($tabs as $i => $tab) : ?>
    <div class="accordion-item">
      <h2 class="accordion-header" id="heading<?= $i ?>">
        <button class="accordion-button <?= $i == $tabActive ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $i ?>" aria-expanded="<?= $i == 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $i ?>">
          <?= $tab ?>
        </button>
      </h2>
      <div id="collapse<?= $i ?>" class="accordion-collapse collapse <?= $i == $tabActive ? 'show' : '' ?>" aria-labelledby="heading<?= $i ?>" data-bs-parent="#<?= $guid ?>">
        <div class="accordion-body">
          <?= $tabContents[$i] ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

</div>
<?php
\Joomla\CMS\HTML\HTMLHelper::_('bootstrap.collapse', '.selector', []);
