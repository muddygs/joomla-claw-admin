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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventInfo;

class EventBooking
{
  /**
   * Reads session information and returns link to last-visited registration page
   * @return string 
   */
  public static function getRegistrationLink(): string
  {
    $eventAlias = Helpers::sessionGet('eventAlias', Aliases::current());
    $regAction  = Helpers::sessionGet('eventAction', EventPackageTypes::none->value);
    $referrer   = Helpers::sessionGet('referrer');

    if ($regAction == EventPackageTypes::none->value) {
      return '/';
    }

    return self::buildRegistrationLink($eventAlias, EventPackageTypes::FindValue($regAction), $referrer);
  }

  /**
   * Creates a link that goes to the registration options page
   * @param string $eventAlias Event Alias as defined in the EventInfo
   * @param EventPackageTypes $eventAction The "action" url param indicating the registration package
   * @param string $referrer (option) For third-party references/logo display
   * @return string href
   **/
  public static function buildRegistrationLink(string $eventAlias, EventPackageTypes $eventAction, string $referrer = ''): string
  {
    $currentUri = \Joomla\CMS\Uri\Uri::getInstance();

    $query = [
      'option' => 'com_claw',
      'view' => 'registrationoptions',
      'event' => $eventAlias,
      'action' => $eventAction->value,
      'return' => base64_encode($currentUri->toString()),
    ];

    if ('' != $referrer) {
      $query['referrer'] = $referrer;
    }

    $route = Route::_('index.php?' . http_build_query($query));

    return $route;
  }

  /**
   * Creates a link to a event (by id) directly (no cart)
   * @param int $eventId ID of the event in Event EventBooking
   * @return string href
   **/
  public static function buildDirectLink(int $eventId): string
  {
    $query = [
      'option' => 'com_eventbooking',
      'view' => 'register',
      'event_id' => $eventId,
    ];

    //what the hell is Joomla doing? loosing the event_id param: $route = Route::_('index.php?' . http_build_query($query));
    $route = '/index.php?' . http_build_query($query);
    return $route;
  }

  /**
   * Override of Event Booking MailChimp subscriber
   * Requires customization of plugins/eventbooking/mailchimp to call into this
   * @param object $row Registration object from Event Booking
   **/
  public static function subscribeByRegistrantId($row)
  {
    // Ignore mailchimp subscription if not on clawinfo.org (i.e., dev site)
    $uri_path = Uri::getInstance()->getHost();
    if (!str_contains($uri_path, 'clawinfo')) {
      return;
    }

    // Load external password file
    require_once JPATH_ROOT . '/../mailchimp_constants.php';
    require_once JPATH_LIBRARIES . '/claw/External/mailchimp-marketing-php/vendor/autoload.php';

    $client = new \MailchimpMarketing\ApiClient();

    $client->setConfig([
      'apiKey' => \Constants::MAILCHIMP_APIKEY,
      'server' => \Constants::MAILCHIMP_SERVER,
    ]);

    $list_id = \Constants::MAILCHIMP_LISTID;

    // user key is lowercase email -> md5 hash
    $email_hash = md5(strtolower($row->email));

    $interests = [];
    $response = (object)[];

    try {
      $response = $client->lists->getListMember($list_id, $email_hash);

      foreach ($response->interests as $id => $value) {
        $interests[$id] = $value == 1 ? true : false;
      }
    } catch (\Exception $e) {
      // new user -- ignore error
    } finally {
      $interests['ff661c2ef4'] = true; // eNews
    }

    // if (!property_exists($response, 'status') || $response->status != 'subscribed') {
    // 	return;
    // }

    $data = [
      'email_address' => $row->email,
      'status_if_new' => 'subscribed',
      'email_type'    => 'html',
      'merge_fields'  => [
        'FNAME' => $row->first_name,
        'LNAME' => $row->last_name,
        'CITY'  => $row->city,
        'STATE' => $row->state,
        'ZIP'   => $row->zip,
        'REGID' => $row->invoice_number
      ],
      'interests' => $interests
    ];

    try {
      $response = $client->lists->setListMember($list_id, $email_hash, $data);
    } catch (\Exception $e) {
      $app = Factory::getApplication();
      //$errors[] = $e->getMessage();
      $app->enqueueMessage('An error occurred while subscribing you to our MailChimp list. Please contact <a href="/help">Guest Services</a> for assistance.', 'Warning');
      $path = __FILE__ . ': ' . __LINE__;
      $data = [
        'list_id' => $list_id,
        'email_hash' => $email_hash,
        'data' => $data,
        'exception' => $e
      ];

      Helpers::sendErrorNotification($path, $data);
    }
  }

  /**
   * Reverse lookup on an Event Booking location ID
   * @param int $locationId Database id of location
   * @return string Name of location
   **/
  public static function getLocationName(int $locationId): string
  {
    $db = Factory::getContainer()->get('DatabaseDriver');
    $query = $db->getQuery(true);
    $query
      ->select('name')
      ->from('#__eb_locations')
      ->where('id = ' . $locationId);
    $db->setQuery($query);
    $result = $db->loadResult();

    return $result ?? '';
  }

  /**
   * This is a simplified version of EventbookingEventModel::getEventData()
   * that simply checks event capacity and assumes the events are not
   * configured for offline nor waitlist
   * @return array of (id,event_capacity,number_registants)
   */
  public static function getEventsCapacityInfo(EventInfo $eventInfo, array $eventIds): array
  {
    if (count($eventIds) == 0) {
      return [];
    }

    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');
    $query = $db->getQuery(true);
    $currentDate = Factory::getDate('now', $eventInfo->timezone);

    $query
      ->select('a.id')
      ->select('event_capacity')
      ->select('IFNULL(SUM(b.number_registrants), 0) AS total_registrants')
      ->from('#__eb_events AS a')
      ->leftJoin(
        '#__eb_registrants AS b ON a.id = b.event_id AND (b.published = 1 or b.published is null)'
      )
      ->whereIn('a.id', $eventIds)
      ->where('a.published = 1')
      ->where('a.registration_start_date < ' . $db->q($currentDate))
      ->where('a.event_end_date < ' . $db->q($eventInfo->end_date))
      ->group('a.id');

    $db->setQuery($query);

    #echo '<pre>' . (string)$db->replacePrefix($db->getQuery()) . '</pre>';
    #echo '<pre>' . implode(',', $eventIds) . '</pre>';

    $result = $db->loadObjectList('id');
    #dd($result);

    return $result;
  }

  /**
   * Returns the raw database row for an event
   * @param int $event_id The event row ID
   * @return object Database row as object or null on error
   */
  public static function loadEventRow(int $event_id): ?object
  {
    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');

    $q = $db->getQuery(true);

    $q->select('*')
      ->from('#__eb_events')
      ->where($db->qn('id') . '=' . $db->q($event_id));
    $db->setQuery($q);
    return $db->loadObject();
  }

  /**
   * Given an array of category ids, returns the raw row values
   * @param array $categoryIds The array of category ids to retrieve
   * @return ?array Object list keyed by row id|null
   */
  public static function getRawCategories(array $categoryIds): ?array
  {
    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = $db->getQuery(true);
    $query->select('*')
      ->from($db->qn('#__eb_categories'))
      ->where($db->qn('id') . ' IN (' . implode(',', (array)($db->q($categoryIds))) . ')');
    $db->setQuery($query);
    $rows = $db->loadObjectList('id');

    return $rows;
  }
}
