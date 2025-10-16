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
use ClawCorpLib\Enums\SponsorshipType;
use ClawCorpLib\Iterators\SponsorArray;

class Sponsors
{
  public SponsorArray $sponsors;

  public function __construct(
    public readonly bool $published = true
  ) {
    $this->sponsors = new SponsorArray();
    $this->getSponsors();
  }

  /**
   * Returns an array of sponsors
   * @param bool $published Default: true
   */
  public static function get(bool $published = true): SponsorArray
  {
    return (new Sponsors($published))->sponsors;
  }

  private function getSponsors()
  {
    $ordering = SponsorshipType::valuesOrdered();

    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = $db->getQuery(true);

    $query->select('id')->from($db->qn('#__claw_sponsors'));

    if ($this->published) $query->where($db->qn('published') . '=1');

    $query->order('FIND_IN_SET(' . $db->qn('type') . ', ' . $db->q(implode(',', $ordering)) . ')')
      ->order($db->qn('ordering') . ' ASC')
      ->order($db->qn('name') . ' ASC');

    $db->setQuery($query);
    $ids = $db->loadColumn();

    foreach ($ids as $id) {
      $this->sponsors[$id] = new Sponsor($id);
    }
  }
}
