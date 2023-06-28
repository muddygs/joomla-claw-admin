<?php

namespace ClawCorp\Component\Claw\Administrator\Field;

use ClawCorpLib\Helpers\Helpers;
use Joomla\CMS\Form\Field\ListField;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Supports an HTML list of CLAW events; based on com_menus@4.2.7
 *
 * @since  3.8.0
 */
class ClawDaysListField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $type = 'ClawDaysList';

    protected $dayfilter;

    private $days;

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
            case 'dayfilter':
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
            case 'dayfilter':
                $this->dayfilter = (string) $value;
                break;

            default:
                parent::__set($name, $value);
        }
    }


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
        $this->days = Helpers::getDays();

        $result = parent::setup($element, $value, $group);

        if ($result == true) {
            $this->dayfilter  = $this->element['dayfilter'] ? explode(',', strtolower((string) $this->element['dayfilter'])) : [];
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
        $currentValue = $this->__get('value');

        if ($currentValue != '' && !in_array($currentValue, $this->days)) {
            $datetime = date_create($currentValue);
            if ($datetime !== false) {
                $currentValue = strtolower(date_format($datetime, 'D'));
                $this->__set('value', $currentValue);
                $data['value'] = $currentValue;
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

        $currentValue = $this->__get('value') ?? '';

        foreach ($this->days as $day) {
            if (!count($this->dayfilter) || in_array($day, $this->dayfilter)) {
                $options[] = (object)[
                    'value'    => $day,
                    'text'     => ucfirst($day),
                    'disable'  => false,
                    'class'    => '',
                    'selected' => $day == $currentValue ? true : false,
                    'checked'  => $day == $currentValue ? true : false,
                    'onclick'  => '',
                    'onchange' => ''
                ];
            }
        }

        // Because this is what ListField (parent) does; I do not know if necessary
        reset($options);

        return $options;
    }
}
