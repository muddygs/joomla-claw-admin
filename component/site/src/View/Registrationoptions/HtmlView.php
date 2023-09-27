<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\View\Registrationoptions;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Helpers\Config;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\ClawEvents;

/** @package ClawCorp\Component\Claw\Site\Controller */
class HtmlView extends BaseHtmlView
{
  public \Joomla\CMS\Application\SiteApplication $app;
  public string $eventAlias;
  public string $action;
  public string $prefix;
  public \ClawCorpLib\Lib\ClawEvents $events;
  public \ClawCorpLib\Lib\EventInfo $eventInfo;

  
  public function display($tpl = null)
  {
    $this->app = Factory::getApplication();

    $input = $this->app->getInput();
    $this->eventAlias = $input->get('event', '', 'STRING');
    $this->action = $input->get('action', '', 'STRING');
    
    // Validate event and action
    $activeEvents = Config::getActiveEventAliases(mainOnly: true);
    if ( !in_array($this->eventAlias, $activeEvents) ) {
      $this->eventAlias = Aliases::current();
    }
    
    Helpers::sessionSet('eventAlias', $this->eventAlias);
    Helpers::sessionSet('eventAction', $this->action);
    $this->events = new ClawEvents($this->eventAlias);
    $this->eventInfo = $this->events->getClawEventInfo();
    // $this->prefix = strtolower($this->eventInfo->prefix);

    // Validate action
    try {
      EventPackageTypes::FindValue((int)($this->action ?? 0));
    }
    catch(\Exception $e) {
      $this->action = EventPackageTypes::none;
    }

    parent::display($tpl);
  }
}
