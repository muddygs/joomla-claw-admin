<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\PackageInfoTypes;
use Joomla\Database\ParameterType;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class SpainfosModel extends EventconfigsModel
{
  /**
   * Get the master query for retrieving a list of equipment rental PackageInfo records.
   * 
   * @return  \Joomla\Database\DatabaseQuery
   *
   * @since   1.6
   */
  protected function getListQuery()
  {
    $query = parent::getListQuery();

    // TODO: See SpeeddatinginfosModel for comments on this code, update when service
    // provider updates
    //$selects = [
    //'a.*',
    //'jt.key_name',
    //'JSON_UNQUOTE(JSON_EXTRACT(a.meta, CONCAT(\'$.\', jt.key_name, \'.userid\'))) AS userid',
    //'JSON_UNQUOTE(JSON_EXTRACT(a.meta, CONCAT(\'$.\', jt.key_name, \'.services\'))) AS services',
    //];

    //$query->select(implode(',', $selects));

    $packageInfoTypes = [
      PackageInfoTypes::spa->value,
    ];

    $query->where('a.packageInfoType IN (' . implode(',', $packageInfoTypes) . ')');
    //
    // Join over the JSON table.
    //$query->join('CROSS', 'JSON_TABLE(JSON_KEYS(a.meta), \'$[*]\' COLUMNS (key_name VARCHAR(255) PATH \'$\')) AS jt');

    $day = $this->getState('filter.day');
    if ($day != null) {
      date_default_timezone_set('etc/UTC');
      $dayInt = date('w', strtotime($day));

      if ($dayInt !== false) {
        $dayInt++; // PHP to MariaDB conversion
        $query->where('DAYOFWEEK(a.start) = :dayint');
        $query->bind(':dayint', $dayInt, ParameterType::INTEGER);
      }
    }

    return $query;
  }
}
