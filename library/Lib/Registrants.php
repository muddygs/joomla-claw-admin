<?php

namespace ClawCorpLib\Lib;

use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Lib\ClawEvents;
use Joomla\CMS\Factory;

class Registrants
{
  /**
   * Returns array of registrant records for a specific event id
   * @param int $eventId Event ID
   * @param array $publishedStatus Array of EbPublishedState (default: published)
   * @return array Registrant records
   */
  public static function byEventId(int $eventId, array $publishedStatus = []): array
  {
    $results = [];
    $publishedStatus = $publishedStatus ?: [EbPublishedState::published];

    $db = Factory::getContainer()->get('DatabaseDriver');

    $eid = $db->q($eventId);

    $published = '(' . implode(',', $db->quote(array_column($publishedStatus, 'value'))) . ')';

    $q = $db->getQuery(true);

    $q->select($db->qn('user_id'))
      ->from($db->qn('#__eb_registrants'))
      ->where($db->qn('event_id') . '=' . $eid)
      ->where($db->qn('published') . ' IN ' . $published)
      ->order($db->qn(['published', 'invoice_number']));

    $db->setQuery($q);
    $userIds = array_unique($db->loadColumn());

    $clawEventAlias = ClawEvents::eventIdToClawEventAlias($eventId);

    if ( $clawEventAlias === false ) {
      die("Event from Event ID cannot be determined");
    }

    foreach ( $userIds as $uid )
    {
      $r = new Registrant($clawEventAlias, $uid, [$eventId], true);
      $r->loadCurrentEvents();
      $results[] = $r;
    }

    return $results;
  }

  public static function getRegistrantCount(int $eventId): int
  {
    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = $db->getQuery(true);
    $query->SELECT('COUNT(*)')
      ->FROM($db->qn('#__eb_registrants'))
      ->WHERE($db->qn('event_id') . '=' . $db->q($eventId))
      ->WHERE($db->qn('published') . '=' . $db->q(EbPublishedState::published->value));

    $db->setQuery($query);
    $count = $db->loadResult();
    return $count;
  }

  /** Returns list of registrant changes filtered on record update (ts_modified)
   * @param int $days_back Number of days for history to report
   * @return array Registrant records
   */
  public static function byHistory(string $eventAlias, int $days_back): array
  {
    $results = [];

    $db = Factory::getContainer()->get('DatabaseDriver');

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
      $r = new registrant($eventAlias, $row->user_id, [$row->event_id], true);
      $r->loadCurrentEvents();

      if ( $r->count > 0) {
        $r->mergeFieldValues($mergeFields);
        $results[] = $r;
      }
    }

    return $results;
  }
}