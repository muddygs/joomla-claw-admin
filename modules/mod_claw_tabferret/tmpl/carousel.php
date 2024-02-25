<?php
defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Bootstrap;
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

Bootstrap::writeCarouselContents($tabContents, $config->interval);