<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

$displayData['items'] = $this->items;
$displayData['htmx'] = false;
$displayData['title'] = 'Volunteer Overview Report';

echo LayoutHelper::render('claw.volunteer_overview', $displayData);
