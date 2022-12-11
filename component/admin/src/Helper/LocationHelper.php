<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2022 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Helper;

use Joomla\Database\DatabaseInterface;
use RuntimeException;

/***
 * Location Helpers
 */

class LocationHelper
{
  public static function nextOrdering(DatabaseInterface $db, int $catid): int
  {
    $query = 'SELECT MAX(ordering) FROM #__claw_locations WHERE `catid`='.$db->q($catid);
    $db->setQuery($query);
    $result = $db->loadResult();
    return $result ?? 1;
  }

  /**
   * Currently, returns list of top-level only locations (i.e., no sub-parents)
   * @param DatabaseInterface $db Database object
   * @return object Result columns: id, value
   * @throws RuntimeException 
   */
  public static function getCandidateParents(DatabaseInterface $db): array
  {
    $query = <<<SQL
    SELECT id,value
    FROM #__claw_locations
    WHERE published = '1' AND catid = '0'
SQL;
    $db->setQuery($query);
    $results = $db->loadObjectList('id');
    return $results;
  }
}