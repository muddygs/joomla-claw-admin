<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Helpers;

use Joomla\Database\DatabaseDriver;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Skills\Skills;
use ClawCorpLib\Enums\SkillPublishedState;

class Rsform
{
  private int $formId;

  public function __construct(
    public DatabaseDriver $db,
    public readonly string $formAlias
  ) {
    $this->formId = $this->getFormId($db, $formAlias);

    if ($this->formId == null) {
      throw new \Exception("Unknown form alias: " . $formAlias);
    }
  }

  private function getFormId(DatabaseDriver $db, string $formAlias): ?int
  {
    $query = $this->db->getQuery(true);
    $query->select('FormId')
      ->from($this->db->quoteName('#__rsform_forms'))
      ->where($this->db->quoteName('FormName') . ' = ' . $this->db->q($formAlias));
    $this->db->setQuery($query);

    $result = $db->loadResult();

    return $result;
  }

  public function getSubmissionIds(): ?array
  {
    $query = $this->db->getQuery(true);
    $query->select('SubmissionId')
      ->from($this->db->quoteName('#__rsform_submissions'))
      ->where($this->db->quoteName('FormId') . ' = ' . $this->db->q($this->formId))
      ->order('SubmissionId ASC');
    $this->db->setQuery($query);

    $result = $this->db->loadColumn();

    return $result;
  }

  public function getSubmissionData(int $submissionId): ?array
  {
    $query = $this->db->getQuery(true);
    $query->select(['FieldName', 'FieldValue'])
      ->from($this->db->quoteName('#__rsform_submission_values'))
      ->where($this->db->quoteName('FormId') . ' = ' . $this->db->q($this->formId))
      ->where($this->db->quoteName('SubmissionId') . ' = ' . $this->db->q($submissionId));
    $this->db->setQuery($query);

    $result = $this->db->loadObjectList('FieldName');

    return $result;
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
}
