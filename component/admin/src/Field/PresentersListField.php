<?php

namespace ClawCorp\Component\Claw\Administrator\Field;

use Joomla\CMS\Form\Field\ListField;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Helpers\Skills;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Supports an HTML list of CLAW events; based on com_menus@4.2.7
 *
 * @since  3.8.0
 */
class PresentersListField extends ListField
{
  /**
   * The form field type.
   *
   * @var    string
   * @since  1.7.0
   */
  public $type = 'PresentersList';

  private array $presenters = [];

  /**
   * Method to get certain otherwise inaccessible properties from the form field object.
   *
   * @param   string  $name  The property name for which to get the value.
   *
   * @return  mixed  The property value or null.
   *
   * @since   3.8.0
   */
  // public function __get($name)
  // {
  //     switch ($name) {
  //         case 'menuType':
  //         case 'language':
  //         case 'published':
  //         case 'disable':
  //             return $this->$name;
  //     }

  //     return parent::__get($name);
  // }

  /**
   * Method to get the field input markup for a generic list.
   * Use the multiple attribute to enable multiselect.
   *
   * @return  string  The field input markup.
   *
   * @since   3.7.0
   */
  protected function getInput()
  {
    $data = $this->getLayoutData();

    $this->presenters = Skills::GetPresentersList($this->getDatabase());
    $currentValue = $this->__get('value');

    if ($currentValue && !array_key_exists($currentValue, $this->presenters)) {
      // Push this presenter into the list
      $presenter = Skills::GetPresenter($this->getDatabase(), $currentValue, Aliases::current(), false);
      if (!is_null($presenter)) {
        $p = (object)[
          'id' => $presenter->id,
          'uid' => $currentValue,
          'name' => $presenter->name,
          'published' => $presenter->published
        ];

        $this->presenters[$currentValue] = $p;
      }
    }

    $data['options'] = (array) $this->getOptions();
    return $this->getRenderer($this->layout)->render($data);
  }

  /**
   * Method to get the field options.
   *
   * @return  array  The field option objects.
   *
   * @since   3.7.0
   */
  protected function getOptions()
  {
    $options = parent::getOptions();

    foreach ($this->presenters as $p) {
      $tmp = [
        'value'    => $p->uid,
        'text'     => $p->name,
        'disable'  => false,
        'class'    => '',
        'selected' => false,
        'checked'  => false,
        'onclick'  => '',
        'onchange' => ''
      ];

      $options[] = (object)$tmp;
    }

    // Because this is what ListField (parent) does; I do not know if necessary
    reset($options);

    return $options;
  }
}
