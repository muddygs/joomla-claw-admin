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
class PackageinfosModel extends EventconfigsModel
{
  /**
   * Get the master query for retrieving a list of PackageInfo records.
   *
   * @return  \Joomla\Database\DatabaseQuery
   *
   * @since   1.6
   */
  protected function getListQuery()
  {
    $query = parent::getListQuery();

    $packageInfoTypes = [
      PackageInfoTypes::main->value,
      PackageInfoTypes::daypass->value,
      PackageInfoTypes::addon->value,
      PackageInfoTypes::coupononly->value,
      PackageInfoTypes::combomeal->value,
      PackageInfoTypes::passes->value,
      PackageInfoTypes::passes_other->value,
      PackageInfoTypes::vendormart->value,
    ];

    $query->where('a.packageInfoType IN (' . implode(',', $packageInfoTypes) . ')');
    return $query;
  }
}
