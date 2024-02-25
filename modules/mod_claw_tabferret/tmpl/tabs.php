<?php
defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Bootstrap;
use Joomla\CMS\Factory;

if ( empty($tabs) ) {
    Factory::getApplication()->enqueueMessage('No content to display', 'warning');
    return;
}

if ( count($tabs) == 1 ) {
    echo $tabContents[0];
    return;
}

Bootstrap::writePillTabs($tabs, $tabContents);
