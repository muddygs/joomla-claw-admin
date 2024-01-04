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
class EquipmentrentalsModel extends EventconfigsModel
{
  /**
   * Get the master query for retrieving a list of equipment rental PackageInfo records.
   * 
   * Because "meta" contains all the subtypes for each event, we use JSON_TABLE to extract the subtypes into a row for each subtype.
   *
   * @return  \Joomla\Database\DatabaseQuery
   *
   * @since   1.6
   */
  protected function getListQuery()
  {
    $query = parent::getListQuery();

    $packageInfoTypes = [
      PackageInfoTypes::equipment->value,
    ];

    $query->where('a.packageInfoType IN (' . implode(',', $packageInfoTypes) . ')');

    return $query;
  }
}
