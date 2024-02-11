<?php

namespace ClawCorp\Component\Claw\Administrator\Field;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;

use ClawCorpLib\Enums\ConfigFieldNames;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Supports an HTML list of CLAW events; based on com_menus@4.2.7
 *
 * @since  3.8.0
 */
class ConfigFieldNamesListField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $type = 'ConfigFieldNamesList';

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
        
        $currentValue = $this->__get('value') ?? 0;
        $options[] = HTMLHelper::_('select.option', '0', 'Select Field Section');

        foreach(ConfigFieldNames::toOptions() AS $enumInt => $title ) {
            $options[] = (object)[
                'value'    => $enumInt,
                'text'     => $title,
                'disable'  => false,
                'class'    => '',
                'selected' => $enumInt == $currentValue,
                'checked'  => $enumInt == $currentValue,
                'onclick'  => '',
                'onchange' => ''
            ];
		}

        // Because this is what ListField (parent) does; I do not know if necessary
        reset($options);

        return $options;
    }
}