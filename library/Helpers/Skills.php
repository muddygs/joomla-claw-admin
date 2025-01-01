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
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseDriver;

class Skills
{
  private array $presenterCache = [];
  private array $classCache = [];
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
    $db = Factory::getContainer()->get('DatabaseDriver');
    $skills = new Skills($db, Aliases::current(true));
    $classes = $skills->GetClassList();

    $results = [];

    foreach ($classes as $class) {
      // stime corresponds to the tabs, just to help people find their class in the list
      $results[] = (object)[
        'id' => $class->id,
        'stime' => explode(':', $class->time_slot)[0],
        'title' => htmlentities($class->title),
        'gid' => $class->id,
        'day' => date('w', strtotime($class->day)), // 0 = Sunday, 1 = Monday, etc.
      ];
    }

    return json_encode($results);
  }

  public function presentersCSV(string $filename, bool $publishedOnly = true)
  {
    // append image_preview to the presenter object
    $config = new Config($this->eventInfo->alias);
    $path = $config->getConfigText(ConfigFieldNames::CONFIG_IMAGES, 'presenters', '/images/skills/presenters');

    $presenterArray = Presenters::get($this->eventInfo, $publishedOnly);
    $keys = $presenterArray->keys();

    if (!count($keys)) {
      throw new GenericDataException('No presenters to export.', 500);
    }

    /** @var \ClawCorpLib\Skills\Presenter */
    $presenter = $presenterArray[$keys[0]];

    if ($presenter === false) {
      throw new GenericDataException('Unable to load presenters', 500);
    }

    $columnNames = array_keys((array)($presenter->toSimpleObject()));

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
    ob_clean();
    ob_start();
    set_time_limit(0);
    ini_set('error_reporting', E_NOTICE);

    $fp = fopen('php://output', 'wb');
    fputcsv($fp, $columnNames);

    foreach ($keys as $pid) {
      /** @var \ClawCorpLib\Skills\Presenter */
      $p = $presenterArray[$pid];

      $row = [];
      foreach ($columnNames as $col) {
        switch ($col) {
          case 'id':
            $row[] = 'presenter_' . $p->$col;
            break;
          case 'image':
            $row[] = '';
            break;
          case 'image_preview':
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
              SkillOwnership::user => 'Admin',
              default => 'Unknown',
            };
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
    $skillArray = \ClawCorpLib\Skills\Skills::get($this->eventInfo, $publishedOnly ? SkillPublishedState::published : SkillPublishedState::any);
    $presenterArray = Presenters::get($this->eventInfo, $publishedOnly);

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


    // $rsformId = $config['se_survey_link'];

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

    $columnNames = array_keys((array)($skill->toSimpleObject()));
    $columnNames[] = 'multitrack';
    $columnNames[] = 'people';
    $columnNames[] = 'people_public_name';
    $columnNames[] = 'start_time';
    $columnNames[] = 'end_time';

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
    fputcsv($fp, $columnNames);

    foreach ($this->classCache as $c) {
      $row = [];
      foreach ($columnNames as $col) {
        switch ($col) {
          case 'id':
            $row[] = 'class_' . $c->$col;
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
            $time = new \DateTime($c->day . ' ' . $time);
            $time->modify('+ ' . $length . ' minutes');
            $row[] = $time->format('g:i A');
            break;

          case 'ownership':
            $row[] = match ($c->ownership) {
              SkillOwnership::user => 'User',
              SkillOwnership::user => 'Admin',
              default => 'Unknown',
            };
            break;

          case 'people':
            if (empty($c->presenters)) {
              $presenterIds = [];
            } else {
              // TODO: Fix decode
              $presenterIds = json_decode($c->presenters);
              if (is_null($presenterIds)) $presenterIds = [];
            }
            array_unshift($presenterIds, $c->owner);

            // Remove any unpublished presenter ids
            $presenterIds = array_filter($presenterIds, function ($id) {
              return isset($this->presenterCache[$id]);
            });

            // Prepend "presenter_" to each id and join with commas
            $row[] = implode(',', array_map(function ($id) {
              return 'presenter_' . $id;
            }, $presenterIds));
            break;

          case 'people_public_name':
            if (empty($c->presenters)) {
              $presenterIds = [];
            } else {
              $presenterIds = json_decode($c->presenters) ?? [];
            }
            array_unshift($presenterIds, $c->owner);

            // Remove any unpublished presenter ids
            $presenterIds = array_filter($presenterIds, function ($id) {
              return isset($this->presenterCache[$id]);
            });

            $row[] = implode(',', array_map(function ($id) {
              return $this->presenterCache[$id]->name;
            }, $presenterIds));
            break;

          case 'location':
            $location = $locations->GetLocationById($c->$col)->value;
            $row[] = $location;
            break;

          case 'multitrack':
            // track is day converted to day of week
            $time = $c->day . ' ' . explode(':', $c->time_slot)[0];
            // Fri/Sat get AM/PM, Sun gets day of week
            $day = date('w', strtotime($time));
            if ($day == 5 || $day == 6) {
              $row[] = date('l A', strtotime($time));
            } else {
              $row[] = date('l', strtotime($time));
            }
            break;

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
              -2 => 'Trashed',
              0 => 'Unpublished',
              1 => 'Published',
              2 => 'Archived',
              3 => 'New',
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

    $query = $this->db->getQuery(true);
    $columnNames = ['id', 'name'];

    $eventAlias = $this->eventAlias;
    $query->select($this->db->qn($columnNames))
      ->from($this->db->qn('#__claw_presenters'))
      ->where($this->db->qn('published') . ' = 1')
      ->where($this->db->qn('event') . ' = :event')
      ->bind(':event', $eventAlias)
      ->order('name ASC');

    $this->db->setQuery($query);
    $presenterRowIds = $this->db->loadObjectList('id');

    // Define the base tmp path
    $tmpBasePath = Factory::getApplication()->get('tmp_path');

    // Create a unique folder name, e.g., using a timestamp or a unique ID
    $uniqueFolderName = 'presenters_' . uniqid();
    $tempFolderPath = implode(DIRECTORY_SEPARATOR, [$tmpBasePath, $uniqueFolderName]);
    $zipFileName = implode(DIRECTORY_SEPARATOR, [$tmpBasePath, $filename]);

    // Check if the directory already exists just in case
    if (!is_dir(Path::clean($tempFolderPath))) {
      Folder::create($tempFolderPath);
    }

    $cache = new DbBlob(
      db: $this->db,
      cacheDir: $tempFolderPath,
      prefix: 'orig_',
      extension: 'jpg'
    );
    $archiveFiles = $cache->toFile(
      tableName: '#__claw_presenters',
      rowIds: array_keys($presenterRowIds),
      key: 'image',
    );

    /**** JOOMLA METHOD FAILS
    $archive = new Archive(['tmp_path' => $tmpBasePath]);

    try {
        $zipFileAdapter = $archive->getAdapter('zip');
        // HERE: says it wants list of files, but really is wants content?
        $zipFile = $zipFileAdapter->create($zipFileName, $archiveFiles);
    } catch (\Exception $e) {
        // Handle exception
        echo "Error creating zip archive: " . $e->getMessage();
        return;
    }
     *****/

    /** PHP METHOD */
    $zip = new \ZipArchive();
    if ($zip->open($zipFileName, \ZipArchive::CREATE) !== TRUE) {
      echo "Error creating zip archive";
      return;
    }

    foreach ($archiveFiles as $id => $file) {
      $name = $presenterRowIds[$id]->name . '-' . $id . '.jpg';
      $src = implode(DIRECTORY_SEPARATOR, [JPATH_ROOT, $file]);
      $zip->addFromString($name, \file_get_contents($src));
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
    Folder::delete($tempFolderPath);
  }
}
