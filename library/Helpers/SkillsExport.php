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
use ClawCorpLib\Skills\Skills;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseDriver;
use RuntimeException;

class SkillsExport
{
  private EventInfo $eventInfo;
  const YAPP_DATE_FORMAT = 'm/d/Y'; // Yapp uses the poorly formatted US m/d/y format
  const YAPP_TIME_FORMAT = 'g:i A';

  // constructor
  public function __construct(
    public DatabaseDriver $db,
    public readonly string $eventAlias = ''
  ) {
    $this->eventInfo = new EventInfo($this->eventAlias);
  }

  public static function rsformJson()
  {
    $eventInfo = new EventInfo(Aliases::current(true));
    $classes = Skills::get($eventInfo, SkillPublishedState::published, ['title']);

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

  private function presentersPreviewCache(array $presenterIds)
  {
    $config = new Config($this->eventInfo->alias);
    $path = $config->getConfigText(ConfigFieldNames::CONFIG_IMAGES, 'presenters', '/images/skills/presenters');

    // Images must be on server for external app import
    $cache = new DbBlob(
      db: $this->db,
      cacheDir: JPATH_ROOT . $path,
      prefix: 'web_',
      extension: 'jpg'
    );
    $filenames = $cache->toFile(
      tableName: '#__claw_presenters',
      rowIds: $presenterIds,
      key: 'image_preview',
    );

    return $filenames;
  }

  public function presentersCSV(string $filename, bool $publishedOnly = true)
  {
    $presenterArray = Presenters::get(
      $this->eventInfo,
      $publishedOnly ? SkillPublishedState::published : SkillPublishedState::any
    );

    if (!count($presenterArray)) {
      throw new GenericDataException('No presenters to export for event ' . $this->eventInfo->description, 500);
    }

    $keys = $presenterArray->keys();

    /** @var \ClawCorpLib\Skills\Presenter */
    $presenter = $presenterArray[$keys[0]];

    // Display these columns first
    $preferred = [
      'id',
      'ownership',
      'published',
      'name',
      'legal_name',
      'bio',
      'email',
      'phone',
      'arrival',
    ];

    $publishedOnlyColumns = [
      'id',
      'name',
      'bio',
      'image_preview',
    ];

    // Renaming for readability
    // TODO: move into Presenter class
    $remapping = [
      'id' => 'Unique ID',
      'image' => 'Full Image',
      'image_preview' => 'Photo URL',
      'submission_date' => 'Submission Date',
      'ownership' => 'Ownership',
      'published' => 'Published',
      'arrival' => 'Arrival Day',
      'copresenter' => 'Is Copresenter?',
      'uid' => 'Joomla User ID',
      'archive_state' => 'Is Archived?',
      'bio' => 'About',
      'comments' => 'Comments',
      'copresenting' => 'Copresenting Classes',
      'email' => 'Email',
      'event' => 'Event Alias',
      'legal_name' => 'Legal Name',
      'name' => 'First Name',
      'phone' => 'Phone',
      'social_media' => 'Social Media',
    ];

    $columnNames = array_keys((array)($presenter->toSimpleObject()));

    $ordering = Helpers::combineArrays($preferred, $columnNames);

    if ($publishedOnly) {
      $ordering = array_intersect($ordering, $publishedOnlyColumns);
    }

    $headers = array_map(function ($x) use ($remapping) {
      return array_key_exists($x, $remapping) ? $remapping[$x] : $x;
    }, $ordering);

    $imageMap = $this->presentersPreviewCache($keys);

    // Small maps to keep the row builder clean
    $publishedMap = [
      SkillPublishedState::unpublished->value => 'Unpublished',
      SkillPublishedState::published->value   => 'Published',
      SkillPublishedState::new->value         => 'New',
    ];

    $ownershipMap = [
      SkillOwnership::user->value  => 'User',
      SkillOwnership::admin->value => 'Admin',
    ];

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
    set_time_limit(0);
    ini_set('error_reporting', E_NOTICE);

    $fp = fopen('php://output', 'wb');
    fputcsv($fp, $headers);

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
            $rel = $imageMap[$p->id] ?? '';
            $row[] = $rel ? 'https://www.clawinfo.org/' . $rel : '';
            break;
          case 'bio':
            // Convert to HTML and append socials
            $bio = Helpers::cleanHtmlForCsv($p->$col);
            if (trim($p->social_media)) $bio .= "\n\n" . $p->social_media;
            $row[] = $bio;
            break;
          case 'published':
            $row[] = $publishedMap[$p->published->value] ?? 'Unknown';
            break;
          case 'ownership':
            $row[] = $ownershipMap[$p->ownership->value] ?? 'Unknown';
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
  }

  public function classesCSV(string $filename, bool $publishedOnly = true)
  {
    $skillArray = Skills::get(
      $this->eventInfo,
      $publishedOnly ? SkillPublishedState::published : SkillPublishedState::any
    );

    $presenterArray = Presenters::get(
      $this->eventInfo,
      $publishedOnly ? SkillPublishedState::published : SkillPublishedState::any
    );

    if (!count($presenterArray)) {
      throw new GenericDataException('No presenters available to export for event ' . $this->eventInfo->description, 500);
    }

    if (!count($skillArray)) {
      throw new GenericDataException('No classes available to export for event ' . $this->eventInfo->description, 500);
    }

    $locations = Locations::get($this->eventAlias);

    // Load the global config for com_claw. We need to get the RS Form ID
    /** @var Joomla\CMS\Application\AdministratorApplication */
    $app = Factory::getApplication();
    $componentParams = ComponentHelper::getParams('com_claw');
    $seSurveyMenuId = $componentParams->get('se_survey_link', 0);
    $surveyLink = '';
    $siteUrl = '';

    if ($seSurveyMenuId > 0) {
      $menu = $app->getMenu('site');
      $item = $menu->getItem($seSurveyMenuId);

      if (!$item) {
        throw new RuntimeException("Survey link not configured - cannot complete export.");
      }

      // Get main site link
      $uri = Uri::getInstance();
      $siteUrl = rtrim($uri::root(), '/');
      $surveyLink = $siteUrl . '/' . $item->alias;
    }

    // Load database columns
    $keys = $skillArray->keys();

    /** @var \ClawCorpLib\Skills\Skill */
    $skill = $skillArray[$keys[0]];

    $preferred = [
      'id',
      'day',
      'multitrack',
      'date',
      'start_time',
      'end_time',
      'people',
      'people_public_name',
      'ownership',
      'published',
      'copresenter_info',
      'title',
      'av',
      'location',
    ];

    $publishedOnlyColumns = [
      'description',
      'end_time',
      'id',
      'location',
      'multitrack',
      'people',
      'start_time',
      'title',
      'yappday',
    ];

    $columnNames = array_keys((array)($skill->toSimpleObject()));
    $columnNames[] = 'multitrack';
    $columnNames[] = 'date';
    $columnNames[] = 'start_time';
    $columnNames[] = 'end_time';
    $columnNames[] = 'people';
    $columnNames[] = 'people_public_name';

    $ordering = Helpers::combineArrays($preferred, $columnNames);

    if ($publishedOnly) {
      $ordering = array_intersect($ordering, $publishedOnlyColumns);
    }

    $remapping = [
      'id' => 'Unique ID',
      'day' => 'Day',
      'mtime' => '',
      'submission_date' => 'Submission Date',
      'ownership' => 'Ownership',
      'published' => 'Published',
      'other_presenter_ids' => 'Other Presenter IDs',
      'av' => 'A/V Requirements',
      'length_info' => 'Requested Length',
      'location' => 'Location',
      'presenter_id' => 'Class Owner',
      'archive_state' => 'Archive State',
      'audience' => 'Audience',
      'category' => 'Category',
      'comments' => 'Submission Comments',
      'copresenter_info' => 'Copresenter Info',
      'description' => 'Class Description',
      'equipment_info' => 'Equipment Requests',
      'event' => 'Event Alias',
      'requirements_info' => 'Participant Requirements',
      'time_slot' => 'Time Slot',
      'title' => 'Title',
      'track' => 'Special Track',
      'type' => 'Class Type',
      'multitrack' => 'Track',
    ];

    $headers = array_map(function ($x) use ($remapping) {
      return array_key_exists($x, $remapping) ? $remapping[$x] : $x;
    }, $ordering);

    // Load category strings
    $config = new Config($this->eventAlias);
    $categories = $config->getConfigValuesText(ConfigFieldNames::SKILL_CATEGORY);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
    set_time_limit(0);
    ini_set('error_reporting', E_NOTICE);

    $fp = fopen('php://output', 'wb');
    fputcsv($fp, $headers);

    /** @var \ClawCorpLib\Skills\Skill */
    foreach ($skillArray as $c) {
      $row = [];
      foreach ($ordering as $col) {
        switch ($col) {
          case 'id':
            $row[] = 'class_' . $c->id;
            break;
          case 'day':
            if (is_null($c->day)) {
              $row[] = '';
            } else {
              $row[] = $c->day->format('l');
            }
            break;
          case 'yappday':
            if (is_null($c->day)) {
              $row[] = '';
            } else {
              $row[] = $c->day->format(self::YAPP_DATE_FORMAT);
            }
            break;
          case 'start_time':
            [$time, $length] = explode(':', $c->time_slot);
            if (is_null($c->day)) {
              $row[] = '';
            } else {
              $day = clone $c->day;
              $day->modify($time);
              $row[] = $day->format(self::YAPP_TIME_FORMAT);
            }
            break;

          case 'end_time':
            // take start time and add length
            [$time, $length] = explode(':', $c->time_slot);
            if (is_null($c->day)) {
              $row[] = '';
            } else {
              $day = clone $c->day;
              $day->modify($time);
              $day->modify('+ ' . $length . ' minutes');
              $row[] = $day->format(self::YAPP_TIME_FORMAT);
            }
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
            $row[] = $locations[$c->location]->value ?? '';
            break;

          case 'multitrack':
            if (!$c->time_slot) {
              $row[] = 'Not configured';
              break;
            }

            // hour:length - hour military hhhh, length in minutes mmm
            $time = explode(':', $c->time_slot)[0];
            // Fri/Sat get AM/PM, Sun gets day of week
            $day = date('l', strtotime($c->day));

            $row[] = match ($day) {
              'Friday', 'Saturday' => $day . ' ' . date('A', strtotime($time)),
              'Sunday' => $day,
              default => 'Unhandled timeslot'
            };

            break;

          case 'description':
            $survey = '';

            if ($surveyLink != '' && $publishedOnly) {
              $newurl = $surveyLink . '?form[classTitleParam]=' . $c->id;
              $oldurl = '/skills_survey_' . $c->id;
              $redirect = new Redirects($this->db, $oldurl, $newurl, 'survey_' . $c->id);
              $redirectId = $redirect->insert();
              if ($redirectId) $survey = 'Survey: ' . $siteUrl . $oldurl . '<br/>';
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
            $row[] = is_array($c->$col) ? implode(', ', $c->$col) : $c->$col;
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
