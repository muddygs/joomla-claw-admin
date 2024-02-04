<?php

namespace ClawCorpLib\Helpers;

use ClawCorpLib\Enums\ConfigFieldNames;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventInfo;
use InvalidArgumentException;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\Exception\UnsupportedAdapterException;
use Joomla\Database\Exception\QueryTypeAlreadyDefinedException;
use RuntimeException;

class Skills
{
  private array $presenterCache = [];
  private array $classCache = [];
  private EventInfo $eventInfo;

  // constructor
  public function __construct(
    public DatabaseDriver $db,
    public readonly string $eventAlias = ''
  )
  {
    $this->eventInfo = new EventInfo($eventAlias);
  }

  public function GetPresentersList(bool $publishedOnly = false): array
  {
    if (count($this->presenterCache)) return $this->presenterCache;

    //if ( $this->eventAlias == '' ) $this->eventAlias = Aliases::current();

    $query = $this->db->getQuery(true);

    $query->select($this->db->qn(['id', 'uid', 'name', 'published']))
      ->from($this->db->qn('#__claw_presenters'))
      ->order('name ASC');

    if ( $publishedOnly ) {
      $query->where($this->db->qn('published') . ' = 1');
    } else {
      $query->where($this->db->qn('published') . ' IN (1,3)'); // published or new
    }

    if ( $this->eventAlias != '' ) {
      $eventAlias = $this->eventAlias;
      $query->where($this->db->qn('event') . ' = :event')
      ->bind(':event', $eventAlias);
    }

    $this->db->setQuery($query);
    $this->presenterCache = $this->db->loadObjectList('uid') ?? [];
    return $this->presenterCache;
  }

  /**
   * Load the presenter bio records (check event for determining current)
   * @param int $pid Presenter User ID
   * @return array|null Bio records array (of objects) or null on error
   */
  public function GetPresenterBios(int $pid): ?array
  {
    $query = $this->db->getQuery(true);
    $query->select('*')
      ->from($this->db->quoteName('#__claw_presenters'))
      ->where($this->db->qn('uid') . '= :uid')
      ->where('('. $this->db->qn('archive_state') . ' = "" OR ' . $this->db->qn('archive_state') . ' IS NULL)')
      ->bind(':uid', $pid);

    if ( $this->eventAlias != '' ) {
      $eventAlias = $this->eventAlias;
      $query->where($this->db->qn('event') . ' = :event')
      ->bind(':event', $eventAlias);
    }

    $query->order('mtime');

    $this->db->setQuery($query);
    return $this->db->loadObjectList();
  }

  /**
   * Load the presenter skills class records (check event for determining current)
   * @param int $pid User ID of Presenter
   * @return array|null Bio records array (of objects) or null on error
   */
  public function GetPresenterClasses(int $pid): ?array
  {
    $query = $this->db->getQuery(true);
    $query->select('*')
      ->from($this->db->quoteName('#__claw_skills'))
      ->where('((JSON_VALID('.$this->db->qn('presenters').') AND JSON_CONTAINS(' . $this->db->qn('presenters') . ', :copresenters)) OR ' . $this->db->qn('owner') . ' = :uid)')
      ->where('('.$this->db->qn('archive_state') . ' = "" OR ' . $this->db->qn('archive_state') . ' IS NULL)')
      ->bind(':uid', $pid)
      ->bind(':copresenters', $pid);

    if ( $this->eventAlias != '' ) {
      $eventAlias = $this->eventAlias;
      $query->where($this->db->qn('event') . ' = :event')
      ->bind(':event', $eventAlias);
    }

    $query->order('mtime');
    $query->setLimit(30);

    $this->db->setQuery($query);
    return $this->db->loadObjectList();
  }

  /**
   * Returns a list of classes for the given event
   * 
   * @param DatabaseDriver $db 
   * @param bool $publishedOnly (default: true)
   * @return array 
   * @throws UnsupportedAdapterException 
   * @throws QueryTypeAlreadyDefinedException 
   * @throws RuntimeException 
   * @throws InvalidArgumentException 
   */
  public function GetClassList(bool $publishedOnly = true): array
  {
    if (count($this->classCache)) return $this->classCache;

    $query = $this->db->getQuery(true);

    $eventAlias = $this->eventAlias;
    $query->select('*')
      ->from($this->db->qn('#__claw_skills'))
      ->where($this->db->qn('event') . ' = :event')->bind(':event', $eventAlias);
    
    if ( $publishedOnly ) {
      $query->where($this->db->qn('published') . '= 1')
        ->where($this->db->qn('day') . ' != "0000-00-00"')
        ->where($this->db->qn('time_slot') . ' IS NOT NULL')
        ->where($this->db->qn('time_slot') . ' != ""');
    }

    $query->order('day ASC, time_slot ASC, title ASC');

    $this->db->setQuery($query);
    $this->classCache = $this->db->loadObjectList('id') ?? [];
    return $this->classCache;
  }

  public function GetPresenter(int $uid, bool $published = true): ?object
  {
    $query = $this->db->getQuery(true);
    $eventAlias = $this->eventAlias;

    $query->select('*')
      ->from($this->db->qn('#__claw_presenters'))
      ->where($this->db->qn('uid') . ' = :uid')->bind(':uid', $uid)
      ->where($this->db->qn('event') . ' = :event')->bind(':event', $eventAlias);

    if ( $published ) {
      $query->where($this->db->qn('published') . ' = 1');
    }

    $this->db->setQuery($query);
    $presenter = $this->db->loadObject();

    if ( $presenter != null )
      $presenter->route = Route::_('index.php?option=com_claw&view=skillspresenter&id=' . $presenter->uid);

    return $presenter;
  }

  public function GetClass(int $cid): ?object
  {
    $query = $this->db->getQuery(true);

    $eventAlias = $this->eventAlias;
    $query->select('*')
      ->from($this->db->qn('#__claw_skills'))
      ->where($this->db->qn('id') . ' = :cid')->bind(':cid', $cid)
      ->where($this->db->qn('event') . ' = :event')->bind(':event', $eventAlias)
      ->where($this->db->qn('published') . ' = 1');

    $this->db->setQuery($query);
    $class = $this->db->loadObject();

    if ( null == $class ) return $class;

    if (empty($class->presenters)) {
      $presenterIds = [];
    } else {
      $presenterIds = json_decode($class->presenters);
    }
    array_unshift($presenterIds, $class->owner);

    $location = Locations::GetLocationById($class->location);
    $class->location = $location->value != '' ? $location->value : 'TBD';

    // day
    $class->day = date('l', strtotime($class->day));

    [$time, $length] = explode(':', $class->time_slot);
    // time
    $class->time = Helpers::formatTime($time);

    // length
    $class->length = (int)$length;

    $config = new Config($this->eventAlias);
    if ( $class->category != 'None' ) $class->category = $config->getConfigValuesText(ConfigFieldNames::SKILL_CATEGORY, $class->category);

    // Get the presenters
    $class->presenters = [];

    foreach ( $presenterIds AS $presenterId ) {
      $presenter = $this->GetPresenter($presenterId);
      if ( null == $presenter ) continue;
      $class->presenters[] = $presenter;
    }

    return $class;
  }

  public static function rsformJson()
  {
    // Database driver
    $db = Factory::getContainer()->get('DatabaseDriver');
    $skills = new Skills($db, Aliases::current(true));
    $classes = $skills->GetClassList();

    $results = [];

    foreach ( $classes as $class ) {
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
    if ( $this->eventAlias == '' ) {
      // error message
      throw new GenericDataException('eventAlias must be specified', 500);
    }

    $query = $this->db->getQuery(true);

    $columnNames = ['id', 'uid', 'name', 'bio', 'photo'];

    if ( !$publishedOnly )
      $columnNames = array_keys($this->db->getTableColumns('#__claw_presenters'));

    $eventAlias = $this->eventAlias;
    $query->select($this->db->qn($columnNames))
      ->from($this->db->qn('#__claw_presenters'))
      ->where($this->db->qn('event') . ' = :event')
      ->bind(':event', $eventAlias)
      ->order('name ASC');

    if ( $publishedOnly ) {
      $query->where($this->db->qn('published') . ' = 1');
    }

    $this->db->setQuery($query);
    $presenters = $this->db->loadObjectList() ?? [];

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="'. $filename . '"');
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
    ob_clean();
    ob_start();
    set_time_limit(0);
    ini_set('error_reporting', E_NOTICE);

    $fp = fopen('php://output', 'wb');
    fputcsv($fp, $columnNames);

    foreach ( $presenters AS $p ) {
      $row = [];
      foreach ( $columnNames AS $col ) {
        switch ( $col ) {
          case 'uid':
            $row[] = 'presenter_' . $p->$col;
            break;
          case 'photo':
            // Remove leading '/' from path
            $link = Helpers::convertMediaManagerUrl(ltrim($p->$col, '/'));
            $row[] = is_null($link) ? '' : $link;
            break;
          case 'bio':
            // Convert to HTML
            $row[] = Helpers::cleanHtmlForCsv($p->$col);
            break;
          case 'published':
            $row[] = match($p->$col) {
              -2 => 'Trashed',
              0 => 'Unpublished',
              1 => 'Published',
              2 => 'Archived',
              3 => 'New',
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
    if ( $this->eventAlias == '' ) {
      // error message
      throw new GenericDataException('eventAlias must be specified', 500);
    }

    $this->GetClassList(publishedOnly: $publishedOnly);
    $this->GetPresentersList(publishedOnly: $publishedOnly);

    // Load the global config for com_claw. We need to the RS Form ID
    /** @var Joomla\CMS\Application\AdministratorApplication */
    $app = Factory::getApplication();
    $componentParams = ComponentHelper::getParams('com_claw');
    $seSurveyMenuId = $componentParams->get('se_survey_link', 0);
    $surveyLink = '';
    $siteUrl = '';

    if ( $seSurveyMenuId > 0 ) {
      $menu = $app->getMenu('site');
      $item = $menu->getItem($seSurveyMenuId);

      // Get main site link
      $uri = Uri::getInstance();
      $siteUrl = $uri::root();
      $surveyLink = $siteUrl . $item->alias;
    }


    // $rsformId = $config['se_survey_link'];

    // Load database columns
    $columnNames = array_keys($this->db->getTableColumns('#__claw_skills'));
    $columnNames[] = 'multitrack';
    $columnNames[] = 'people';
    $columnNames[] = 'start_time';
    $columnNames[] = 'end_time';

    // Load category strings
    $config = new Config($this->eventInfo->alias);
    $categories = $config->getColumn(ConfigFieldNames::SKILL_CATEGORY);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="'. $filename . '"');
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
    ob_clean();
    ob_start();
    set_time_limit(0);
    ini_set('error_reporting', E_NOTICE);

    $fp = fopen('php://output', 'wb');
    fputcsv($fp, $columnNames);

    foreach ( $this->classCache AS $c) {
      $row = [];
      foreach ( $columnNames AS $col ) {
        switch ( $col ) {
          case 'id':
            $row[] = 'class_'.$c->$col;
            break;
          case 'start_time':
            $time = Helpers::formatTime(explode(':', $c->time_slot)[0]);
            if ( $time == 'Midnight' ) $time = '12:00 AM';
            if ( $time == 'Noon' ) $time = '12:00 PM';
            $row[] = $time;
            break;
          case 'end_time':
            // take start time and add length
            [ $time, $length ] = explode(':', $c->time_slot);
            $time = new \DateTime($c->day . ' ' . $time);
            $time->modify('+ '.$length .' minutes');
            $row[] = $time->format('g:i A');
            break;
          case 'owner':
            if ( !$publishedOnly) {
              $row[] = $this->presenterCache[$c->$col]->name;
            } else {
              $row[] = $c->$col;
            }
          case 'people':
            if (empty($c->presenters)) {
              $presenterIds = [];
            } else {
              // TODO: Fix decode
              $presenterIds = json_decode($c->presenters);
              if ( is_null($presenterIds)) $presenterIds = []; 
            }
            array_unshift($presenterIds, $c->owner);

            // Remove any unpublished presenter ids
            $presenterIds = array_filter($presenterIds, function($id) {
              return isset($this->presenterCache[$id]);
            });

            // Prepend "presenter_" to each id and join with commas
            $row[] = implode(',', array_map(function($id) {
              return 'presenter_' . $id;
            }, $presenterIds));
            break;
          case 'location':
            $location = Locations::GetLocationById($c->$col)->value;
            $row[] = $location;
            break;
          case 'multitrack':
            // track is day converted to day of week
            $time = $c->day . ' ' . explode(':', $c->time_slot)[0];
            // Fri/Sat get AM/PM, Sun gets day of week
            $day = date('w', strtotime($time));
            if ( $day == 5 || $day == 6 ) {
              $row[] = date('l A', strtotime($time));
            } else {
              $row[] = date('l', strtotime($time));
            }
            break;
          case 'description':
            $survey = '';

            if ( $surveyLink != '' && $publishedOnly ) {
              $newurl = $surveyLink . '?form[classTitleParam]=' . $c->id;
              $oldurl = $siteUrl . 'skills_survey_'.$c->id; 
              $redirect = new Redirects($this->db, $oldurl, $newurl, 'survey_'.$c->id);
              $redirectId = $redirect->insert();
              if ( $redirectId ) $survey = 'Survey: ' . $oldurl . '<br/>';
              $description = $survey . 'Category: ' . $categories[$c->category]->text . '<br/>' . $c->$col;
            } else {
              $description = $c->col;
            }

            // Convert category to text
            $description = Helpers::cleanHtmlForCsv($description);
            $row[] = $description;
            break;
            
          case 'published':
            $row[] = match($c->$col) {
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
    if ( $this->eventAlias == '' ) {
      // error message
      throw new GenericDataException('eventAlias must be specified', 500);
    }

    $query = $this->db->getQuery(true);
    $columnNames = ['id', 'uid', 'name', 'bio', 'photo'];

    $eventAlias = $this->eventAlias;
    $query->select($this->db->qn($columnNames))
      ->from($this->db->qn('#__claw_presenters'))
      ->where($this->db->qn('published') . ' = 1')
      ->where($this->db->qn('event') . ' = :event')
      ->bind(':event', $eventAlias)
      ->order('name ASC');

    $this->db->setQuery($query);
    $presenters = $this->db->loadObjectList() ?? [];

    // Define the base tmp path
    $tmpBasePath = Factory::getApplication()->get('tmp_path');

    // Create a unique folder name, e.g., using a timestamp or a unique ID
    $uniqueFolderName = 'presenters_' . uniqid();
    $tempFolderPath = implode(DIRECTORY_SEPARATOR,[$tmpBasePath, $uniqueFolderName]);
    $zipFileName = implode(DIRECTORY_SEPARATOR, [$tmpBasePath, $filename]);

    // Check if the directory already exists just in case
    if (!Folder::exists($tempFolderPath)) {
      // Create the directory
      Folder::create($tempFolderPath);
    }

    $archiveFiles = [];

    foreach ( $presenters AS $p ) {
      if ( $p->photo !== '') {
        if (is_file(implode(DIRECTORY_SEPARATOR, [JPATH_ROOT, $p->photo]))) {
          $name = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $p->name));

          $orig = implode(DIRECTORY_SEPARATOR, [JPATH_ROOT, 'images', 'skills', 'presenters', 'orig', basename($p->photo)]);
          $tmp = implode(DIRECTORY_SEPARATOR, [$tempFolderPath, $name.'_'.$p->uid.'.jpg']);
          $archiveFiles[basename($tmp)] = $tmp;

          // Copy the file to the temp folder
          if ( !copy($orig, $tmp) ) {
            // error message
            echo "<p>Unable to copy file for {$p->name}</p>";
          }
        }
      }
    }

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
    if ( $zip->open($zipFileName, \ZipArchive::CREATE) !== TRUE ) {
      echo "Error creating zip archive";
      return;
    }

    foreach ( $archiveFiles AS $name => $file ) {
      $zip->addFromString($name, \file_get_contents($file));
    }

    $zip->close();

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="'. $filename . '"');
    header("Cache-Control: no-store");
    header('Content-Length: '.filesize($zipFileName));

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
