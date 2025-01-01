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

use ClawCorpLib\Enums\SkillOwnership;
use ClawCorpLib\Enums\SkillPublishedState;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Language\Text;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Helpers\Locations;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Skills\Presenter;
use ClawCorpLib\Skills\Presenters;
use ClawCorpLib\Skills\Skill;

/**
 * Methods to handle processing a skill submission
 *
 */
class SkillModel extends AdminModel
{
  /**
   * The prefix to use with controller messages.
   *
   * @var    string
   */
  protected $text_prefix = 'COM_CLAW';

  public function validate($form, $data, $group = null)
  {
    $eventInfo = new EventInfo($data['event']);
    $presenters = Presenters::get(eventInfo: $eventInfo, publishedOnly: true);

    $okToPublish = true;
    if (SkillPublishedState::published->value == $data['published']) {
      $okToPublish = $presenters->offsetExists($data['presenter_id']);

      if ($okToPublish && array_key_exists('other_presenter_ids', $data)) {
        foreach ($data['other_presenter_ids'] as $copresenter) {
          $okToPublish = $presenters->offsetExists($copresenter);
          if (!$okToPublish) break;
        }
      }
    }

    if (!$okToPublish) {
      $app = Factory::getApplication();
      $app->enqueueMessage('Class cannot be published until all presenters are published.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      return false;
    }

    return parent::validate($form, $data, $group);
  }

  public function save($data)
  {
    $app = Factory::getApplication();

    if ($app->isClient('administrator')) {
      $requestedId = $data['id'];
    } else {
      $requestedId = $this->getState($this->getName() . '.id');

    }

    if ($requestedId) {
      $skill = new Skill($requestedId);
    } else {
      $skill = new Skill(0);
    }

    try {
      $eventInfo = new EventInfo($data['event']);
    } catch (\Exception) {
      $app->enqueueMessage('Invalid event alias.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      return false;
    }

    if (array_key_exists('day', $data) && in_array($data['day'], Helpers::getDays())) {
      $day = $eventInfo->modify($data['day'] ?? '');
      if ($day !== false) {
        $data['day'] = $day;
      }
    } else {
      $data['day'] = null;
    }

    if (!isset($data['location']) || !$data['location']) {
      $data['location'] = Locations::BLANK_LOCATION;
    }

    $skill->day = $data['day'];
    $skill->ownership = SkillOwnership::tryFrom($data['ownership']) ?? SkillOwnership::user;
    $skill->published = SkillPublishedState::tryFrom($data['published']) ?? SkillPublishedState::new;
    $skill->other_presenter_ids = $data['other_presenter_ids'] ?? [];
    $skill->av = $data['av'] ?? 0;
    $skill->length_info = $data['length_info'] ?? 60;
    $skill->location = $data['location'] ?? Locations::BLANK_LOCATION;
    $skill->presenter_id = $data['presenter_id'];
    $skill->audience = $data['audience'] ?? '';
    $skill->category = $data['category'] ?? '';
    $skill->comments = $data['comments'] ?? '';
    $skill->copresenter_info = $data['copresenter_info'] ?? '';
    $skill->description = $data['description'] ?? '';
    $skill->equipment_info = $data['equipment_info'] ?? '';
    $skill->event = $data['event'];
    $skill->requirements_info = $data['requirements_info'] ?? '';
    $skill->time_slot = $data['time_slot'] ?? '';
    $skill->title = $data['title'];
    $skill->track = $data['track'] ?? '';
    $skill->type = $data['type'] ?? '';

    try {
      $skill->save();
      $this->setState($this->getName() . '.id', $skill->id);

      if ($app->isClient('site')) {
        $presenter = new Presenter($skill->presenter_id);
        $skill->emailResults($eventInfo, $presenter, $requestedId == 0);
      }
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
   */
  public function getForm($data = [], $loadData = true)
  {
    // Get the form.
    $form = $this->loadForm('com_claw.skill', 'skill', array('control' => 'jform', 'load_data' => $loadData));

    if (empty($form)) {
      return false;
    }

    return $form;
  }

  /**
   * Method to get the data that should be injected in the form.
   *
   * @return  mixed  The data for the form.
   */
  protected function loadFormData()
  {
    // Check the session for previously entered form data.
    /** @var Joomla\CMS\Application\AdministratorApplication */
    $app = Factory::getApplication();
    $data = $app->getUserState('com_claw.edit.skill.data', []);

    if (empty($data)) {
      $data = $this->getItem();
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
   */
  public function getTable($name = '', $prefix = '', $options = array())
  {
    $name = 'Skills';
    $prefix = 'Table';

    if ($table = $this->_createTable($name, $prefix, $options)) {
      return $table;
    }

    throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
  }
}
