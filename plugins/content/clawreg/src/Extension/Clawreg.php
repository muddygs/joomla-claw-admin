<?php

namespace Clawcorp\Plugin\Content\Clawreg\Extension;

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\CMS\Event\Result\ResultAwareInterface;

use ClawCorpLib\Helpers\EventBooking;
use ClawCorpLib\Enums\EventPackageTypes;

class Clawreg extends CMSPlugin implements SubscriberInterface
{
  public static function getSubscribedEvents(): array
  {
    return [
      'onContentPrepare' => 'createButton',
    ];
  }

  public function createButton(Event $event)
  {
    if (!$this->getApplication()->isClient('site')) {
      return;
    }

    [$context, $article, $params, $page] = array_values($event->getArguments());
    if ($context !== "com_content.article") return;

    if (str_contains($article->text, "{clawreg")) {
      $text = $article->text; // text of the article to manipulate

      $regex         = "#{clawreg\s+([\w,\"]+)}#i";
      // Find all instances of plugin and put in $matches for loadposition
      // $matches[0] is full pattern match, $matches[1] is the position
      preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);

      // No matches, skip this
      if ($matches) {
        foreach ($matches as $match) {
          // ordering:
          // 0: location
          // 1: EventPackageType enum case | packageId
          // 2: display option (null, onsite_active) 
          $matcheslist = explode(',', $match[1]);

          $output = $this->errorButton();

          // We should replace only first occurrence in order to allow positions with the same name to regenerate their content:
          if (($start = strpos($article->text, $match[0])) !== false) {
            $article->text = substr_replace($article->text, $output, $start, \strlen($match[0]));
          }
        }
      }

      $article->text = $text;
    }
  }

  public function errorButton(): string
  {
    return "[ error ]";
  }
}
