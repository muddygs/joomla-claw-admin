<?php

namespace ClawCorp\Component\Claw\Administrator\Field;

use ClawCorpLib\Helpers\Helpers;
use Joomla\CMS\Form\Field\ListField;

defined('_JEXEC') or die;

class UsersByGroupField extends ListField
{
  public $type = 'UsersByGroup';

  protected $groupnames;

  // protected function getGroups()
  // {
  //   $groups = [];

  //   foreach ($this->groupnames as $g) {
  //     $groups[] = Helpers::getGroupId($this->getDatabase(), $g);
  //   }
  //   return $groups;
  // }

  /**
   * Method to get certain otherwise inaccessible properties from the form field object.
   *
   * @param   string  $name  The property name for which to get the value.
   *
   * @return  mixed  The property value or null.
   *
   * @since   3.8.0
   */
  public function __get($name)
  {
    switch ($name) {
      case 'groupnames':
        return $this->$name;
    }

    return parent::__get($name);
  }

  /**
   * Method to set certain otherwise inaccessible properties of the form field object.
   *
   * @param   string  $name   The property name for which to set the value.
   * @param   mixed   $value  The value of the property.
   *
   * @return  void
   *
   * @since   3.8.0
   */
  public function __set($name, $value)
  {
    switch ($name) {
      case 'groupnames':
        $this->groupnames = (array) $value;
        break;

      default:
        parent::__set($name, $value);
    }
  }

  public function setup(\SimpleXMLElement $element, $value, $group = null)
  {
    $result = parent::setup($element, $value, $group);

    if ($result == true) {
      $this->groupnames  = $this->element['groupnames'] ? explode(',', strtolower((string) $this->element['groupnames'])) : [];
    }

    return $result;
  }

  /**
   * Customized method to populate the field option groups.
   *
   * @return  array  The field option objects as a nested array in groups.
   */
  protected function getOptions()
  {
    $this->listItems = parent::getOptions();

    $currentValue = $this->__get('value');

    foreach ($this->groupnames as $g) {
      $users = Helpers::getUsersByGroupName($this->getDatabase(), $g);

      foreach ( $users AS $u ) {
      $this->listItems[] = (object)[
        'value'    => $u->user_id,
        'text'     => $u->name,
        'disable'  => false,
        'class'    => '',
        'selected' => $u->user_id == $currentValue ? true : false,
        'checked'  => $u->user_id == $currentValue ? true : false,
        'onclick'  => '',
        'onchange' => ''
      ];
    }
  }

    return $this->listItems;
  }
}
