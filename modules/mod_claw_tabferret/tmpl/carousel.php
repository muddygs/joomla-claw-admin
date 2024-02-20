<?php
defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Bootstrap;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/** @var Joomla\Registry\Registry $params */
// var_dump($params);

$tabFields = $params->get('tab-fields', (object)[]);
$carouselInterval = $params->get('carousel_interval', 5);
$carouselRefresh = $params->get('carousel_refresh', 300);

/** @var \Joomla\CMS\Application */
$app = Factory::getApplication();
$document = $app->getDocument();
$document->setMetaData('refresh', $carouselRefresh, 'http-equiv');

$carouselContents = [];

foreach ( $tabFields as $tabField ) {
    switch ( $tabField->tab_type ) {
        case 'article':
            $articleId = $tabField->tab_article;
            $table = Bootstrap::loadContentById($articleId);

            if ( property_exists($table, 'introtext')) {
                $carouselContents[] = HTMLHelper::_('content.prepare', $table->introtext);
            }
            break;
        case 'module':
            $moduleId = $tabField->tab_module;
            $module = Bootstrap::loadModuleById($moduleId);
            $carouselContents[] = $module;
            break;
    }
}

if ( empty($carouselContents) ) {
    Factory::getApplication()->enqueueMessage('No content to display', 'warning');
    return;
}

if ( count($carouselContents) == 1 ) {
    echo $carouselContents[0];
    return;
}

Bootstrap::writeCarouselContents($carouselContents, $carouselInterval);
