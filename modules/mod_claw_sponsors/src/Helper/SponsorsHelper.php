<?php

/**
 * @package     ClawCorp.Module.Sponsors
 * @subpackage  mod_claw_sponsors
 *
 * @copyright   (C) 2024 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Module\Sponsors\Site\Helper;

use ClawCorpLib\Enums\SponsorshipType;
use ClawCorpLib\Lib\Sponsors;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;

\defined('_JEXEC') or die;

/**
 * Helper for mod_claw_sponsors
 */
class SponsorsHelper implements DatabaseAwareInterface
{
  use DatabaseAwareTrait;

  public function loadSponsors(): array
  {
    $sponsors = Sponsors::get();

    // Bin by type and leave no sponsor category type id empty
    $sponsorsByType = [];

    $sponsorsByType = array_fill_keys(SponsorshipType::valuesOrdered(), []);

    /** @var \ClawCorpLib\Lib\Sponsor */
    foreach ($sponsors as $sponsorItem) {
      $sponsorsByType[$sponsorItem->type->value][] = $sponsorItem;
    }

    return $sponsorsByType;
  }
}
