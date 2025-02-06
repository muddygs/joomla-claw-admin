<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

$displayData['items'] = $this->items;
$displayData['htmx'] = true;
$displayData['htmxloaded'] = true;
$displayData['title'] = 'Select Shift to Add Below';

echo LayoutHelper::render('claw.volunteer_overview', $displayData);
