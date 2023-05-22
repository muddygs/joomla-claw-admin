<?php

namespace ClawCorpLib\Lib;

use ClawCorpLib\Enums\EbPublishedState;
use Joomla\CMS\Factory;

class registrants {
  // Returns simple list of registrants by event id
  // Ties back to the primary invoice (badge id)

  function __construct(public string $clawEventAlias)
  {
    $this->clawEventAlias = $clawEventAlias;
  }

  /**
   * Returns array of registrant records for a specific event id
   * @return array Registrant records
   */
  public static function byEventId(int $eventId, array $publishedStatus = [EbPublishedState::published]): array
  {
    $results = [];

    $db = Factory::getContainer()->get(DatabaseInterface::class);

    $eid = $db->q($eventId);

    $published = '(' . implode(',', $db->q($publishedStatus)) . ')';

    $q = $db->getQuery(true);

    $q->select($db->qn('user_id'))
      ->from($db->qn('#__eb_registrants'))
      ->where($db->qn('event_id') . '=' . $eid)
      ->where($db->qn('published') . ' IN ' . $published)
      ->order($db->qn(['published', 'invoice_number']));

    $db->setQuery($q);
    $userIds = array_unique($db->loadColumn());

    $clawEventAlias = clawEvents::eventIdToClawEventAlias($eventId);

    foreach ( $userIds as $uid )
    {
      $r = new registrant($clawEventAlias, $uid, [$eventId], true);
      $r->loadCurrentEvents();
      $results[] = $r;
    }

    return $results;
  }

  /** Returns list of registrant changes filtered on record update (ts_modified)
   * @param int $days_back Number of days for history to report
   * @return array Registrant records
   */
  public function byHistory(int $days_back): array
  {
    $results = [];

    $db = Factory::getContainer()->get(DatabaseInterface::class);

    $q = $db->getQuery(true);

    $q->select($db->qn(['user_id','event_id']))
      ->from($db->qn('#__eb_registrants'))
      ->where($db->qn('ts_modified') . " > ( NOW() - INTERVAL $days_back DAY ) ")
      ->where($db->qn('user_id') . ' != 0')
      ->order('ts_modified DESC')
      ->group($db->qn('event_id'));

    $db->setQuery($q);
    $rows = $db->loadObjectList();

    $mergeFields = ['Z_REFUND_TRANSACTION', 'Z_REFUND_DATE', 'Z_REFUND_AMOUNT'];
    
    foreach ($rows as $row) {
      $r = new registrant($this->clawEventAlias, $row->user_id, [$row->event_id], true);
      $r->loadCurrentEvents();

      if ( $r->count > 0) {
        $r->mergeFieldValues($mergeFields);
        $results[] = $r;
      }
    }

    return $results;
  }
}