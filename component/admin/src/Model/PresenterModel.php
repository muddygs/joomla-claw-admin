<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2022 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Image\Image;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Helpers\Mailer;
use ClawCorpLib\Lib\Aliases;
use Joomla\Component\Finder\Administrator\Indexer\Parser\Html;
use LogicException;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class PresenterModel extends AdminModel
{
    /**
   * The prefix to use with controller messages.
   *
   * @var    string
   * @since  1.6
   */
  protected $text_prefix = 'COM_CLAW_PRESENTER';

  public function delete(&$pks)
  {
    // TODO: Delete presenter image files
    // $db = $this->getDatabase();
    // $query = $db->getQuery(true);
    // $query->select($db->quoteName('uid'))
    //   ->from($db->quoteName('#__claw_presenters'))
    //   ->where('id IN (:uid)')
    //   ->bind(':uid', implode(',', ));
    // $db->setQuery($query);
    // $result = $db->loadResult();

    parent::delete($pks);
  }

  public function validate($form, $data, $group = null)
  {
    // Handle readonly account data 
    if ( $data['uid_readonly_uid'] != 0 ) $data['uid'] = $data['uid_readonly_uid'];

    return parent::validate($form, $data, $group);
  }

  public function save($data)
  {
    $app = Factory::getApplication();

    $data['mtime'] = Helpers::mtime();

    $new = false;

    // New record handling
    if ( $data['id'] == 0 )
    {
      $data['submission_date'] = date("Y-m-d");

      // Check UID record is unique
      $db = $this->getDatabase();
      $query = $db->getQuery(true);
      $query->select($db->quoteName('id'))
        ->from($db->quoteName('#__claw_presenters'))
        ->where('uid = :uid')
        ->where('event = :event')
        ->bind(':uid', $data['uid'])
        ->bind(':event', $data['event']);
      $db->setQuery($query);
      $result = $db->loadResult();

      if ( $result ) {
        $app->enqueueMessage('Record for this presenter already exists for this event.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
        return false;
      }

      $new = true;
    }

    if ( array_key_exists('arrival', $data)) $data['arrival'] = implode(',',$data['arrival']);

    $input = $app->input;
    $files = $input->files->get('jform');
    $tmp_name = $files['photo_upload']['tmp_name'];
    $mime = $files['photo_upload']['type'];
    $error = $files['photo_upload']['error'];
  
    $orig = '';
    
    if ( 0 == $error ) {
      // Copy original out of tmp
      $orig = implode(DIRECTORY_SEPARATOR, [JPATH_ROOT, Aliases::presentersdir, 'orig', $data['uid'].'.jpg']);
      $result = copy($tmp_name, $orig);
      if ( !$result ) {
        $app->enqueueMessage('Unable to save original photo file.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
        return false;
      }

      $upload = implode(DIRECTORY_SEPARATOR, ['..', Aliases::presentersdir, 'orig', $data['uid']]);

      $upload .= match($mime) {
        'image/jpeg' => '.jpg',
        default => '.png',
      };

      if ( File::upload($tmp_name, $upload))
      {
        try {
          $output = implode(DIRECTORY_SEPARATOR, [JPATH_ROOT, Aliases::presentersdir, 'web', $data['uid'].'.jpg']);
          $image = new Image();
          $image->loadFile($orig);
          $image->resize(300, 300, false);
          $image->toFile($output, IMAGETYPE_JPEG, ['quality' => 80]);

          $data['photo'] = implode(DIRECTORY_SEPARATOR, [Aliases::presentersdir, 'web', $data['uid'].'.jpg']);
        } catch(LogicException $ex)
        {
          $app->enqueueMessage('Unable to save photo file.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
          return false;
        }
      } else {
        $app->enqueueMessage('Unable to save photo file.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
        return false;
      }
    }

    // If we're coming from the front end controller, email will be defined
    if ( array_key_exists('email', $data)) {
      $data['orig'] = $orig;
      $this->email(new: $new, data: $data);
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
  public function getForm($data = array(), $loadData = true)
  {
    // Get the form.
    $form = $this->loadForm('com_claw.presenter', 'presenter', array('control' => 'jform', 'load_data' => $loadData));

    if (empty($form))
    {
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
    $data = $app->getUserState('com_claw.edit.presenter.data', []);

    if (empty($data))
    {
      $data = $this->getItem();
    } else {
      // Handle readonly account data 
      if ( !array_key_exists('uid', $data) && $data['uid_readonly_uid'] ?? 0 != 0) {
        $data['uid'] = $data['uid_readonly_uid'];
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
    $name = 'Presenters';
    $prefix = 'Table';

    if ($table = $this->_createTable($name, $prefix, $options))
    {
      return $table;
    }

    throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
  }

  private function email(bool $new, array $data)
  {
    return;

    $subject = $new ? '[New] ' : '[Updated] ';
    $subject .= Aliases::defaultPrefix. ' Presenter Application - ';
    $subject .= $data['name'];

    $m = new Mailer(
      tomail: [$data['email']],
      toname: [$data['name']],
      fromname: 'CLAW Skills and Education',
      frommail: 'education@clawinfo.org',
      subject: $subject,
      attachments: [$data['photo']]
    );

    $header = <<< HTML
    <h1>CLAW Skills &amp; Education Bio Submission Record</h1>
    <p>Thank you for your submission. Your next step is to submit your classes. If you have previous classes, you
      can copy them from previous years by editing and resaving them for the current CLAW/Leather Getaway event.</p>
    <p>Go to <a href="https://www.clawinfo.org/index.php?option=com_claw&view=skillssubmissions">Submission Launcher</a> to proceed.</p> 
HTML;

    $m->appendToMessage($header);
    $m->appendToMessage('<p>Application Details:</p>');
    $m->appendToMessage($m->arrayToTable($data, ['photo','uid','email','id','mtime', 'orig']));
    
    $m->appendToMessage('<p>Questions? Please email <a href="mailto:education@clawinfo.org">education@clawinfo.org</a></p>');

    $m->send();
  }
}