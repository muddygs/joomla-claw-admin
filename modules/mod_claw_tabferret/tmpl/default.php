<?php
defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Bootstrap;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/** @var Joomla\Registry\Registry $params */
// var_dump($params);

$tabFields = $params->get('tab-fields', (object)[]);

$tabs = [];
$tabContents = [];

// Use global configuration time zone to filter articles based on publication dates
$config = Factory::getApplication()->getConfig();
$timeZone = $config->get('offset');
$timeZoneObject = new DateTimeZone($timeZone);

foreach ( $tabFields as $tabField ) {
    switch ( $tabField->tab_type ) {
        case 'article':
            $articleId = $tabField->tab_article;
            $table = Bootstrap::loadContentById($articleId);

            $publishUp = $table->publish_up;
            $publishDown = $table->publish_down;
            $now = Factory::getDate('now', $timeZoneObject);

            if ( $publishUp && $publishUp > $now ) {
                continue 2;
            }

            if ( $publishDown && $publishDown < $now ) {
                continue 2;
            }

            if ( property_exists($table, 'introtext')) {
                $tabContents[] = HTMLHelper::_('content.prepare', $table->introtext);
                $tabs[] = $tabField->tab_title;
            }
            break;
        case 'module':
            $moduleId = $tabField->tab_module;
            $module = Bootstrap::loadModuleById($moduleId);
            $tabs[] = $tabField->tab_title;
            $tabContents[] = $module;
            break;
    }
}

if ( empty($tabs) ) {
    Factory::getApplication()->enqueueMessage('No content to display', 'warning');
    return;
}

if ( count($tabs) == 1 ) {
    echo $tabContents[0];
    return;
}

Bootstrap::writePillTabs($tabs, $tabContents);
