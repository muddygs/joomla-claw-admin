<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\View\Skillssubmissions;

defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Helpers;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use ClawCorpLib\Lib\Aliases;


/** @package ClawCorp\Component\Claw\Site\Controller */
class HtmlView extends BaseHtmlView
{
  /**
   * Execute and display a template script.
   *
   * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
   *
   * @return  void
   */
  public function display($tpl = null)
  {
    $this->state = $this->get('State');

    $app = Factory::getApplication();
    $this->params = $params = $app->getParams();
    $group = $params->get('se_group', '');
    $groups = $app->getIdentity()->getAuthorisedGroups();

    if ($group == 0 || !in_array($group, $groups)) {
      $app->enqueueMessage('You do not have permission to access this resource.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      $app->redirect('/');
    }

    /** @var \ClawCorp\Component\Claw\Site\Model\SkillssubmissionsModel */
    $model = $this->getModel();

    $this->bio = (object)[];

    // Try to get the most current bio for this user
    $bio = $model->GetPresenterBios(Aliases::current);
    if (count($bio) == 0) {
      $bio = $model->GetPresenterBios();
      if (count($bio) != 0) {
        $this->bio = $bio[0];
      }
    } else {
      $this->bio = $bio[0];
    }

    $this->classes = $model->GetPresenterClasses();
    
    // Check for errors.
    $errors = $this->get('Errors');
    if ($errors != null && count($errors)) {
      throw new GenericDataException(implode("\n", $errors), 500);
    }

    $this->eventInfo = $model->GetEventInfo();

    parent::display($tpl);
  }
}
