<?php

// Validates that a selected ACL has only a single group associated with it
//

namespace ClawCorp\Component\Claw\Administrator\Field\Rule;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;
use SimpleXMLElement;

class SimpleaclRule extends FormRule
{
  public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
  {
    return true; // Disable the rule for now
    
    /** @var \Joomla\CMS\Database\DatabaseDriver  */
    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = $db->getQuery(true);

    $query->select('rules')
      ->from('#__viewlevels')
      ->where('id = :id')
      ->bind(':id', $value);
    $db->setQuery($query);
    $result = $db->loadResult();

    if ($result === null) {
      return false;
    }

    $rules = json_decode($result, true);

    if (count($rules) === 1) {
      return true;
    }

    return false;
  }
}
