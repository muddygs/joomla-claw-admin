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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Helper for mod_claw_sponsors
 *
 * @since  1.5
 */
class VendorsHelper
{
  public static function loadVendors(string $event): array
  {
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

  public static function echoVendors(string $event)
  {
    $vendors = VendorsHelper::loadVendors($event);
    ?>
    <div class="d-flex flex-row flex-wrap justify-content-center mb-3">
    <?php

    foreach ( $vendors AS $row ) {
      $name = $row->name;

      $img = '';

      if ( $row->logo !== '') {
        $i = HTMLHelper::cleanImageURL($row->logo);
        $img = $i->url;
      }

      $img = "<img src=\"$img\" class=\"card-img-top mx-auto d-block vendorlogo mt-1 mb-1\" alt=\"$name\" title=\"$name\">";
      $link = $row->link;
    
      $urlopen = '';
      $urlclose = '';
    
      if ( !empty($link) )
      {
        $urlopen = "<a href=\"$link\" target=\"_blank\" rel=\"noopener\">";
        $urlclose = "</a>";
      }
    
      ?>
        <div class="p-2 vendorcard">
          <div class="card h-100 border border-warning" style="background-color:#444;">
            <?=$urlopen?><?=$img?><?=$urlclose?>
            <div class="card-body border-top border-warning">
              <h5 class="card-title"><?=$urlopen?><?=$name?><?=$urlclose?></h5>
              <p class="card-text d-none d-lg-block" style="font-size:0.8rem;margin-bottom:0 !important;"><?=$row->description?></p>
            </div>
          </div>
        </div>
    <?php
    }
    ?>
    </div>
    <?php
  }
}
