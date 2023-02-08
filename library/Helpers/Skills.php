<?php
namespace ClawCorpLib\Helpers;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Form\Field\SubformField;

class Skills {
  public static function GetPresentersList(DatabaseDriver $db): array {
    $query = $db->getQuery(true);

    $query->select($db->qn(['id','name']))
    ->from($db->qn('#__claw_presenters'))
    ->where($db->qn('published') . '=1')
    ->order('name ASC');

    $db->setQuery($query);
    return $db->loadObjectList() ?? [];
  }
}