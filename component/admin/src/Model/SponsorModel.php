<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Language\Text;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Enums\SponsorshipType;

class SponsorModel extends AdminModel
{
  protected $text_prefix = 'COM_CLAW';

  public function save($data)
  {
    if ($data['expires'] == $this->getDatabase()->getNullDate() || $data['expires'] == '')
      $data['expires'] = '0000-00-00';

    $data['mtime'] = Helpers::mtime();

    return parent::save($data);
  }

  public function getForm($data = array(), $loadData = true)
  {
    // Get the form.
    $form = $this->loadForm('com_claw.sponsor', 'sponsor', ['control' => 'jform', 'load_data' => $loadData]);

    if (empty($form)) {
      return false;
    }

    /** @var \Joomla\CMS\FormField */
    $p = $form->getField('type');
    foreach (SponsorshipType::cases() as $type) {
      $p->addOption($type->toString(), ['value' => $type->value]);
    }

    return $form;
  }

  protected function loadFormData()
  {
    // Check the session for previously entered form data.
    /** @var Joomla\CMS\Application\AdministratorApplication */
    $app = Factory::getApplication();
    $data = $app->getUserState('com_claw.edit.sponsor.data', []);

    if (empty($data)) {
      $data = ($this->getItem());
    }

    $data = (object)$data;

    // TODO: Fix calendar date (w/o time) in "null" case
    if (!property_exists($data, 'expires') || str_starts_with($data->expires, '0000-00-00'))
      $data->expires = $this->getDatabase()->getNullDate();

    return $data;
  }

  /**
   * Method to get a table object, load it if necessary.
   *
   * @param   string  $name     The table name. Optional.
   * @param   string  $prefix   The class prefix. Optional.
   * @param   array   $options  Configuration array for model. Optional.
   *
   * @return  Table  A Table object
   *
   * @since   3.0
   * @throws  \Exception
   */
  public function getTable($name = '', $prefix = '', $options = array())
  {
    $name = 'Sponsors';
    $prefix = 'Table';

    if ($table = $this->_createTable($name, $prefix, $options)) {
      return $table;
    }

    throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
  }
}
