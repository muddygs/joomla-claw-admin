<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2022 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Helper;

use Joomla\Database\DatabaseDriver;
use RuntimeException;

/***
 * Location Helpers
 */

class LocationHelper
{
  public static function nextOrdering(DatabaseDriver $db, int $catid): int
  {
    $query = $db->getQuery(true);
    $query->select('MAX(ordering)')->from('#__claw_locations')->where('catid = '.$db->q($catid));
    $db->setQuery($query);
    $result = $db->loadResult();
    return $result ?? 1;
  }

  /**
   * Currently, returns list of top-level only locations (i.e., no sub-parents)
   * @param DatabaseDriver $db Database object
   * @return object Result columns: id, value
   * @throws RuntimeException 
   */
  public static function getCandidateParents(DatabaseDriver $db): array
  {
    $query = $db->getQuery(true);
    $query->select(['id','value'])->from('#__claw_locations')->where('catid = 0')->where('published = 1');
    $db->setQuery($query);
    $results = $db->loadObjectList('id');
    return $results;
  }
}