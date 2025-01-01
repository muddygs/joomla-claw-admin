<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Skills;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\User\UserHelper;

\defined('JPATH_PLATFORM') or die;

final class UserState
{
  // Probably can pretend these are real for code hints
  public ?object $presenter; // Presenter
  public array $skills; // Skill[]

  public bool $submissionsOpen = false;
  public bool $submissionsBioOnly = false;
  public string $event;

  public function __construct(
    public int $uid,
    private string $token = '',
  ) {
    if ($this->uid == 0) {
      throw new \InvalidArgumentException("User id required.");
    }

    $this->presenter = null;
    $this->skills = [];

    $this->event = Aliases::current(true);
    if ($this->token == '') $this->setToken();
  }

  public static function get(string $token): UserState
  {
    $state = Helpers::sessionGet($token);
    /** @var \Joomla\CMS\Application\SiteApplication */
    if ($state != '') {
      $state = unserialize($state);
      if ($state !== false) return $state;
    }

    throw new \Exception('Permission denied.');
  }

  public function isValid(): bool
  {
    /** @var \Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();
    $identity = $app->getIdentity();
    if ($this->uid != $identity->id) {
      return false;
    }

    $params = $app->getParams('com_claw');
    $group = $params->get('se_group', 0);

    if (0 == $group) {
      return false;
    }

    $groups = $identity->getAuthorisedGroups();

    return in_array($group, $groups);
  }

  public function setPresenter(Presenter $presenter)
  {
    $presenter->loadImageBlobs();
    $this->presenter = $presenter->toSimpleObject();
  }

  public function isBioCurrent(): bool
  {
    if (is_null($this->presenter)) return false;

    return $this->presenter->event == $this->event;
  }

  public function addSkill(Skill $skill)
  {
    if ($skill->id == 0) {
      throw new \InvalidArgumentException("Skill id cannot be 0.");
    }

    $this->skills[$skill->id] = $skill->toSimpleObject();
  }

  private function setToken()
  {
    $this->token = ApplicationHelper::getHash(UserHelper::genRandomPassword());
  }

  public function getToken(): string
  {
    return $this->token;
  }
}
