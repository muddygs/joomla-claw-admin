<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\PackageInfoTypes;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\EventInfo;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Language\Text;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class EventconfigModel extends AdminModel
{
  /**
   * The prefix to use with controller messages.
   *
   * @var    string
   * @since  1.6
   */
  protected $text_prefix = 'COM_CLAW';

  private $jsonFields = ['couponAccessGroups', 'meta'];

  protected $context = 'com_claw.edit.packageinfo';

  public function __construct($config = array())
  {
    if (isset($config['context'])) {
      $this->context = $config['context'];
    }
    parent::__construct($config);
  }

  public function save($data)
  {
    // Handle JSON data
    foreach ($this->jsonFields as $field) {
      if (isset($data[$field])) {
        // Always make sure we get an array
        if (!is_array($data[$field])) $data[$field] = [$data[$field]];
        $data[$field] = json_encode($data[$field]);
      }
    }

    $eventInfo = new EventInfo($data['eventAlias']);

    $packageInfoType = PackageInfoTypes::FindValue($data['packageInfoType']);

    switch ($packageInfoType) {
      case PackageInfoTypes::main:
      case PackageInfoTypes::combomeal:
      case PackageInfoTypes::coupononly:
        $data['start'] = $this->getDatabase()->getNullDate();
        $data['end'] = $this->getDatabase()->getNullDate();
        break;

      case PackageInfoTypes::addon:
      case PackageInfoTypes::daypass:
      case PackageInfoTypes::passes:
        $start = $data['day'] . ' ' . $data['start_time'];
        $end = $data['day'] . ' ' . $data['end_time'];

        if ($data['end_time'] < $data['start_time']) $end .= ' +1 day';

        $startDate = $eventInfo->modify($start ?? '');
        $data['start'] = $startDate !== false ? $startDate->toSql() : $data['start'];

        $data['start'] = $eventInfo->modify($start ?? '')->toSql();
        $data['end'] = $eventInfo->modify($end ?? '')->toSql();
        break;

      case PackageInfoTypes::speeddating:
        $start = $data['day'] . ' ' . $data['start_time'];
        $end = $data['day'] . ' ' . $data['start_time'] . ' +45 minutes';

        $data['start'] = $eventInfo->modify($start ?? '')->toSql();
        $data['end'] = $eventInfo->modify($end ?? '')->toSql();
        break;

      case PackageInfoTypes::sponsorship:
      case PackageInfoTypes::equipment:
        break;

      case PackageInfoTypes::spa:
        $start = $data['day'] . ' ' . $data['start_time'];
        $end = $data['day'] . ' ' . $data['start_time'] . ' +' . (int)$data['length'] . ' minutes';

        $data['start'] = $eventInfo->modify($start ?? '')->toSql();
        $data['end'] = $eventInfo->modify($end ?? '')->toSql();
        break;

      default:
        throw (new \Exception("Unhandled PackageInfoTypes value: $packageInfoType->value"));
    }

    if ($data['start'] === false || $data['end'] === false) {
      $app = Factory::getApplication();
      $app->enqueueMessage(Text::_('COM_CLAW_ERROR_INVALID_DATE'), 'error');
      return false;
    }

    $data['mtime'] = Helpers::mtime();

    $result = parent::save($data);
    if ($result) {
      Factory::getApplication()->setUserState($this->context . '.data', []);
    }

    return $result;
  }

  /**
   * Method to get the record form. Implemented by child classes.
   *
   */
  public function getForm($data = array(), $loadData = true)
  {
    die('Must be implemented in child class.');
  }

  /**
   * Method to get the data that should be injected in the form.
   *
   * @return  mixed  The data for the form.
   *
   * @since   1.6
   */
  protected function loadFormData()
  {
    // Check the session for previously entered form data.
    $data = Factory::getApplication()->getUserState($this->context . '.data', []);
    if (empty($data)) {
      $data = $this->getItem();

      if (!$data) {
        throw new \Exception('Invalid record ID', 404);
      }

      // Handle JSON data
      foreach ($this->jsonFields as $field) {
        if (property_exists($data, $field) && is_string($data->$field)) $data->$field = json_decode($data->$field);

        // Remove empty values
        if (is_array($data->$field)) $data->$field = array_filter($data->$field);
      }

      // Equipment Rental needs meta as a string
      if ($data->packageInfoType == PackageInfoTypes::equipment->value) {
        $data->meta = (is_array($data->meta) ? $data->meta[0] : $data->meta);
      }

      // Package types with specific start and end times that need to be converted
      // into day, start time and end time
      $timePackageTypes = [
        PackageInfoTypes::addon->value,
        PackageInfoTypes::daypass->value,
        PackageInfoTypes::speeddating->value,
        PackageInfoTypes::passes->value,
        PackageInfoTypes::spa->value,
      ];

      // Convert start and end times to day, start_time, end_time
      if (in_array($data->packageInfoType, $timePackageTypes)) {
        $start = new Date($data->start);
        $end = new Date($data->end);

        $data->day = strtolower($start->format('D'));
        $data->start_time = $start->format('H:i');
        $data->end_time = $end->format('H:i');
      }
    }

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
  public function getTable($name = '', $prefix = '', $options = [])
  {
    $name = 'Packageinfos';
    // $prefix = 'Table';

    if ($table = $this->_createTable($name, $prefix, $options)) return $table;

    throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
  }
}
