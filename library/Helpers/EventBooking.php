<?php

namespace ClawCorpLib\Helpers;

use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\ClawEvents;
use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

class EventBooking
{
  /**
   * Returns event ID and Title for ticketed events
   */
  static function LoadTicketedEvents(ClawEvents $e): array
  {
    $result = [];

    $info = $e->getEvent()->getInfo();
    $events = ClawEvents::getEventsByCategoryId(ClawEvents::getCategoryIds(Aliases::categoriesTicketedEvents), $info);

    foreach ($events as $e) {
      $result[$e->id] = $e->title;
    }

    return $result;
  }

  /**
   * Reads session information and returns link to last-visited registration page
   * @return string 
   */
  static function getRegistrationLink(): string
  {
    $eventAlias = Helpers::sessionGet('eventAlias', Aliases::current());
    $regAction  = Helpers::sessionGet('eventAction', EventPackageTypes::none->value);
    $referrer   = Helpers::sessionGet('referrer');

    $route = Route::_('index.php?option=com_claw&view=registrationoptions&event=' . $eventAlias . '&action='. $regAction);

    if ('' != $referrer) {
      $route .= '&referrer=' . $referrer;
    }

    return $route;
  }

  static function buildRegistrationLink(string $eventAlias, EventPackageTypes $eventAction, string $referrer = ''): string
  {
    $route = Route::_('index.php?option=com_claw&view=registrationoptions&event=' . $eventAlias . '&action='. $eventAction->value);
    if ('' != $referrer) {
      $route .= '&referrer=' . $referrer;
    }

    return $route;

  }

  static function subscribeByRegistrantId($row)
  {
    // Ignore mailchimp subscription if not on clawinfo.org (i.e., dev site)
    $uri_path = Uri::getInstance()->getHost();
    if (strpos($uri_path, 'clawinfo') === false) {
      // return;
    }

    // TODO: use DIRECTORY_SEPARATOR
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
    } catch (Exception $e) {
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
    } catch (Exception $e) {
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
}
