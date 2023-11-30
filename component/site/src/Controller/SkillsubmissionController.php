<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
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
use ClawCorpLib\Helpers\Skills;
use ClawCorpLib\Lib\Aliases;
use Joomla\CMS\Router\Route;

/**
 * Controller for a single skill record
 */
class SkillsubmissionController extends FormController
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

    if ($task == 'submit' && $input != null) {
      $this->save();
    }
  }

  public function save($key = null, $urlVar = null)
  {
    // Check for request forgeries.
    $this->checkToken();

    /** @var \Joomla\CMS\MVC\Model\FormModel */
    $siteModel = $this->getModel();
    $form = $siteModel->getForm();
    $app = Factory::getApplication();

    $input = $app->input;
    $data = $input->get('jform', [], 'array');
    $data = $form->filter($data);

    $validation = $siteModel->validate($form, $data);

    // Always cache the form data in case an error gets thrown below
    // 'formdata' is merged in the Model::loadFormData() method
    Helpers::sessionSet('formdata', json_encode($data));

    if ($validation === false) {
      $errors = $form->getErrors();

      foreach ($errors as $e) {
        $app->enqueueMessage($e->getMessage(), \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      }

      return false;
    }

    $data['description'] = trim($data['description']);

    // Replace CR/LF with LF for the purposes of our counting
    $tmpDescription = str_replace("\r\n", "\n", $data['description']);
    if ( mb_strlen($tmpDescription, 'UTF-8') > 500 ) {
      $app->enqueueMessage('Description is too long. Please shorten it to 500 characters or less.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      return false;
    }

    if ( strlen($data['title']) > 50 ) {
      $app->enqueueMessage('Title is too long. Please shorten it to 50 characters or less.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      return false;
    }

    // Setup items not included in site model
    $identity = $app->getIdentity();
    $data['owner'] = $data['uid'] = $identity->id;
    $data['email'] = $identity->email;

    $skills = new Skills($siteModel->db, Aliases::current(true));
    $bio = $skills->GetPresenterBios($data['owner']);
    $data['name'] = is_null($bio) ? '' : $bio[0]->name;

    $data['event'] = Aliases::current(true);
    $data['length_info'] = (int)$data['length'] ?? 60;

    // Get id from the session
    $data['id'] = Helpers::sessionGet('recordid', 0);

    if ($data['id'] == 0) {
      $data['published'] = 3; // New submission
      $data['submission_date'] = date('Y-m-d');
    }

    /** @var \ClawCorp\Component\Claw\Administrator\Model\SkillModel */
    $adminModel = $this->getModel('Skill', 'Administrator');
    $result = $adminModel->save($data);

    if ($result) {
      $this->setRedirect(Route::_('index.php?option=com_claw&view=skillssubmissions', 'Class submission save successful.'));
    } else {
      $app->enqueueMessage('An error occurred during save.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
    }
    
    return $result;
  }
}
