<?php

namespace ClawCorp\Component\Claw\Administrator\Field;

use ClawCorpLib\Lib\EventConfig;
use Joomla\CMS\Form\Field\ListField;
use ClawCorpLib\Lib\Aliases;
use Joomla\CMS\HTML\HTMLHelper;

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
    protected bool $all;

    // Methods to add "all" parameter to field
    public function __get($name)
    {
        switch ($name) {
            case 'all':
                return $this->$name;
        }

        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        switch ($name) {
            case 'all':
                $this->all == strtolower($value) === 'true' ? true : false;
                break;

            default:
                parent::__set($name, $value);
        }
    }

    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $result = parent::setup($element, $value, $group);

        if ($result == true) {
            $this->all = strtolower($this->element['all'] ?? 'false') === 'true';
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

        $currentValue = $this->__get('value');
        if ( empty($currentValue) ) {
            $data['value'] = Aliases::current();
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
        
        $currentValue = $this->__get('value') ?? Aliases::current();
        $options[] = HTMLHelper::_('select.option', '0', 'Select Event');

        foreach(EventConfig::getTitleMapping() AS $alias => $title ) {
            $options[] = (object)[
                'value'    => $alias,
                'text'     => $title,
                'disable'  => false,
                'class'    => '',
                'selected' => $alias == $currentValue,
                'checked'  => $alias == $currentValue,
                'onclick'  => '',
                'onchange' => ''
            ];
		}

        if ( $this->all ) {
        $options[] = (object)[
                'value'    => 'all',
                'text'     => 'All Events',
                'disable'  => false,
                'class'    => '',
                'selected' => 'all' == $currentValue,
                'checked'  => 'all' == $currentValue,
                'onclick'  => '',
                'onchange' => ''
            ];
        }

        // Because this is what ListField (parent) does; I do not know if necessary
        reset($options);

        return $options;
    }
}