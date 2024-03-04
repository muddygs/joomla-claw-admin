<?php
/**
 * @package     CLAW.Sponsors
 * @subpackage  mod_claw_sponsors
 *
 * @copyright   (C) 2023 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Module\Vendors\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;

/**
 * Helper for mod_claw_sponsors
 *
 * @since  1.5
 */
class VendorsHelper
{
  public static function loadVendors(Registry $params, SiteApplication $app): array
  {
    $event = $params->get('event', '');

    if (empty(trim($event))) {
      return [];
    }

    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = $db->getQuery(true);

    $query->select('*')
      ->from('#__claw_vendors')
      ->where('published = 1')
      ->where('event = ' . $db->quote($event))
      ->order('ordering');

    $db->setQuery($query);
    return $db->loadObjectList();
  }
}

