<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\View\Checkin;

\defined('_JEXEC') or die;

use ClawCorpLib\Checkin\Record;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use ClawCorpLib\Lib\Checkin;

class HtmxRecordView extends BaseHtmlView
{
  public string $search; // set in CheckinController
  public string $page; // set in CheckinController

  // For the template
  public string $error; // prepared error string from Checkin->
  public Record $record;
  public bool $isValid = false;

  function display($tpl = null)
  {
    try {
      $checkin = new Checkin($this->search);
      $this->isValid = $checkin->isValid;
    } catch (\Exception) {
      $this->isValid = false;
    }

    $this->record = new Record();
    $this->error = 'Record not loaded';

    if (isset($checkin->r)) {
      $this->record = $checkin->r->toRecord();
      $this->error = $checkin->r->error;
    }

    $this->setLayout('htmx_search_results');
    parent::display($tpl);
  }
}
