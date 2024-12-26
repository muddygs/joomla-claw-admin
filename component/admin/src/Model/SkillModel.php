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
use ClawCorpLib\Helpers\Locations;
use ClawCorpLib\Helpers\Mailer;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Skills\Presenters;
use Joomla\CMS\Component\ComponentHelper;

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
  protected $text_prefix = 'COM_CLAW_SKILL';

  public function validate($form, $data, $group = null)
  {
    $eventInfo = new EventInfo($data['event']);
    $presenters = Presenters::get($eventInfo, publishedOnly: true);

    $okToPublish = true;
    if (1 == $data['published']) {
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

    $data['mtime'] = Helpers::mtime();

    try {
      $info = new EventInfo($data['event']);
    } catch (\Exception) {
      $app->enqueueMessage('Invalid event alias.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      return false;
    }

    if (array_key_exists('day', $data) && in_array($data['day'], Helpers::getDays())) {
      $day = $info->modify($data['day'] ?? '');
      if ($day !== false) {
        $data['day'] = $day->toSql();
      }
    } else {
      $data['day'] = $this->getDatabase()->getNullDate();
    }

    // $data['presenters'] = implode(',', $data['presenters'] ?? []);
    $data['other_presenter_ids'] = json_encode($data['other_presenter_ids'] ?? []);

    if (!isset($data['location']) || !$data['location']) {
      $data['location'] = Locations::$blankLocation;
    }

    // If we're coming from the front end controller, email will be defined
    if ($app->isClient('site') && array_key_exists('email', $data)) {
      $this->email(new: $data['id'] == 0, data: $data);
    }

    return parent::save($data);
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
   *
   * @since   1.6
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
   *
   * @since   3.0
   * @throws  \Exception
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

  public function email(bool $new, array $data)
  {
    $params = ComponentHelper::getParams('com_claw');
    $notificationEmail = $params->get('se_notification_email', 'education@clawinfo.org');

    $alias = Aliases::current();

    try {
      $info = new EventInfo($alias);
    } catch (\Exception) {
      Factory::getApplication()->enqueueMessage('Email failure due to invalid event alias.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      return false;
    }

    $data['event'] = $info->description;

    $subject = $new ? '[New] ' : '[Updated] ';
    $subject .= $info->description . ' Class Submission - ';
    $subject .= $data['name'];

    $m = new Mailer(
      tomail: [$data['email']],
      toname: [$data['name']],
      bcc: [$notificationEmail],
      fromname: 'CLAW Skills and Education',
      frommail: $notificationEmail,
      subject: $subject,
    );

    // TODO: Use global config for this
    // TODO: Substitute the notification email

    $m->appendToMessage(
      '<p>Thank you for your interest in presenting at the CLAW/Leather Getaway Skills and Education Program.</p>' .
        '<p>Your class submission has been received and will be reviewed by the CLAW Education Committee.  You will be notified of the status of your application by email.</p>' .
        '<p>If you have any questions, please contact us at <a href="mailto:' . $notificationEmail . '">CLAW S&E Program Manager</a>.</p>'
    );

    $m->appendToMessage('<p>Class Submission Details:</p>');

    $m->appendToMessage($m->arrayToTable($data, ['id', 'uid', 'published', 'location', 'mtime', 'day', 'presenters', 'owner']));

    $m->send();
  }
}
