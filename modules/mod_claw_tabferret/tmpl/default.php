<?php
defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Bootstrap;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Table\Table;

/** @var Joomla\Registry\Registry $params */
// var_dump($params);

$tabFields = $params->get('tab-fields', (object)[]);

$tabs = [];
$tabContents = [];

foreach ( $tabFields as $tabField ) {
    // var_dump($tabField);

    $tabs[] = $tabField->tab_title;

    if ( $tabField->tab_type == 'article') {
      // load article content
      $articleId = $tabField->tab_article;
      $table = Bootstrap::loadContentById($articleId);
      $introtext = $table->introtext;

      // Prepare the introtext for display
      $introtext = HTMLHelper::_('content.prepare', $introtext);
      $tabContents[] = $introtext;
    }
}

Bootstrap::writePillTabs($tabs, $tabContents);
