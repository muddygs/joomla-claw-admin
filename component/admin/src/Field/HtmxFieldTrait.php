<?php

namespace ClawCorp\Component\Claw\Administrator\Field;

trait HtmxFieldTrait
{
  // HTMx Core and Additional Attributes@1.9.12
  // See: https://htmx.org/reference/#attributes
  // TODO: hx_on* not handled

  // Core Attributes
  protected $hx_get;
  protected $hx_post;
  protected $hx_push_url;
  protected $hx_select_oob;
  protected $hx_swap;
  protected $hx_swap_oob;
  protected $hx_target;
  protected $hx_trigger;
  protected $hx_vals;

  // cache
  private $_hx_properties = [];
  private $_hx_values = [];

  // Additional Attributes
  /* TODO: 
  hx_boost
hx_confirm
hx_delete
hx_disable
hx_disabled_elt
hx_disinherit
hx_encoding
hx_ext
hx_headers
hx_history
hx_history_elt
hx_include
hx_indicator
hx_params
hx_patch
hx_preserve
hx_prompt
hx_put
hx_replace_url
hx_request
hx_sse
hx_sync
hx_validate
hx_vars
hx_ws
  */

  public function getHtmxClassProperties(bool $TranslateUnderscore = false): array
  {
    if ( count($this->_hx_properties) ) {
      return $this->_hx_properties;
    }

    $this->_hx_properties = [];
    $classProperties = get_class_vars(__CLASS__);
    foreach ($classProperties as $propertyName => $propertyValue) {
      if (str_starts_with($propertyName, 'hx_')) {
        $this->_hx_properties[] = $TranslateUnderscore ? str_replace('_', '-', $propertyName) : $propertyName;
      }
    }
    return $this->_hx_properties;
  }

  public function getHtmxClassValues(): array
  {
    if ( count($this->_hx_values) ) return $this->_hx_values;

    $this->_hx_values = [];
    $classProperties = array_keys(get_class_vars(__CLASS__));
    foreach ($classProperties as $propertyName) {
      if (str_starts_with($propertyName, 'hx_')) {
        $this->_hx_values[$propertyName] = $this->$propertyName;
      }
    }
    $this->_hx_values = array_filter($this->_hx_values);

    return $this->_hx_values;
  }

  public function __get($name)
  {
    $p = $this->getHtmxClassProperties();

    if (in_array($name, $p)) {
      return $this->$name;
    }

    if (in_array($name, $this->getHtmxClassProperties())) {
      return null;
    }

    // If used in template, parent is not set
    $parent = get_parent_class($this);
    return $parent !== false ? parent::__get($name) : '';
  }

  public function __set($name, $value)
  {
    $p = $this->getHtmxClassProperties();

    if ( in_array($name, $p) ) {
      $this->$name = (string) $value;
      return;
    }

    parent::__set($name, $value);
  }

  public function setup(\SimpleXMLElement $element, $value, $group = null)
  {
    $result = parent::setup($element, $value, $group);

    if (!$result) return false;

    foreach ($this->getHtmxClassProperties() as $attributeName) {
      $this->__set($attributeName, $element[$attributeName]);
    }

    return true;
  }

  protected function getLayoutData()
  {
    $data = parent::getLayoutData();
    $data['options'] = (array) $this->getOptions();

    switch($this->type) {
      case 'HtmxList':
        // $data['options'] = (array) $this->getOptions();
        break;
      case 'HtmlCheckboxes':
        $data['checkedOptions'] = \is_array($this->value) ? $this->value : explode(',', (string) $this->value);
        $data['hasValue'] = isset($this->value) && !empty($this->value);
        // $data['options'] = (array) $this->getOptions();
        break;
    }

    return array_merge($data, $this->getHtmxClassValues());
  }

  protected function getInput()
  {
    return $this->getRenderer($this->layout)->render($this->getLayoutData());
  }

  protected function getOptions()
  {
    return parent::getOptions();
  }

  public function addOption($text, $attributes = [])
  {
    return parent::addOption($text, $attributes);
  }
}

?>