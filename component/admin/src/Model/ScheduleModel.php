<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Enums\PackageInfoTypes;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Language\Text;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\Sponsors;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Lib\ScheduleRecord;

/**
 * Methods to handle a Schedule record
 */
class ScheduleModel extends AdminModel
{
  /**
   * The prefix to use with controller messages.
   *
   * @var    string
   * @since  1.6
   */
  protected $text_prefix = 'COM_CLAW';

  public function save($data)
  {
    $app = Factory::getApplication();
    // Handle array merges
    // https://github.com/muddygs/joomla-claw-admin/wiki/Joomla-Form-Load-Save-of-Checkboxes-and-Multi-Select-Lists

    $data['sponsors'] = json_encode($data['sponsors'] ?? []);
    $data['fee_event'] = implode(',', $data['fee_event']);

    try {
      $eventInfo = new EventInfo($data['event_alias']);
    } catch (\Exception) {
      $app->enqueueMessage('Invalid event alias.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      return false;
    }

    if (array_key_exists('day', $data) && in_array($data['day'], Helpers::days)) {
      $day = $eventInfo->modify($data['day'] ?? '');
      if ($day === false) {
        $app->enqueueMessage('Unknown day selected. Unable to convert to event date.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
        return false;
      }
    } else {
      $app->enqueueMessage('Unknown day selected. Unable to convert to event date.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      return false;
    }

    foreach (['start', 'end'] as $key) {
      if (array_key_exists($key, $data)) {
        $datetime = $day->modify($data[$key]);
        if ($datetime === false) {
          $app->enqueueMessage("Invalid $key.", \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
          return false;
        }
        $data["datetime_$key"] = $datetime->toSql();
      } else {
        $app->enqueueMessage("Invalid $key.", \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
        return false;
      }
    }

    unset($data['day']);
    unset($data['start']);
    unset($data['end']);

    // Process accessiblemedia field
    // Remove Joomla meta data - store only the path and filename
    if (!is_null($data['poster']) && !empty($data['poster']['imagefile'])) {
      $data['poster'] = explode("#", $data['poster']['imagefile'])[0];
      $orig = JPATH_ROOT . DIRECTORY_SEPARATOR . $data['poster'];

      $basename = basename($orig);
      // guarantee ending is .jpg
      $basename = preg_replace('/\.[a-zA-Z0-9]{3,4}$/', '.jpg', $basename);
      $basepath = dirname($orig);

      $thumbname = implode(DIRECTORY_SEPARATOR, [$basepath, 'thumb_' . $basename]);

      if (!Helpers::ProcessImageUpload(
        source: $orig,
        thumbnail: $thumbname,
        thumbsize: 200,
      )) {
        $app->enqueueMessage('Unable to save poster thumbnail file.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
        return false;
      }
    } else {
      $data['poster'] = '';
    }

    $record = new ScheduleRecord($data['id'] ?? 0);
    $record->fromSql((object)$data, true);

    try {
      $record->save();
      $this->setState($this->getName() . '.id', $record->id);
    } catch (\Exception $e) {
      $app->enqueueMessage($e->getMessage(), \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      return false;
    }

    return true;
  }

  /**
   * Method to get the record form.
   *
   * @param   array    $data      Data for the form.
   * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
   *
   * @return  Form|boolean  A Form object on success, false on failure
   *
   * @since   1.6
   */
  public function getForm($data = array(), $loadData = true)
  {
    // Get the form and add dynamic values

    $form = $this->loadForm('com_claw.schedule', 'schedule', array('control' => 'jform', 'load_data' => $loadData));
    if (empty($form)) return false;

    $event = $form->getField('event_alias')->value;
    if (empty($event)) $event = Aliases::current();
    $eventConfig = new EventConfig($event, [PackageInfoTypes::addon]);

    // Seed location list
    Helpers::sessionSet('eventAlias', $event);

    $sponsors = new Sponsors(published: true);

    /** @var \Joomla\CMS\Form\Field\ListField */
    $parentField = $form->getField('sponsors');
    /** @var \ClawCorpLib\Lib\Sponsor */
    foreach ($sponsors->sponsors as $s) {
      if ($s->published != EbPublishedState::published) continue;

      $parentField->addOption($s->name . ' (' . $s->type->toString() . ')', ['value' => $s->id]);
    }

    /** @var \Joomla\CMS\Form\Field\ListField */
    $parentField = $form->getField('event_id');
    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($eventConfig->packageInfos as $packageInfo) {
      if ($packageInfo->published != EbPublishedState::published || $packageInfo->eventId == 0) continue;

      $parentField->addOption($packageInfo->title, ['value' => $packageInfo->eventId]);
    }

    return $form;
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

    /** @var \Joomla\CMS\Application\AdministratorApplication $app */
    $app = Factory::getApplication();
    $data = $app->getUserState('com_claw.edit.schedule.data', array());

    // ChatGPT - this got complicated to make sure $data is an array with no private objects
    // that form rending tries to render but shouldn't be exposed
    // I'll need to eval further what all is necessary, but it at least allows manipulation
    // of the datetime into values for the form without a warning
    if (empty($data)) {
      $item = $this->getItem();

      if (is_array($item)) {
        $data = $item;
      } elseif ($item instanceof \Joomla\CMS\Table\Table) {
        // Public properties only
        $data = $item->getProperties(true);
      } elseif ($item instanceof \Joomla\Registry\Registry) {
        $data = $item->toArray();
      } elseif (is_object($item)) {
        // Public properties only
        $data = \Joomla\Utilities\ArrayHelper::fromObject($item, true);
      } else {
        $data = [];
      }

      // Strip leaked protected/private keys (those with leading null-bytes)
      foreach (array_keys($data) as $k) {
        if (strpos($k, "\0") !== false) {
          unset($data[$k]);
        }
      }

      // If anything like _errors sneaks in, drop it explicitly
      unset($data['_errors']);
    }

    // Pull out day, start/end times
    if (array_key_exists('datetime_start', $data)) {
      $data['day'] = Factory::getDate($data['datetime_start'])->format('D');
      $data['start'] = Factory::getDate($data['datetime_start'])->format('H:i');
      $data['end'] = Factory::getDate($data['datetime_end'])->format('H:i');
    }

    return $data;
  }

  public function getTable($name = 'Schedules', $prefix = '', $options = array())
  {
    if ($table = $this->_createTable($name, $prefix, $options)) return $table;

    throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
  }
}
