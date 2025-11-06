<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Lib;

use Joomla\CMS\Factory;
use ClawCorpLib\Iterators\PackageInfoArray;
use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Enums\EventPackageTypes;

final class PackageInfos
{
  public PackageInfoArray $packageInfoArray;

  public function __construct(
    public readonly array $eventAliases,
    public readonly array $filter = [],
    public readonly bool $publishedOnly = true,
    public bool $useDeployedTable = false,
  ) {
    if (!$eventAliases) {
      throw new \Exception("At least one event alias must be provided.");
    }

    foreach ($eventAliases as $alias) {
      if (!EventInfos::isEventAlias($alias)) {
        throw new \Exception("PackageInfos requires a valid event alias; $alias provided.");
      }
    }

    $this->loadPackageInfos();
  }

  private function loadPackageInfos()
  {
    $this->packageInfoArray = new PackageInfoArray();

    $tableName = $this->useDeployedTable ? PackageInfo::TABLE_NAME_DEPLOYED : PackageInfo::TABLE_NAME;

    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');
    $aliases = implode(',', (array)($db->q($this->eventAliases)));

    $query = $db->getQuery(true);

    $query->select('id')
      ->from($tableName)
      ->where('eventAlias IN (' . $aliases . ')');

    if (!empty($this->filter)) {
      $packageInfoTypesFilter = implode(',', array_map(fn($e) => $e->value, $this->filter));
      $query->where('packageInfoType IN (' . $packageInfoTypesFilter . ')');
    }

    if ($this->publishedOnly) {
      $query->where('published = ' . EbPublishedState::published->value);
    }

    $query->order('start ASC')->order('end ASC');

    $db->setQuery($query);

    $rows = $db->loadColumn();

    if (is_null($rows)) return;

    foreach ($rows as $row) {
      $this->packageInfoArray[$row] = new PackageInfo(id: $row, deployed: $this->useDeployedTable);
    }
  }

  public function byPackageType(EventPackageTypes $packageType): PackageInfoArray
  {
    $result = new PackageInfoArray();

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($this->packageInfoArray as $packageId => $packageInfo) {
      if ($packageInfo->packageInfoType == $packageType) {
        $result[$packageId] = clone $packageInfo;
      }
    }

    return $result;
  }
}
