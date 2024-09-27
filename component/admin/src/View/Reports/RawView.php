<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\View\Reports;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class RawView extends BaseHtmlView
{
  public function display($tpl = null)
  {
    $this->state = $this->get('State');

    /** @var \ClawCorp\Component\Claw\Administrator\Model\ReportsModel */
    $this->model = $this->getModel('Reports');

    $this->db = Factory::getContainer()->get('DatabaseDriver');

    $layout = $this->getLayout();

    switch ($layout) {
      case 'speeddating':
        $this->items = $this->model->getSpeedDatingItems();
        break;
      case 'shirts':
        $this->items = $this->model->getShirtSizes();
        break;
      case 'volunteer_overview':
        $this->items = $this->model->getVolunteerOverview();
        break;
      case 'volunteer_detail':
        $this->items = $this->model->getVolunteerOverview();
        break;
      case 'meals':
        $this->items = $this->model->getMealCounts();
        break;
      case 'csv_presenters':
      case 'csv_classes':
        $input = Factory::getApplication()->getInput();
        $this->publishedOnly = $input->getBool('published_only', true);
        break;
      case 'csv_artshow':
        $this->items = $this->model->getArtShowSubmissions();
        break;
      case 'spa':
        $this->items = $this->model->getSpaSchedule();
      default:
        break;
    }

    parent::display($tpl);
  }
}
