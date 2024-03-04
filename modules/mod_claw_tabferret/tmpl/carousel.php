<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/** @var \Joomla\CMS\Application */
$app = Factory::getApplication();
$document = $app->getDocument();
$document->setMetaData('refresh', $config->refresh, 'http-equiv');

if ( empty($tabs) ) {
    Factory::getApplication()->enqueueMessage('No content to display', 'warning');
    return;
}

if ( count($tabs) == 1 ) {
    echo $tabContents[0];
    return;
}

$guid = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'),1,8);

$intervalMS = $config->interval * 1000;

?>
<div id="<?= $guid ?>" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-config='{"pause":false}'>
<div class="carousel-inner">
<?php foreach ($tabContents as $i => $content) : ?>
  <div class="carousel-item <?= $i == 0 ? 'active' : '' ?>" data-bs-interval="<?= $intervalMS ?>">
    <?= $content ?>
  </div>
<?php endforeach; ?>
</div>
</div>
<?php
\Joomla\CMS\HTML\HTMLHelper::_('bootstrap.carousel', '.selector', []);
