<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\Model;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\PackageInfoTypes;
use ClawCorpLib\Helpers\EventBooking;
use Exception;
use Joomla\Database\Exception\DatabaseNotFoundException;
use RuntimeException;

use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\EventConfig;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseModel;

/**
 * Minimal code for a component to configure state, but do nothing else
 */
class RegistrationsurveyModel extends BaseModel
{
  /**
   * Returns JSON encoded assessment of a coupon code
   * @param string $coupon 
   * @return string 
   * @throws DatabaseNotFoundException 
   * @throws RuntimeException 
   * @throws Exception 
   */
  public function RegistrationSurveyCouponStatus(string $coupon): string
  {
    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');

    $result = [
      'error' => 1,
      'link' => '',
    ];

    // Sanitize
    if ('' == $coupon) return json_encode($result);
    $couponCode = strtoupper(preg_replace('/[^\x21-\x7E]/', '', $coupon));

    $query = $db->getQuery(true);
    $query->select('*')
      ->from($db->quoteName('#__eb_coupons'))
      ->where($db->quoteName('published') . ' = 1')
      ->where($db->quoteName('code') . ' = ' . $db->quote($couponCode));

    $db->setQuery($query);
    $coupon = $db->loadRow();

    if ($coupon != null) {
      $events = new EventConfig(
        Aliases::current(),
        [
          PackageInfoTypes::main,
          PackageInfoTypes::daypass,
          PackageInfoTypes::addon,
          PackageInfoTypes::passes,
          PackageInfoTypes::passes_other,
          PackageInfoTypes::coupononly,
        ]
      );

      /** @var \ClawCorpLib\Lib\PackageInfo */
      foreach ($events->packageInfos as $e) {
        if (substr($couponCode, 0, 1) === $e->couponKey) {
          Helpers::sessionSet('eventAction', $e->eventPackageType->value);
          $result = [
            'error' => 0,
            'link' => EventBooking::getRegistrationLink(),
          ];

          Helpers::sessionSet('clawcoupon', $couponCode);

          break;
        }
      }
    }

    return json_encode($result);
  }
}
