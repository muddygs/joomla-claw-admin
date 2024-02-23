<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2022 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


namespace ClawCorp\Component\Claw\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\Input\Input;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Utility\Utility;

/**
 * Controller for a single sponsor record
 *
 * @since  1.6
 */
class PresentersubmissionController extends FormController
{
  public function __construct(
    $config = [],
    MVCFactoryInterface $factory = null,
    ?CMSApplication $app = null,
    ?Input $input = null,
    FormFactoryInterface $formFactory = null
  ) {
    parent::__construct($config, $factory, $app, $input, $formFactory);

    $task = $input->getCmd('task');

    if ( $task == 'submit' && $input != null )
    {
      $this->save();
    }
  }

  public function save($key = null, $urlVar = null)
  {
    // Check for request forgeries.
    $this->checkToken();

    /** @var \Joomla\CMS\MVC\Model\SiteModel */
    $siteModel = $this->getModel();
    $form = $siteModel->getForm();
    /** @var \Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();

    $input = $app->input;
    $data = $input->get('jform', [], 'array');
    $data = $form->filter($data);
    $validation = $siteModel->validate($form, $data);

    // Always cache the form data in case an error gets thrown below
    // 'formdata' is merged in the Model::loadFormData() method
    Helpers::sessionSet('formdata', json_encode($data));

    if ( $validation === false ) {
      $errors = $form->getErrors();

      foreach ( $errors AS $e ) {
        $app->enqueueMessage($e->getMessage(), \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      }

      return false;
    }

    $files = $input->files->get('jform');

    if ( !$data['photo'] && (!array_key_exists('photo_upload', $files) || $files['photo_upload']['size'] < 1) )
    {
      $photo = Helpers::sessionGet('photo');
      if ( !$photo ) {
        $app->enqueueMessage('A representative photo is required', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      }
      return false;
    }

    $maxSize = Utility::getMaxUploadSize();
    if ( array_key_exists('photo_upload', $files ) && $files['photo_upload']['size'] > $maxSize ) {
      $app->enqueueMessage('The photo you uploaded is too large. Please upload a photo less than ' . $maxSize . ' bytes.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      return false;
    }
    
    $data['bio'] = trim($data['bio']);

    // Replace CR/LF with LF for the purposes of our counting
    $tmpBio = str_replace("\r\n", "\n", $data['bio']);
    if ( mb_strlen($tmpBio, 'UTF-8') > 1000 ) {
      $app->enqueueMessage('Biography is too long. Please shorten it to 1000 characters or less.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      return false;
    }

    $identity = $app->getIdentity();

    // Setup items not included in site model
    $data['uid'] = $identity->id;
    $data['email'] = $identity->email;
    $data['id'] = $input->get('id',0,'int');

    // If it's not the current event, we want to clear the ID and create
    // a new record.

    if ( $data['event'] != Aliases::current(true) ) {
      $data['id'] = 0;
    }

    $data['event'] = Aliases::current(true);
    
    if ( $data['id'] == 0 ) {
      $data['published'] = 3; // New submission
    }
    
    /** @var ClawCorp\Component\Claw\Administrator\Model\PresenterModel */    
    $adminModel = $this->getModel('Presenter','Administrator');
    $result = $adminModel->save($data);

    // Redirect to the main submission page
    if ( $result ) {
      $this->setRedirect(Route::_('index.php?option=com_claw&view=skillssubmissions', 'Biography save successful.'));
    } else {
      $app->enqueueMessage('An error occurred during save.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
    }

    return $result;
  }
}
