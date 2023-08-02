<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Helpers\Bootstrap;
use ClawCorpLib\Helpers\Skills;

// Get menu heading information
echo $this->params->get('heading') ?? '';
$eventAlias = $this->params->get('event_alias') ?? Aliases::current;
$listType = $this->params->get('list_type') ?? 'simple';

echo 'detailed'; return;


