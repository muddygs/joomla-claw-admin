<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_claw_tabferret
 *
 * @copyright   (C) 2024 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Module\ClawTabferret\Site\Helper;

use DateTimeZone;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Table\Table;

defined('_JEXEC') or die;

/**
 * Helper for mod_claw_tabferret
 */
class ClawTabferretHelper
{
  // TODO: private int $uid (or similar to meet module permissions)
  
  /**
   * Retrieve the tab name and tab content, regardless of the actual display mechanism. Tabs/accordions
   * will use the tab name, but carousels will use only the tab content.
   * @param Registry $params 
   * @param SiteApplication $app 
   * @return array Keyed by 'tabs' and 'tabContents' 
   */
  public function getTabData(Registry $params, SiteApplication $app): array
  {
    $tabFields = $params->get('tab-fields', (object)[]);
    $tabActive = null;

    $tabs = [];
    $tabContents = [];

    foreach ($tabFields as $tabField) {
      if ( !$tabField->tab_enabled ) continue;

      switch ($tabField->tab_type) {
        case 'article':
          $articleId = $tabField->tab_article;
          $table = $this->loadContentById($articleId);
          if ( is_null($table) ) continue 2;

          // TODO: Handle readmore?
          if (property_exists($table, 'introtext')) {
            $tabContents[] = HTMLHelper::_('content.prepare', $table->introtext);
            $tabs[] = $tabField->tab_title;
          }
          break;
        case 'module':
          $moduleId = $tabField->tab_module;
          $module = $this->loadModuleById($moduleId);
          if ( empty($module) ) continue 2;
          $tabs[] = $tabField->tab_title;
          $tabContents[] = $module;
          break;
      }

      if ( null === $tabActive && $tabField->tab_isdefault ) $tabActive = count($tabs) - 1;
    }

    $carouselInterval = $params->get('carousel_interval', 5);
    $carouselRefresh = $params->get('carousel_refresh', 300);

    $carouselConfig = (object)[
      'interval' => $carouselInterval,
      'refresh' => $carouselRefresh
    ];

    if ( null == $tabActive ) $tabActive = 0;

    return [
      $tabs,
      $tabContents,
      $tabActive,
      $carouselConfig
    ];
  }

  /**
   * Given an article ID, load the article and return the Table object
   * @param int $id 
   * @return Table 
   */
  private function loadContentById(int $id): ?Table
  {
    /** @var \Joomla\CMS\Application */
    $app = Factory::getApplication();
    $component = $app->bootComponent('com_content');
    $mvcFactory = $component->getMVCFactory();
    $table = $mvcFactory->createTable('Article', 'Content', []);

    if ( is_null($table) ) return null;
    $table->load($id);

    if ( $table->state != 1 ) return null;

    // Use global configuration time zone to filter articles based on publication dates
    $config = $app->getConfig();
    $timeZone = $config->get('offset');
    $timeZoneObject = new DateTimeZone($timeZone);

    $publishUp = $table->publish_up;
    $publishDown = $table->publish_down;
    $now = Factory::getDate('now', $timeZoneObject);

    if ( ($publishUp && $publishUp > $now) || ($publishDown && $publishDown < $now) ) return null;

    // TODO: Check permissions

    return $table;
  }

  /** 
   * Loads and renders the module (copy of PlgContentLoadmodule::loadid)
   *
   * @param   string  $id  The id of the module
   *
   * @return  mixed
   *
   * @since   3.9.0
   */
  private function loadModuleById($id): string
  {
    // No need for date filtering since getModuleById() handles it

    /** @var \Joomla\CMS\Application */
    $app = Factory::getApplication();
    $document = $app->getDocument();
    $renderer = $document->loadRenderer('module');
    $modules  = ModuleHelper::getModuleById($id);
    $params   = ['style' => 'none'];
    ob_start();

    if ($modules->id > 0) {
      echo $renderer->render($modules, $params);
    }

    return ob_get_clean();
  }
}
