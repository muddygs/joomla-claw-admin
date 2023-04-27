<?php

namespace ClawCorp\Component\Claw\Administrator\Field;

use Joomla\CMS\Form\Field\ListField;
use ClawCorpLib\Lib\Aliases;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Supports an HTML list of CLAW events; based on com_menus@4.2.7
 *
 * @since  3.8.0
 */
class ClawEventsListField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $type = 'ClawEventsList';


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

        $data['options'] = (array) $this->getOptions();

        // VALID for other menus -- future reference $db = $this->getDatabase();
        $currentValue = $this->__get('value');
        if ( $currentValue === '' ) {
            $data['value'] = Aliases::current;
        }

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

        foreach(Aliases::eventTitleMapping AS $alias => $title ) {
            $options[] = (object)[
                'value'    => $alias,
                'text'     => $title,
                'disable'  => false,
                'class'    => '',
                'selected' => false,
                'checked'  => false,
                'onclick'  => '',
                'onchange' => ''
            ];
		}

        // Because this is what ListField (parent) does; I do not know if necessary
        reset($options);

        return $options;
    }
}