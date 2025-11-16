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
use Joomla\Database\DatabaseDriver;
use ClawCorpLib\Iterators\PackageInfoArray;
use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Traits\PackageDeploy;

final class PackageInfos
{
  use PackageDeploy;

  public PackageInfoArray $packageInfoArray;
  private DatabaseDriver $db;

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

    $this->db = Factory::getContainer()->get('DatabaseDriver');
    $this->loadPackageInfos();
  }

  protected function getDb(): DatabaseDriver
  {
    return $this->db;
  }

  private function loadPackageInfos()
  {
    $this->packageInfoArray = new PackageInfoArray();

    $tableName = $this->useDeployedTable ? $this->ensureDeployedTable(PackageInfo::TABLE_NAME) : PackageInfo::TABLE_NAME;

    /** @var \Joomla\Database\DatabaseDriver */
    $aliases = implode(',', (array)($this->db->q($this->eventAliases)));

    $query = $this->db->getQuery(true);

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

    $this->db->setQuery($query);

    $rows = $this->db->loadColumn();

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
