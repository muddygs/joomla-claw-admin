<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\PackageInfoTypes;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class SpeeddatinginfosModel extends EventconfigsModel
{
  /**
   * Get the master query for retrieving a list of PackageInfos of type speeddating.
   * 
   * Because "meta" contains all the subtypes for each event id, 
   * we use JSON_TABLE to extract each subtype into a row with subtype and eventId columns.
   *
   * @return  \Joomla\Database\DatabaseQuery
   *
   * @since   1.6
   */
  protected function getListQuery()
  {
    // MariaDB 10.5.8 does not support the JSON_TABLE function.
/*    $query = <<<SQL
    SELECT JSON_KEYS(a.meta) as metaKeys
FROM #__claw_packages AS a
WHERE a.eventAlias = 'c0424 'AND a.packageInfoType IN(4);
SQL;

    $this->db->setQuery($query);
    $metaKeys = $this->db->loadColumn();
*/

    $query = parent::getListQuery();

    $selects = [
      'a.*',
      //'jt.key_name',
//  10.6:    'JSON_UNQUOTE(JSON_EXTRACT(a.meta, CONCAT(\'$.\', jt.key_name, \'.role\'))) AS role',
  // 10.6:   'JSON_UNQUOTE(JSON_EXTRACT(a.meta, CONCAT(\'$.\', jt.key_name, \'.eventId\'))) AS eventId',
      'JSON_UNQUOTE(JSON_EXTRACT(a.meta, CONCAT(\'$.meta0.role\'))) AS role',
      'JSON_UNQUOTE(JSON_EXTRACT(a.meta, CONCAT(\'$.meta0.eventId\'))) AS eventId',
    ];

    // Since the base select cannot be appended, we just redeclare it here with everything
    $query->select(implode(',', $selects));

    // Future reference: if a unique row id is needed, use this:
    // $query->select('@rownum:=@rownum+1 AS rownum, j.subgroup, a.*');

    $packageInfoTypes = [
      PackageInfoTypes::speeddating->value,
    ];

    $query->where('a.packageInfoType IN (' . implode(',', $packageInfoTypes) . ')');
    // Join over the JSON table.
    // $query->join('INNER', 'JSON_TABLE(a.meta, \'$[*]\' COLUMNS (subgroup VARCHAR(50) PATH \'$\')) AS j');
    // TODO: Fix for MariaDB 10.5: $query->join('CROSS', 'JSON_TABLE(JSON_KEYS(a.meta), \'$[*]\' COLUMNS (key_name VARCHAR(255) PATH \'$\')) AS jt');

    // Initialize the row number variable.
    // $this->db->setQuery('SET @row_number = 0;');
    // $this->db->execute();

    return $query;
  }
}
