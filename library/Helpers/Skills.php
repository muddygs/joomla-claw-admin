<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Helpers;

use ClawCorpLib\Enums\ConfigFieldNames;
use ClawCorpLib\Enums\SkillOwnership;
use ClawCorpLib\Enums\SkillPublishedState;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Skills\Presenters;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseDriver;

class Skills
{
  private EventInfo $eventInfo;

  // constructor
  public function __construct(
    public DatabaseDriver $db,
    public readonly string $eventAlias = ''
  ) {
    $this->eventInfo = new EventInfo($this->eventAlias);
  }

  public static function rsformJson()
  {
    // Database driver
    $eventInfo = new EventInfo(Aliases::current(true));
    $classes = \ClawCorpLib\Skills\Skills::get($eventInfo, SkillPublishedState::published);

    $results = [];

    foreach ($classes->keys() as $key) {
      $results[] = (object)[
        'id' => $classes[$key]->id,
        'stime' => explode(':', $classes[$key]->time_slot)[0],
        'title' => htmlentities($classes[$key]->title),
        'gid' => $key,
        'day' => $classes[$key]->day->format('w')
      ];
    }

    return json_encode($results);
  }

  public function presentersCSV(string $filename, bool $publishedOnly = true)
  {
    // append image_preview to the presenter object
    $config = new Config($this->eventInfo->alias);
    $path = $config->getConfigText(ConfigFieldNames::CONFIG_IMAGES, 'presenters', '/images/skills/presenters');

    $presenterArray = Presenters::get($this->eventInfo, $publishedOnly ? SkillPublishedState::published : SkillPublishedState::any);
    $keys = $presenterArray->keys();

    if (!count($keys)) {
      throw new GenericDataException('No presenters to export.', 500);
    }

    /** @var \ClawCorpLib\Skills\Presenter */
    $presenter = $presenterArray[$keys[0]];

    if ($presenter === false) {
      throw new GenericDataException('Unable to load presenters', 500);
    }

    $ordering = [
      'id',
      'ownership',
      'published',
      'name',
      'legal_name',
      'email',
      'phone',
      'arrival',
    ];

    $columnNames = array_keys((array)($presenter->toSimpleObject()));

    $ordering = Helpers::combineArrays($ordering, $columnNames);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
    ob_clean();
    ob_start();
    set_time_limit(0);
    ini_set('error_reporting', E_NOTICE);

    $fp = fopen('php://output', 'wb');
    fputcsv($fp, $ordering);

    /** @var \ClawCorpLib\Skills\Presenter */
    foreach ($presenterArray as $p) {
      $row = [];
      foreach ($ordering as $col) {
        switch ($col) {
          case 'id':
            $row[] = 'presenter_' . $p->$col;
            break;
          case 'image':
            $row[] = '';
            break;
          case 'image_preview':
            // Images must be on server for external app import
            $cache = new DbBlob(
              db: $this->db,
              cacheDir: JPATH_ROOT . $path,
              prefix: 'web_',
              extension: 'jpg'
            );
            $filenames = $cache->toFile(
              tableName: '#__claw_presenters',
              rowIds: [$p->id],
              key: 'image_preview',
            );

            $row[] = $filenames[$p->id] ? 'https://www.clawinfo.org/' . $filenames[$p->id] : '';
            break;
          case 'bio':
            // Convert to HTML
            $row[] = Helpers::cleanHtmlForCsv($p->$col);
            break;
          case 'published':
            $row[] = match ($p->published) {
              SkillPublishedState::unpublished => 'Unpublished',
              SkillPublishedState::published => 'Published',
              SkillPublishedState::new => 'New',
              default => 'Unknown',
            };
            break;
          case 'ownership':
            $row[] = match ($p->ownership) {
              SkillOwnership::user => 'User',
              SkillOwnership::admin => 'Admin',
              default => 'Unknown',
            };
            break;
          case 'arrival':
            $row[] = implode(', ', $p->arrival);
            break;
          default:
            $row[] = $p->$col;
            break;
        }
      }

      fputcsv($fp, $row);
    }

    fclose($fp);
    ob_end_flush();
  }

  public function classesCSV(string $filename, bool $publishedOnly = true)
  {
    $published = $publishedOnly ? SkillPublishedState::published : SkillPublishedState::any;
    $skillArray = \ClawCorpLib\Skills\Skills::get($this->eventInfo, $published);
    $presenterArray = Presenters::get($this->eventInfo, $published);

    $locations = new Locations($this->eventAlias);

    // Load the global config for com_claw. We need to the RS Form ID
    /** @var Joomla\CMS\Application\AdministratorApplication */
    $app = Factory::getApplication();
    $componentParams = ComponentHelper::getParams('com_claw');
    $seSurveyMenuId = $componentParams->get('se_survey_link', 0);
    $surveyLink = '';
    $siteUrl = '';

    if ($seSurveyMenuId > 0) {
      $menu = $app->getMenu('site');
      $item = $menu->getItem($seSurveyMenuId);

      // Get main site link
      $uri = Uri::getInstance();
      $siteUrl = $uri::root();
      $surveyLink = $siteUrl . $item->alias;
    }

    // Load database columns
    $keys = $skillArray->keys();

    if (!count($keys)) {
      throw new GenericDataException('No skills to export.', 500);
    }

    /** @var \ClawCorpLib\Skills\Skill */
    $skill = $skillArray[$keys[0]];

    if ($skill === false) {
      throw new GenericDataException('Unable to load skills', 500);
    }

    $ordering = [
      'id',
      'day',
      'start_time',
      'end_time',
      'ownership',
      'published',
      'people',
      'people_public_name',
      'copresenter_info',
      'title',
      'av',
      'location',
    ];

    $columnNames = array_keys((array)($skill->toSimpleObject()));
    #$columnNames[] = 'multitrack';
    $columnNames[] = 'people';
    $columnNames[] = 'people_public_name';
    $columnNames[] = 'start_time';
    $columnNames[] = 'end_time';

    $ordering = Helpers::combineArrays($ordering, $columnNames);

    // Load category strings
    $config = new Config($this->eventAlias);
    $categories = $config->getConfigValuesText(ConfigFieldNames::SKILL_CATEGORY);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
    ob_clean();
    ob_start();
    set_time_limit(0);
    ini_set('error_reporting', E_NOTICE);

    $fp = fopen('php://output', 'wb');
    fputcsv($fp, $ordering);

    /** @var \ClawCorpLib\Skills\Skill */
    foreach ($skillArray as $c) {
      $row = [];
      foreach ($ordering as $col) {
        switch ($col) {
          case 'id':
            $row[] = 'class_' . $c->id;
            break;
          case 'start_time':
            $time = Helpers::formatTime(explode(':', $c->time_slot)[0]);
            if ($time == 'Midnight') $time = '12:00 AM';
            if ($time == 'Noon') $time = '12:00 PM';
            $row[] = $time;
            break;

          case 'end_time':
            // take start time and add length
            [$time, $length] = explode(':', $c->time_slot);
            $day = clone $c->day;
            $day->modify($time);
            $day->modify('+ ' . $length . ' minutes');
            $row[] = $day->format('g:i A');
            break;

          case 'ownership':
            $row[] = match ($c->ownership) {
              SkillOwnership::user => 'User',
              SkillOwnership::admin => 'Admin',
              default => 'Unknown',
            };
            break;

          case 'people':
            $pids = [$c->presenter_id, ...$c->other_presenter_ids];

            // Remove any unpublished presenter ids
            $pids = array_filter($pids, function ($id) use ($presenterArray) {
              return $presenterArray->offsetExists($id);
            });

            // Prepend "presenter_" to each id and join with commas
            $row[] = implode(',', array_map(function ($id) {
              return 'presenter_' . $id;
            }, $pids));
            break;

          case 'people_public_name':
            $pids = [$c->presenter_id, ...$c->other_presenter_ids];

            // Remove any unpublished presenter ids
            $pids = array_filter($pids, function ($id) use ($presenterArray) {
              return $presenterArray->offsetExists($id);
            });

            // Prepend "presenter_" to each id and join with commas
            $row[] = implode(',', array_map(function ($id) use ($presenterArray) {
              return $presenterArray[$id]->name;
            }, $pids));
            break;

          case 'location':
            $location = $locations->GetLocationById($c->location)->value;
            $row[] = $location;
            break;

            #case 'multitrack':
            #// track is day converted to day of week
            #$time = $c->day . ' ' . explode(':', $c->time_slot)[0];
            #// Fri/Sat get AM/PM, Sun gets day of week
            #$day = date('w', strtotime($time));
            #if ($day == 5 || $day == 6) {
            #$row[] = date('l A', strtotime($time));
            #} else {
            #$row[] = date('l', strtotime($time));
            #}
            #break;

          case 'description':
            $survey = '';

            if ($surveyLink != '' && $publishedOnly) {
              $newurl = $surveyLink . '?form[classTitleParam]=' . $c->id;
              $oldurl = '/skills_survey_' . $c->id;
              $redirect = new Redirects($this->db, $oldurl, $newurl, 'survey_' . $c->id);
              $redirectId = $redirect->insert();
              if ($redirectId) $survey = 'Survey: ' . $oldurl . '<br/>';
              $description = $survey . 'Category: ' . $categories[$c->category] . '<br/>' . $c->$col;
            } else {
              $description = $c->$col;
            }

            // Convert category to text
            $description = Helpers::cleanHtmlForCsv($description);
            $row[] = $description;
            break;

          case 'published':
            $row[] = match ($c->$col) {
              SkillPublishedState::new => 'New',
              SkillPublishedState::unpublished => 'Unpublished',
              SkillPublishedState::published => 'Published',
              default => 'Unknown',
            };
            break;

          default:
            $row[] = $c->$col;
            break;
        }
      }

      fputcsv($fp, $row);
    }
  }

  public function zipPresenters(string $filename)
  {
    if ($this->eventAlias == '') {
      // error message
      throw new GenericDataException('eventAlias must be specified', 500);
    }

    $presenterArray = Presenters::get($this->eventInfo, SkillPublishedState::published);

    // Define the base tmp path
    $tmpBasePath = Factory::getApplication()->get('tmp_path');

    // Create a unique folder name, e.g., using a timestamp or a unique ID
    $zipFileName = implode(DIRECTORY_SEPARATOR, [$tmpBasePath, $filename]);

    /** PHP METHOD */
    $zip = new \ZipArchive();
    if ($zip->open($zipFileName, \ZipArchive::CREATE) !== TRUE) {
      echo "Error creating zip archive";
      return;
    }

    /** @var \ClawCorpLib\Skills\Presenter */
    foreach ($presenterArray as $presenter) {
      $presenter->loadImageBlobs();

      $name = $presenter->name . '-' . $presenter->id . '.jpg';
      $zip->addFromString($name, $presenter->image);
    }

    $zip->close();

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header("Cache-Control: no-store");
    header('Content-Length: ' . filesize($zipFileName));

    /** WARNING: THESE BREAK OUR HOSTING COMPANY */
    // header('Content-Transfer-Encoding: binary'); <-- this is a mail transport header
    // header('Cache-Control', 'must-revalidate');      and many resources lie about this?
    // header("Expires: 0");

    set_time_limit(120);
    ini_set('error_reporting', E_NOTICE);

    ob_end_clean();
    flush();
    readfile($zipFileName);

    File::delete($zipFileName);
  }
}
