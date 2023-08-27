<?php

namespace ClawCorpLib\Helpers;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Image\Image;
use Joomla\CMS\User\UserHelper;
use Joomla\Database\DatabaseDriver;
use LogicException;

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
   * Returns hh:mm formatted string in seconds
   * @param mixed $t 
   * @return int|bool 
   */
  public static function timeToInt($t): int|bool
  {
    $ts = explode(':', $t);
    if (count($ts) < 2) return false;
    return 60 * ($ts[0] * 60 + $ts[1]);
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
    if (0 === strpos($time, '00:00')) {
      $time = "Midnight";
    } else if (0 === strpos($time, '12:00')) {
      $time = "Noon";
    } else {
      date_default_timezone_set('etc/UTC');
      $time = date('g:iA', strtotime(substr($time, 0, 5)));
    }

    return $time;
  }

  /**
   * Returns array with short day (Mon,Tue) to sql date for the event week starting Monday
   */
  public static function getDateArray(string $startDate, bool $dateOnly = false)
  {
    $result = [];

    $date = Factory::getDate($startDate);

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

  public static function getClawFieldValues(DatabaseDriver $db, string $section): array
  {
    $query = $db->getQuery(true);
    $query->select(['value', 'text'])
      ->from('#__claw_field_values')
      ->where('fieldname = :fieldname')
      ->order('value')
      ->bind(':fieldname', $section);
    $db->setQuery($query);
    return $db->loadObjectList('value');
  }

#region User Helpers
  public static function getUsersByGroupName(DatabaseDriver $db, string $groupname): array
  {
    $groupId = Helpers::getGroupId($db, $groupname);

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
  public static function getUserGroupsByName(DatabaseDriver $db, int $userId = 0): array
  {
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

  public static function getGroupId(DatabaseDriver $db, $groupName): int
  {
    $query = $db->getQuery(true);
    $query->select($db->qn(['id']))
      ->from($db->qn('#__usergroups'))
      ->where('LOWER(' . $db->qn('title') . ')=' . $db->q(strtolower($groupName)));

    $db->setQuery($query);
    $groupId = $db->loadResult();

    return $groupId != null ? $groupId : 0;
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

    $mailer->setSender(['webmaster@clawinfo.org', 'CLAW']);
    $mailer->setSubject('Some Error Has Occurred');
    $mailer->addRecipient('webmaster@clawinfo.org');

    $body = 'PATH: ' . $path . "\n";
    $body .= "DATA FOLLOWS:\n";
    $body .= print_r($data, true);
    $mailer->setBody($body);

    $mailer->Send();
  }

  public static function StringCleanup(string $s, bool $lower = false): string
  {
    $s = preg_replace('/[\x00-\x20\x7F-\xFF]/', '', $s);
    return $lower ? strtolower($s) : $s;
  }

  public static function ProcessImageUpload(
    string $source,
    string $thumbnail,
    int $resize = 300,
    string $copyto = '',
    bool $deleteSource = false,
    int $quality = 80
  ): bool {
    if (file_exists($source)) {
      $result = true;
      if ($copyto != '') $result = copy($source, $copyto);

      if (!$result) {
        //echo('Unable to save original photo file: '. $orig);
        return false;
      } else {
        try {
          $image = new Image();
          $image->loadFile($source);
          $exif = @exif_read_data($source);
          $image->resize($resize, $resize, false);

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
          $image->toFile($thumbnail, IMAGETYPE_JPEG, ['quality' => $quality]);
        } catch (LogicException $ex) {
          //echo('Unable to save web photo file: '. $web);
          return false;
        }
      }
    } else {
      return false;
    }

    if ( $deleteSource ) unlink($source);

    return true;
  }
}
