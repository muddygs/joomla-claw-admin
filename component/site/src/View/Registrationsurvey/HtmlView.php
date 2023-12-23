<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\View\Registrationsurvey;

defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Lib\Coupon;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Lib\Registrant;
use Joomla\CMS\Factory;
use Joomla\CMS\Help\Help;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use ReflectionClass;

require_once(JPATH_ROOT . '/components/com_eventbooking/helper/cart.php');
require_once(JPATH_ROOT . '/components/com_eventbooking/helper/database.php');
require_once(JPATH_ROOT . '/components/com_eventbooking/helper/helper.php');

/** @package ClawCorp\Component\Claw\Site\Controller */
class HtmlView extends BaseHtmlView
{
  public \Joomla\CMS\Application\SiteApplication $app;
  public string $eventAlias = '';
  public Coupon $autoCoupon;
  public string $couponCode = '';
  public int $uid = 0;
  public ?\ClawCorpLib\Lib\RegistrantRecord $mainEvent = null;
  public ?\ClawCorpLib\Lib\EventConfig $events = null;
  public bool $hasMainEvent = false;
  public bool $onsiteActive = false;
  public string $prefix = '';
  public string $referrer = '';

  public function __construct($config = [])
  {
    parent::__construct($config);

    /** @var \Joomla\CMS\Application\SiteApplication */
    $this->app = Factory::getApplication();
    
    $this->params = $params = $this->app->getParams();
    $this->eventAlias = $params->get('eventAlias', '');

    // Kill on bad event alias
    // TODO: Need function to test if alias is active and act if not
    if ( $this->eventAlias == '' ) {
      $this->app->enqueueMessage('No event alias specified.', 'error');
      return;
    }

    // TODO: have valid referrers in config
    $this->referrer = $this->app->getInput()->get('referrer', '', 'alnum');

    // Do we need to clear the cart (i.e., when switching events)?
    $oldEventAlias = Helpers::sessionGet('eventAlias');
    if ( $oldEventAlias != $this->eventAlias ) {
      $cart = new \EventbookingHelperCart();
      $cart->reset();
    }

    /**************************************************************
     * SET EVENT ALIAS
     **************************************************************/
    Helpers::sessionSet('eventAlias', $this->eventAlias);
    
    $this->events = new EventConfig($this->eventAlias);
    $this->uid = $this->app->getIdentity()->id;
    $this->onsiteActive = $this->events->eventInfo->onsiteActive;
    $this->prefix = $this->events->eventInfo->prefix;

    $this->couponCode = trim($this->app->input->get('coupon', '', 'string'));

    // set referrer tracking
    if ( $this->referrer != '' ) {
      Helpers::sessionSet('referrer', $this->referrer);
    } else {
      Helpers::sessionSet('referrer', '');
    }

    // Remember link back to this menu item
    Helpers::sessionSet('optionslink', \Joomla\CMS\Uri\Uri::getInstance()->toString());
  }

  public function display($tpl = null)
  {
    $this->state = $this->get('State');
    $this->form  = $this->get('Form');
    $this->item  = $this->get('Item');

    if ( is_null($this->events) ) {
      $this->app->enqueueMessage('Direct registration linking not permitted.', 'error');
      return;
    }

    if ( $this->events->eventInfo->onsiteActive) {
      if ($this->app->getIdentity()->id != 0) $this->app->logout();
      $coupon = new Coupon('',0);
      $this->autoCoupon = $coupon;
    } else {
      if (!$this->uid) {
        $return = \Joomla\CMS\Uri\Uri::getInstance()->toString();
        $url    = 'index.php?option=com_users&view=login';
        $url   .= '&return=' . base64_encode($return);
        $this->app->redirect($url);
      }

      $registrant = new Registrant($this->eventAlias, $this->uid);
      $registrant->loadCurrentEvents();
      $mainEvent = $registrant->getMainEvent();
      if ( $mainEvent ) {
        $this->mainEvent = $mainEvent;
      } 

      $this->autoCoupon = $this->getUserCoupon();
    }

    // Easier than reflection: https://bugs.php.net/bug.php?id=79620
    // $this->hasMainEvent =  (new ReflectionClass(self::class))->getProperty('mainEvent')->isInitialized($this);
    // TODO: hasMainEvent is unnecessary - replace with !is_null($this->mainEvent)
    $this->hasMainEvent = !is_null($this->mainEvent);

    parent::display($tpl ?? $this->eventAlias);
  }

  private function getUserCoupon(): Coupon
  {
    // Has a coupon already been generated for this registrant?
    if ( !$this->hasMainEvent ) {
      $eventIds = $this->events->getMainEventIds();
      return $this->getAssignedCoupon($this->uid, $eventIds);
    }

    return new Coupon('', 0 );
  }

  /**
   * Find a coupon for a signed in user that falls within the event ids
   * @param int $uid User ID
   * @param array $eventIds Array of event ids
   * @return object null or coupon info (code and event_id)
   */
   private function getAssignedCoupon( int $uid, array $eventIds ): Coupon
   {
     if ( 0 == $uid || count($eventIds) == 0 ) return null;
 
     $db = Factory::getDbo();
    //  $db = Factory::getContainer()->get(DatabaseInterface::class);
     $events = join(',',$eventIds);
 
     $query = $db->getQuery(true);
     $query->select('c.code, e.event_id')
         ->from('#__eb_coupons c')
         ->leftJoin('#__eb_coupon_events e ON e.coupon_id = c.id')
         ->where('c.user_id = ' . $uid)
         ->where('c.published = 1')
         ->where('e.event_id IN (' . $events . ')');
     $db->setQuery($query);
     $result = $db->loadObject();
 
     if ( $result == null ) return new Coupon('',0);
 
     return new Coupon($result->code, $result->event_id);
   }
 

}
