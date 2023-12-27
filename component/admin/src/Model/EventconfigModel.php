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
  protected $text_prefix = 'COM_CLAW_PACKAGEINFO';

  private $jsonFields = ['couponAccessGroups', 'meta'];

  public function save($data)
  {
    // Handle JSON data
    foreach ( $this->jsonFields as $field ) {
      if (isset($data[$field])) {
        // Always make sure we get an array
        if ( !is_array($data[$field]) ) $data[$field] = [$data[$field]];
        $data[$field] = json_encode($data[$field]);
      }
    }

    $eventInfo = new EventInfo($data['eventAlias']);

    $data['start'] = $this->getDatabase()->getNullDate();
    $data['end'] = $this->getDatabase()->getNullDate();

    $packageInfoType = PackageInfoTypes::FindValue($data['packageInfoType']);

    if ( $packageInfoType == PackageInfoTypes::addon || $packageInfoType == PackageInfoTypes::daypass ) {
      $start = $data['day'] . ' ' . $data['start_time'];
      $end = $data['day'] . ' ' . $data['end_time'];

      if ( $data['end_time'] < $data['start_time']) $end .= ' +1 day';

      $startDate = $eventInfo->modify( $start ?? '' );
      $data['start'] = $startDate !== false ? $startDate->toSql() : $data['start'];
      
      $data['start'] = $eventInfo->modify( $start ?? '' )->toSql();
      $data['end'] = $eventInfo->modify( $end ?? '' )->toSql();
    }

    if ( $data['start'] === false || $data['end'] === false ) {
      $app = Factory::getApplication();
      $app->enqueueMessage(Text::_('COM_CLAW_ERROR_INVALID_DATE'), 'error');
      return false;
    }

    $data['mtime'] = Helpers::mtime();

    return parent::save($data);
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
    /** @var $app AdministratorApplication */
    $app = Factory::getApplication();
    $data = $app->getUserState('com_claw.edit.packageinfo.data', []);
    if (empty($data)) {
      $data = $this->getItem();

      if ( !$data ) {
        throw new \Exception('Invalid record ID', 404);
      }

      // Handle JSON data
      foreach ( $this->jsonFields as $field ) {
        if ( property_exists($data, $field) && is_string($data->$field) ) $data->$field = json_decode($data->$field);

        // Remove empty values
        if ( is_array($data->$field) ) $data->$field = array_filter($data->$field);
      }

      // Speeddating subform needs meta in object format
      if ($data->packageInfoType == PackageInfoTypes::speeddating->value) {
        $meta = (object) [];

        for ( $i = 0; $i < sizeof($data->meta); $i++ ) {
          $key = 'meta'.$i;
          $meta->$key = (object) ['role' => $data->meta[$i]];
        }

        $data->meta = $meta;
      }

      // Convert start and end times to day, start_time, end_time
      if ( $data->packageInfoType == PackageInfoTypes::addon->value || $data->packageInfoType == PackageInfoTypes::daypass->value ) {
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