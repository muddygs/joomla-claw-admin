<?php

namespace ClawCorpLib\Helpers;

use DateTime;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Image\Image;
use Joomla\CMS\User\UserHelper;
use Joomla\Database\DatabaseDriver;
use LogicException;
use Joomla\CMS\Uri\Uri;
use RuntimeException;

class Helpers
{

#region Date/Time functions
  /**
   * Quicky that produces a mostly correct SQL time
   * TODO: set time zone
   * @return string
   */
  public static function mtime(): string
  {
    $date = new Date('now');
    return $date->toSQL(true);
  }

  /** 
   * Returns event days from tue to mon
   * @return array  */
  public static function getDays(): array
  {
    return [
      'tue',
      'wed',
      'thu',
      'fri',
      'sat',
      'sun',
      'mon',
    ];
  }


  /**
   * Returns hh:mm formatted string as seconds
   * @param mixed $t 
   * @return int|bool 
   */
  public static function timeToInt($t): int|bool
  {
    $ts = explode(':', $t);
    if (count($ts) < 2 || !\is_numeric($ts[0]) || !\is_numeric($ts[1]) || 
      $ts[0] < 0 || $ts[0] > 23 || $ts[1] < 0 || $ts[1] > 59) return false;
    return strtotime('1970-01-01 ' . implode(':', [ $ts[0], $ts[1], '00' ]));
  }

  public static function dateToDay(string $date): string
  {
    $d = Factory::getDate($date);
    return $d->format('D');
  }

  public static function dateToDayNum(string $date): int
  {
    $d = Factory::getDate($date);
    $n = $d->format('w');
    if ( $n < 2 ) $n += 7;
    return $n;
  }

  /**
   * Converts hh:mm:ss to hh:mm XM, 00:00 -> Midnight, 12:00 -> Noon
   * @param string Time string (hh:mm:ss)
   * @return string Formatted time
   */
  public static function formatTime(string $time): string
  {
    if (preg_match('/^([01]\d|2[0-3])([0-5]\d)$/', $time, $matches)) {
      $hour = $matches[1];
      $minute = $matches[2];

      $time = DateTime::createFromFormat('H:i', "$hour:$minute");
      return $time->format('g:i A');
    }

    if (str_starts_with($time, '00:00')) {
      $time = "Midnight";
    } else if (str_starts_with($time, '12:00')) {
      $time = "Noon";
    } else {
      date_default_timezone_set('etc/UTC');
      $time = date('g:i A', strtotime(substr($time, 0, 5)));
    }

    return $time;
  }

  /**
   * Returns array with short day (Mon,Tue) to sql date for the event week starting Monday
   */
  public static function getDateArray(Date $date, bool $dateOnly = false)
  {
    $result = [];

    if ($date->dayofweek != 1) // 0 is Sunday
    {
      die('Starting date must be a Monday');
    }

    $date->setTime(0, 0);
    for ($i = 0; $i < 7; $i++) {
      $date->modify(('+1 day'));
      $d = $date->toSql();
      if ($dateOnly) $d = substr($d, 0, 10);
      $result[$date->format('D')] = $d;
    }

    return $result;
  }

#endregion Date/Time functions

#region User Helpers
  public static function getUsersByGroupName(DatabaseDriver $db, string $groupname): array
  {
    $groupId = Helpers::getGroupId($groupname);

    if (!$groupId) return [];

    $query = $db->getQuery(true);
    $query->select(['m.user_id', 'u.name'])
      ->from('#__user_usergroup_map m')
      ->leftJoin('#__users u ON u.id = m.user_id')
      ->where('m.group_id = ' . $groupId)
      ->order('u.name');
    $db->setQuery($query);
    $users = $db->loadObjectList();

    return $users != null ? $users : [];
  }

  /**
   * Provides an associative array, keyed by group title, of user groups by name.
   * @return array Group list
   */
  public static function getUserViewLevelsByName(DatabaseDriver $db, int $userId = 0): array
  {
    if ( $userId == 0 ) {
      $identity = Factory::getApplication()->getIdentity();
      if (!$identity) return [];

      $userId = $identity->id;
    }

    $views = Access::getAuthorisedViewLevels($userId);

    $query = $db->getQuery(true);
    $query->select($db->qn(['id', 'title']))
      ->from($db->qn('#__viewlevels'))
      ->where('id IN (' . implode(',', $query->bindArray($views)) . ')');
    $db->setQuery($query);
    $avl  = $db->loadAssocList('title');

    return $avl;
  }

  /**
   * Create associative array of group titles for the current user
   * 
   * @param int $userId (optional) use specific user id. If not supplied, user comes from Factory object
   * 
   * @return array groups indexed by group name
   */
  public static function getUserGroupsByName(int $userId = 0): array
  {
    $db = Factory::getContainer()->get('DatabaseDriver');

    if (!$userId) {
      $identity = Factory::getApplication()->getIdentity();

      if (!$identity || !$identity->id) {
        return [];
      }
      
      $userId = $identity->id;
    }

    $groupIds = UserHelper::getUserGroups($userId);
    
    $query = $db->getQuery(true);
    $query->select(['id', 'title'])
    ->from('#__usergroups')
    ->where('id IN (' . implode(',',$query->bindArray($groupIds)) . ')');
    $db->setQuery($query);
    $groups  = $db->loadAssocList('title');

    return $groups != null ? $groups : [];
  }

  public static function getGroupId(string $groupName): int
  {
    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = $db->getQuery(true);
    $query->select($db->qn(['id']))
      ->from($db->qn('#__usergroups'))
      ->where('LOWER(' . $db->qn('title') . ')=' . $db->q(strtolower($groupName)));

    $db->setQuery($query);
    $groupId = $db->loadResult();

    return $groupId != null ? $groupId : 0;
  }

  public static function getAccessId($accessLevelName): int
  {
    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = $db->getQuery(true);
    $query->select($db->qn(['id']))
      ->from($db->qn('#__viewlevels'))
      ->where($db->qn('title') . ' LIKE ' . $db->q($accessLevelName));

    $db->setQuery($query);
    $accessLevelId = $db->loadResult();

    return $accessLevelId != null ? $accessLevelId : 0;
  }


  /**
   * Get the Joomla user id for an email address
   * @param string The email address
   * @return int The id (or null on error)
   */
  public static function getUserIdByEmail(DatabaseDriver $db, string $email): int
  {
    $query = $db->getQuery(true);
    $query->select(['id'])
      ->from('#__users')
      ->where('email=' . $db->quote($email));
    $db->setQuery($query);
    $id = $db->loadResult();

    return ($id == null) ? 0 : intval($id);
  }
#endregion User Helpers

#region Session
  /**
   * Sets a CLAW-specific Joomla session variable.
   * @param string $key Key to variable
   * @param string $value Key's value
   */
  static function sessionSet(string $key, string $value): void
  {
    /** @var \Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();
    $session = $app->getSession();
    if ($session->isActive()) {
      $session->set('claw'.$key, $value);
    }
  }

  /**
   * Gets a CLAW-specific Joomla session variable
   * @param string Key to the variable
   * @param string Default value if not already set
   * @return string|null Value of key (or null on error)
   */
  static function sessionGet(string $key, string $default = ''): string|null
  {
    /** @var $app \Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();
    $session = $app->getSession();
    if ($session->isActive()) {
      return $session->get('claw'.$key, $default);
    }

    return null;
  }
#endregion Session

  /**
   * Pass in some data - it gets emailed to webmaster for debugging
   */
  public static function sendErrorNotification(string $path, $data)
  {
    $mailer = Factory::getMailer();

    $email = Config::getConfigValuesText('config_debug_email', 'email');
    $mailer->setSender([$email, 'CLAW']);
    $mailer->setSubject('Some Error Has Occurred');
    $mailer->addRecipient($email);

    $body = 'PATH: ' . $path . "\n";
    $body .= "DATA FOLLOWS:\n";
    $body .= print_r($data, true);
    $mailer->setBody($body);

    $mailer->Send();
  }

  /**
   * Process an input image into JPG (resize original, resize thumbnail), auto rotation on output(s)
   * @param string $source Uploaded file
   * @param string $thumbnail Thumbnail file to create
   * @param int $thumbsize Thumbnail size (default 300)
   * @param string $copyto Copy uploaded file to this location (default '' - do not copy)
   * @param int $origsize (optional) Resize original during copy
   * @param bool $deleteSource Default false
   * @param int $quality JPG quality (default 80)
   * @return bool 
   * @throws RuntimeException 
   */
  public static function ProcessImageUpload(
    string $source,
    string $thumbnail,
    int $thumbsize = 300,
    int $origsize = 0,
    string $copyto = '',
    bool $deleteSource = false,
    int $quality = 80
  ): bool {
    if ( $copyto ) {
      $success = self::imageRotate(source: $source, dest: $copyto, size: $origsize, quality: $quality);
      
      if ( !$success ) {
        if ( $deleteSource ) unlink($source);
        return false;
      }
    }
    
    $success = self::imageRotate(source: $source, dest: $thumbnail, size: $thumbsize, quality: $quality);

    if ( $deleteSource ) unlink($source);

    return $success;
  }

  /**
   * Rotates (based on EXIF data) and resizes an image (if requested)
   * @param string $source Any supported input image format
   * @param string $dest JPG filename
   * @param int $size Bounding size (default 0 - no resize)
   * @param int $quality JPG output quality (default 80)
   * @return bool Success/Fail
   * @throws RuntimeException 
   */
  public static function imageRotate(string $source, string $dest, int $size = 0, int $quality = 80): bool
  {
    if (file_exists($source)) {
      try {
        $image = new Image();
        $image->loadFile($source);
        $exif = @exif_read_data($source);
        if ( $size > 0) $image->resize($size, $size, false);

        if ($exif && array_key_exists('Orientation', $exif)) {
          switch ($exif['Orientation']) {
            case 3:
              $image->rotate(angle: 180.0, createNew: false);
              break;
            case 6:
              $image->rotate(angle: -90.0, createNew: false);
              break;
            case 8:
              $image->rotate(angle: 90.0, createNew: false);
              break;
          }
        }
        $image->toFile($dest, IMAGETYPE_JPEG, ['quality' => $quality]);
      } catch (LogicException $ex) {
        return false;
      }

      return true;
    }
  }

  /**
   * Returns Joomla x#x media representation to a site URL
   * @param string $mediaManagerPath Media manager path
   * @return string URL (null on error)
   */
  public static function convertMediaManagerUrl(string $mediaManagerPath): ?string
  {
    if ( trim($mediaManagerPath) == '' ) return null;
    
    // Split the internal path by the "#" symbol
    // It's ok if the # portion is missing
    $parts = explode("#", $mediaManagerPath);

    // The actual path should be the first element
    $actualPath = $parts[0];

    // Convert to full path if it's a relative path
    $fullPath = !str_starts_with($actualPath, '/') ? JPATH_ROOT . '/' . $actualPath : $actualPath;

    // Check if the file actually exists
    if (file_exists($fullPath)) {
        // Convert the internal path to URL using Uri class
        return Uri::root() . $actualPath;
    } else {
        // Handle the case where the file doesn't exist
        return null;
    }
  }

  public static function cleanHtmlForCsv($htmlString) {
    // Replace <br> and <br/> with two carriage returns "\r\n\r\n"
    $cleanedString = preg_replace('/<br\s*\/?>/i', "\r\n\r\n", $htmlString);

    // Remove anchor tags but keep the href part.
    // This finds all href attributes and replaces the anchor tag with its URL in parenthesis.
    $cleanedString = preg_replace_callback(
        '/<a\s+[^>]*href=(["\'])(.*?)\1[^>]*>(.*?)<\/a>/i',
        function($matches) {
            // If the link text is the same as the URL, we'll just use the URL
            if ($matches[3] === $matches[2]) {
                return $matches[2];
            }
            // Otherwise, return the link text followed by the URL in parenthesis
            return $matches[3] . ' (' . $matches[2] . ')';
        },
        $cleanedString
    );

    // Strip remaining HTML tags
    $cleanedString = strip_tags($cleanedString);

    return $cleanedString;
}


}
