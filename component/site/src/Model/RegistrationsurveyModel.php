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

use ClawCorpLib\Helpers\EventBooking;
use Exception;
use Joomla\Database\Exception\DatabaseNotFoundException;
use RuntimeException;

use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Helpers\Helpers;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\Database\DatabaseDriver;

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
    // Get the database
    $db = Factory::getDbo();

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
      $events = new ClawEvents(Aliases::current());

      /** @var \ClawCorpLib\Lib\ClawEvent $e */
      foreach ($events->getEvents() as $e) {
        if (substr($couponCode, 0, 1) === $e->couponKey) {
          $action = $e->eventPackageType;
          Helpers::sessionSet('eventAction', $action->value);
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
