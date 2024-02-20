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

foreach ( $tabFields as $tabField ) {
    switch ( $tabField->tab_type ) {
        case 'article':
            $articleId = $tabField->tab_article;
            $table = Bootstrap::loadContentById($articleId);

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
