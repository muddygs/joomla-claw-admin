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
     * Method to attach a JForm object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @see     \Joomla\CMS\Form\FormField::setup()
     * @since   3.8.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $result = parent::setup($element, $value, $group);

        if ($result == true) {
        }

        return $result;
    }

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
            $this->__set('value', Aliases::current);
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
            $tmp = [
                'value'    => $alias,
                'text'     => $title,
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