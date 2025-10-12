<?php

namespace Clawcorp\Plugin\Content\Clawcoupon\Extension;

// no direct access
defined('_JEXEC') or die;

use ClawCorpLib\Lib\Aliases;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use ClawCorpLib\Lib\Coupon;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Lib\Registrant;
use ClawCorpLib\Lib\RegistrantRecord;

class Clawcoupon extends CMSPlugin implements SubscriberInterface
{
  private static bool $needsAssets = true;
  public string $plg_name = "clawcoupon";
  public Coupon $autoCoupon;
  public int $uid = 0;
  public int $location = 0;
  public string $alias = '';
  public string $referrer = '';
  public bool $hasMainEvent = false;
  public bool $onsiteActive = false;
  public ?RegistrantRecord $mainEvent = null;
  public ?EventConfig $eventConfig = null;

  public static function getSubscribedEvents(): array
  {
    return [
      'onContentPrepare' => 'createCouponForm',
    ];
  }

  public function createCouponForm(Event $event)
  {
    $target = '{' . $this->plg_name . '}';

    /** @var Joomla\CMS\Application\SiteApplication */
    $app = $this->getApplication();

    if (!$app->isClient('site')) {
      return;
    }

    /** @var \Joomla\CMS\WebAsset\WebAssetManager */
    // NOTE: this currently doesn't work as of Joomla 5.3.3
    //$wa = $app->getDocument()->getWebAssetManager();
    //$wa->getRegistry()->addExtensionRegistryFile('plg_content_clawcoupon');
    //$wa->useScript('plg_content_clawcoupon.registrationsurvey');
    if (Clawcoupon::$needsAssets) {
      HTMLHelper::_('script', 'media/com_claw/js/registrationsurvey.js', array('version' => 'auto'));
      Clawcoupon::$needsAssets = false;
    }

    [$context, $article, $params, $page] = array_values($event->getArguments());

    // no plugin marker in article, we're done
    if (!str_contains($article->text, $target)) return;

    $output = $this->insertCouponEntry();

    // We should replace only first occurrence in order to allow positions with the same name to regenerate their content:
    if (($start = strpos($article->text, $target)) !== false) {
      $article->text = substr_replace($article->text, $output, $start, \strlen($target));
    }
  }

  private function insertCouponEntry(): string
  {
    $this->location  = $this->params->get('clawLocationId', 0);
    $this->alias  = $this->params->get('event', '');
    $this->referrer = $this->getApplication()->getInput()->get('referrer', '', 'alnum');

    $this->loadCoupon();

    $form = $this->getTemplateOutput();

    return $form;
  }

  private function loadCoupon()
  {
    $this->eventConfig = new EventConfig($this->alias, []);
    $this->uid = $this->getApplication()->getIdentity()->id;

    if ($this->eventConfig->eventInfo->onsiteActive) {
      // TODO: need to figure out onsite logout handling
      if ($this->getApplication()->getIdentity()->id != 0) $this->getApplication()->logout();
      $coupon = new Coupon();
      $this->autoCoupon = $coupon;
    } else {
      if (!$this->uid) {
        $return = \Joomla\CMS\Uri\Uri::getInstance()->toString();
        $url    = 'index.php?option=com_users&view=login';
        $url   .= '&return=' . base64_encode($return);
        $this->getApplication()->redirect($url);
      }

      $registrant = new Registrant($this->eventConfig, $this->uid);
      $registrant->loadCurrentEvents();
      $this->mainEvent = $registrant->getMainEvent();
      $this->autoCoupon = $this->getUserCoupon();
    }
  }

  private function getUserCoupon(): Coupon
  {
    // Has coupon and not registered
    if (is_null($this->mainEvent)) {
      $eventIds = $this->eventConfig->getMainEventIds();
      return $this->getAssignedCoupon($this->uid, $eventIds);
    }

    return new Coupon();
  }

  /**
   * Find a coupon for a signed in user that falls within the event ids
   * @param int $uid User ID
   * @param array $eventIds Array of event ids
   * @return object null or coupon info (code and event_id)
   */
  private function getAssignedCoupon(int $uid, array $eventIds): Coupon
  {
    if (0 == $uid || count($eventIds) == 0) return new Coupon();

    $db = Factory::getContainer()->get('DatabaseDriver');

    $events = join(',', $eventIds);

    $query = $db->getQuery(true);
    $query->select('c.code, e.event_id')
      ->from('#__eb_coupons c')
      ->leftJoin('#__eb_coupon_events e ON e.coupon_id = c.id')
      ->where('c.user_id = ' . $uid)
      ->where('c.published = 1')
      ->where('e.event_id IN (' . $events . ')');
    $db->setQuery($query);
    $result = $db->loadObject();

    if ($result == null) return new Coupon();

    return new Coupon($result->code, $result->event_id);
  }

  private function getTemplateOutput($tmpl = "default"): string
  {
    ob_start();

    $getTemplatePath = PluginHelper::getLayoutPath('content', $this->plg_name, $tmpl);
    include($getTemplatePath);

    return ob_get_clean();
  }
}
