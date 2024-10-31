<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;

class EbCategoryListField extends ListField
{
  protected $type = "EbCategoryList";

  protected function getOptions()
  {
    $this->layout = 'joomla.form.field.list-fancy-select';

    /** @var \Joomla\Database\DatabaseDriver */
    $db    = Factory::getContainer()->get('DatabaseDriver');
    $query = $db->getQuery(true)
      ->select('id, parent AS parent_id, name AS title')
      ->from('#__eb_categories')
      ->where('published = 1')
      ->order('title ASC');
    $db->setQuery($query);
    $rows     = $db->loadObjectList();
    $children = [];

    // first pass - collect children
    foreach ($rows as $v) {
      $pt            = $v->parent_id;
      $list          = $children[$pt] ?? [];
      $list[]        = $v;
      $children[$pt] = $list;
    }

    $list      = HTMLHelper::_('menu.treerecurse', 0, '', [], $children, 9999, 0, 0);
    $options   = [];
    $options[] = HTMLHelper::_('select.option', '0', 'Select a Category');

    foreach ($list as $item) {
      $options[] = HTMLHelper::_('select.option', $item->id, '&nbsp;' . $item->treename);
    }

    // Convert the values

    if ($this->multiple && is_string($this->value)) {
      $this->value = explode(',', $this->value);
      $this->value = array_map('trim', $this->value);
    }

    return $options;
  }
}
