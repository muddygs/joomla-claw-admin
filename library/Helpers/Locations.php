<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Helpers;

use Joomla\CMS\Factory;

class Locations
{
  private static array $cache = [];

  public static int $blankLocation = -1;

  public function __construct(
    public readonly string $eventAlias
  ) {}


  public function GetLocationsList(): array
  {
    if (array_key_exists($this->eventAlias, Locations::$cache)) return Locations::$cache[$this->eventAlias];

    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = $db->getQuery(true);

    $query->select($db->qn(['id', 'value']))
      ->from($db->qn('#__claw_locations'))
      ->where($db->qn('published') . '=1')
      ->where($db->qn('event') . '=' . $db->q($this->eventAlias))
      ->order($db->qn('value'));

    $db->setQuery($query);
    Locations::$cache[$this->eventAlias] = $db->loadObjectList('id') ?? [];

    return Locations::$cache[$this->eventAlias];
  }

  public function GetLocationById(int $id): ?object
  {
    if ($id == Locations::$blankLocation) return (object)['value' => ''];
    if (!count(Locations::$cache)) $this->GetLocationsList();
    return array_key_exists($id, Locations::$cache[$this->eventAlias]) ? Locations::$cache[$this->eventAlias][$id] : null;
  }
}

