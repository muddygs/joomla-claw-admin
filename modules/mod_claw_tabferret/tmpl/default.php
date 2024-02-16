<?php
defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Bootstrap;

/** @var Joomla\Registry\Registry $params */
var_dump($params);

$tabFields = $params->get('tab-fields', (object)[]);
var_dump($tabFields);
