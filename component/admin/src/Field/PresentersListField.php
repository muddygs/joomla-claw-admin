<?php

namespace ClawCorp\Component\Claw\Administrator\Field;

use ClawCorpLib\Helpers\Helpers;
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

    // Get the list of presenters
    $eventAlias = Helpers::sessionGet('eventAlias');
    $skills = new Skills($this->getDatabase(), $eventAlias);
    $this->presenters = $skills->GetPresentersList();
    
    $currentValue = $this->__get('value');

    if ( $currentValue ) {
      $currentPresenters = is_array($currentValue) ? $currentValue : explode(',', $currentValue);
      foreach ( $currentPresenters AS $currentPresenter ) {
        if ( !array_key_exists($currentPresenter, $this->presenters) ) {
          // Push this presenter into the list
          $presenter = $skills->GetPresenter($currentPresenter, false);
          if (!is_null($presenter)) {
            $p = (object)[
              'id' => $presenter->id,
              'uid' => $currentPresenter,
              'name' => $presenter->name,
              'published' => $presenter->published
            ];

            $this->presenters[$currentPresenter] = $p;
          }
        }
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
    $currentValue = $this->__get('value');
    $value = is_array($currentValue) ? $currentValue : explode(',', $currentValue);

    foreach ($this->presenters as $p) {
      $tmp = [
        'value'    => $p->uid,
        'text'     => $p->name,
        'disable'  => false,
        'class'    => '',
        'selected' => in_array($p->uid, $value),
        'checked'  => in_array($p->uid, $value),
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
