<?php

/**
 * @package     CLAW.Sponsors
 * @subpackage  plg_task_clawcorp
 *
 * @copyright   (C) 2024 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Task\ClawCorp\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Date\Date;
use ClawCorpLib\Lib\Authnetprofile;

use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventInfos;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

final class Tasks extends CMSPlugin implements SubscriberInterface
{
  use TaskPluginTrait;

  private DatabaseDriver $db;
  private string $eventAlias = '';
  private Date $now;
  private EventInfos $eventInfos;

  /**
   * @var string[]
   */
  private const TASKS_MAP = [
    'claw.checkshifts' => [
      'langConstPrefix' => 'PLG_TASK_CLAWCORP_CHECKSHIFTS',
      'method'          => 'checkshifts',
    ],
    'claw.profiles' => [
      'langConstPrefix' => 'PLG_TASK_CLAWCORP_PROFILES',
      'method'          => 'profiles',
      'form'            => 'profilesForm',
    ],
  ];

  /**
   * @var boolean
   */
  protected $autoloadLanguage = true;

  public function __construct(&$subject, $config = [])
  {
    parent::__construct($subject, $config);

    $this->db = Factory::getContainer()->get('DatabaseDriver');
    $this->eventAlias = Aliases::current(true);
    $this->eventInfos = new EventInfos();

    $this->now = Factory::getDate();
  }

  /**
   * @inheritDoc
   *
   * @return string[]
   *
   * @since 4.1.0
   */
  public static function getSubscribedEvents(): array
  {
    return [
      'onTaskOptionsList'    => 'advertiseRoutines',
      'onExecuteTask'        => 'standardRoutineHandler',
      'onContentPrepareForm' => 'enhanceTaskItemForm',
    ];
  }

  /**
   * @param   ExecuteTaskEvent  $event  The `onExecuteTask` event.
   *
   * @return integer  The routine exit code.
   */
  private function checkshifts(ExecuteTaskEvent $event): int
  {
    /** @var \Joomla\Component\Scheduler\Administrator\Task\Task */
    $task    = $event->getArgument('subject');

    $hidden = $this->updateShiftState();
    $shown = $this->updateShiftState(false);
    $this->logTask(sprintf('Shifts hidden:%d shown:%d Task ID %d', $hidden, $shown, $task->get('id')));
    return Status::OK;
  }

  /**
   * @param bool $hidemode True (default) hide full shifts / False show non-full shifts
   *
   * @return integer Count of shifts affected
   */

  private function updateShiftState(bool $hidemode = true): int
  {
    $changeShifts = [];

    /** @var \ClawCorpLib\Lib\EventInfo */
    foreach ($this->eventInfos as $eventInfo) {
      if ($eventInfo->alias != $this->eventAlias) continue;

      $prefix = $eventInfo->shiftPrefix;

      $query = $this->db->getQuery(true)
        ->select(['e.id'])
        ->from($this->db->quoteName('#__eb_events', 'e'))
        ->join('INNER', '(SELECT r.id, r.event_id, COUNT(r.id) AS mycount FROM #__eb_registrants r WHERE r.published = 1 GROUP BY r.event_id) tsum ON tsum.event_id = e.id')
        ->where('e.published = 1')
        ->where('e.event_capacity > 0')
        ->where('e.alias LIKE ' . $this->db->quote($prefix . '%'));

      if ($hidemode) {
        $query->where('tsum.mycount >= e.event_capacity');
        $query->where('e.hidden != 1');
      } else {
        $query->where('tsum.mycount < e.event_capacity');
        $query->where('e.hidden != 0');
      }

      $this->db->setQuery($query);

      $this->db->transactionStart(false);
      $changeShifts = $this->db->loadColumn();
      $this->db->transactionCommit();

      break;
    }

    if (count($changeShifts)) {
      $eventIds = join(',', $changeShifts);
      $hidden = $hidemode ? '1' : '0';

      $query = $this->db->getQuery(true)
        ->update($this->db->quoteName('#__eb_events'))
        ->set(['hidden = :hidden'])
        ->bind(':hidden', $hidden)
        ->where($this->db->quoteName('id') . ' IN (' . $eventIds . ')');

      $this->db->setQuery($query);
      $this->db->transactionStart(false);
      $this->db->execute();
      $this->db->transactionCommit();
    }

    return sizeof($changeShifts);
  }

  /**
   * @param   ExecuteTaskEvent  $event  The `onExecuteTask` event.
   *
   * @return integer  The routine exit code.
   */
  private function profiles(ExecuteTaskEvent $event): int
  {
    /** @var \Joomla\Component\Scheduler\Administrator\Task\Task */
    $task    = $event->getArgument('subject');

    $limit = (int) $event->getArgument('params')->limit ?? 20;
    $count = Authnetprofile::create(eventAlias: $this->eventAlias, maximum_records: $limit, cron: true);

    $this->logTask(sprintf('Profiles Created: %d Task ID %d', $count, $task->get('id')));

    return Status::OK;
  }
}
