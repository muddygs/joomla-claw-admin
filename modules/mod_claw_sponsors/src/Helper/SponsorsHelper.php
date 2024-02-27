<?php

/**
 * @package     CLAW.Sponsors
 * @subpackage  mod_claw_sponsors
 *
 * @copyright   (C) 2023 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Module\Sponsors\Site\Helper;

use ClawCorpLib\Enums\SponsorshipType;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_claw_sponsors
 *
 * @since  1.5
 */
class SponsorsHelper implements DatabaseAwareInterface
{
  use DatabaseAwareTrait;

  public function loadSponsors(): array
  {
    $db = $this->getDatabase();

    # Enumerate SponsorshipType and create empty arrays
    $sponsors = [];
    $validTypes = [];

    foreach (SponsorshipType::cases() as $type) {
      $sponsors[$type->value] = [];
      $validTypes[] = $type->value;
    }

    $query = $db->getQuery(true);

    $query->select('*')
      ->from('#__claw_sponsors')
      ->where('published = 1')
      ->order('type, ordering');

    $db->setQuery($query);
    $rows = $db->loadObjectList();

    // TODO: validate $row->type is a valid enum value
    foreach ($rows as $row) {
      if (!in_array($row->type, $validTypes)) continue;
      $sponsors[$row->type][] = $row;
    }

    return $sponsors;
  }

}
