<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Helpers;

use Joomla\CMS\Factory;

class Locations
{
  const BLANK_LOCATION = -1;

  private static array $cache = [];

  public static function get(string $eventAlias): array
  {
    if (array_key_exists($eventAlias, Locations::$cache)) return Locations::$cache[$eventAlias];

    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = $db->getQuery(true);

    $query->select($db->qn(['id', 'value']))
      ->from($db->qn('#__claw_locations'))
      ->where($db->qn('published') . '=1')
      ->where($db->qn('event') . '=' . $db->q($eventAlias))
      ->order($db->qn('value'));

    $db->setQuery($query);
    Locations::$cache[$eventAlias] = $db->loadObjectList('id') ?? [];

    return Locations::$cache[$eventAlias];
  }
}
