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

use ClawCorpLib\Helpers\Bootstrap;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Table\Table;
use RuntimeException;

defined('_JEXEC') or die;

/**
 * Helper for mod_claw_tabferret
 */
class ClawTabferretHelper
{
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

    $tabs = [];
    $tabContents = [];

    // Use global configuration time zone to filter articles based on publication dates
    $config = $app->getConfig();
    $timeZone = $config->get('offset');
    $timeZoneObject = new DateTimeZone($timeZone);

    foreach ($tabFields as $tabField) {
      switch ($tabField->tab_type) {
        case 'article':
          $articleId = $tabField->tab_article;
          $table = $this->loadContentById($articleId);
          if ( is_null($table) ) continue 2;

          $publishUp = $table->publish_up;
          $publishDown = $table->publish_down;
          $now = Factory::getDate('now', $timeZoneObject);

          if ($publishUp && $publishUp > $now) continue 2;
          if ($publishDown && $publishDown < $now) continue 2;

          // TODO: Handle readmore?
          if (property_exists($table, 'introtext')) {
            $tabContents[] = HTMLHelper::_('content.prepare', $table->introtext);
            $tabs[] = $tabField->tab_title;
          }
          break;
        case 'module':
          // No need for date filtering since loadModuleById() handles it
          $moduleId = $tabField->tab_module;
          $module = $this->loadModuleById($moduleId);
          if ( empty($module) ) continue 2;
          $tabs[] = $tabField->tab_title;
          $tabContents[] = $module;
          break;
      }
    }

    $carouselInterval = $params->get('carousel_interval', 5);
    $carouselRefresh = $params->get('carousel_refresh', 300);

    $carouselConfig = (object)[
      'interval' => $carouselInterval,
      'refresh' => $carouselRefresh
    ];

    return [
      $tabs,
      $tabContents,
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

    if (is_null($table)) throw new RuntimeException('Could not load Article table');
    $table->load($id);
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
