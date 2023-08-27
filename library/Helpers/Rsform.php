<?php

namespace ClawCorpLib\Helpers;

use Joomla\Database\DatabaseDriver;

class Rsform
{

  public static function getFormId(DatabaseDriver $db, string $formAlias): int
  {
    $query = "SELECT FormId FROM #__rsform_forms WHERE FormName = " . $db->q($formAlias);
    $db->setQuery($query);
    $result = $db->loadResult();

    if (null == $result) {
      die("Unknown form alias: " . $formAlias);
    }

    return $result;
  }

  public static function getSubmissionIds(DatabaseDriver $db, int $formId): array
  {
    $query = "SELECT SubmissionId FROM #__rsform_submissions WHERE FormId = " . $db->q($formId) . ' ORDER BY SubmissionId ASC';
    $db->setQuery($query);
    $result = $db->loadColumn();

    if (null == $result) {
      die("No data or unknown form id: " . $formId);
    }

    return $result;
  }

  public static function getSubmissionData(DatabaseDriver $db, int $formId, int $submissionId): array
  {
    $query = "SELECT FieldName,FieldValue FROM #__rsform_submission_values WHERE FormId=" . $db->q($formId) . " AND SubmissionId=" . $db->q($submissionId);
    $db->setQuery($query);
    $result = $db->loadObjectList('FieldName');
    if (null == $result) {
      die("Error Loading Data for Submission Id: " . $submissionId);
    }

    return $result;
  }
}
