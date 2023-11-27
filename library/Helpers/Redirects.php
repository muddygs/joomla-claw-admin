<?php

namespace ClawCorpLib\Helpers;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;

class Redirects
{
  private array $redirectLinksColumns;
  private object $redirect;

  public function __construct(
    public DatabaseDriver $db,
    public string $oldUrl,
    public string $newUrl,
    public string $comment = '', // Used as an id for tracking future updates, blank to ignore
    public string $header = '301',
  ) {
    $this->redirectLinksColumns = array_keys($this->db->getTableColumns('#__redirect_links'));
    $this->setDefaults();
  }

  private function setDefaults()
  {
    // Set date
    $date = Factory::getDate()->toSql();

    $this->redirect =
      (object)[
        'id' => 0,
        'old_url' => $this->oldUrl,
        'new_url' => $this->newUrl,
        'referer' => '',
        'comment' => $this->comment,
        'hits' => '0',
        'published' => 1,
        'created_date' => $date,
        'modified_date' => $date,
        'header' => $this->header
      ];
  }

  public function get(): object
  {
    return $this->redirect;
  }

  public function set(string $key, string $value)
  {
    if (in_array($key, $this->redirectLinksColumns)) {
      $this->redirect->$key = $value;
    } else {
      throw new \Exception("Invalid redirect key: " . $key);
    }
  }

  public function insert(): int
  {
    // If comment is not blank, try to delete equivalent redirect first
    // or if oldurl already exists
    $query = $this->db->getQuery(true);
    $conditions = [
      $query->qn('old_url') . ' = ' . $query->q($this->oldUrl)
    ];

    if ($this->comment) {
      $conditions[] = $query->qn('comment') . ' = ' . $query->q($this->comment);
    }

    $query->delete($this->db->qn('#__redirect_links'))
      ->where($conditions, 'OR');
    
    $this->db->setQuery($query);
    $this->db->execute();

    // Insert new redirect
    $result = $this->db->insertObject('#__redirect_links', $this->redirect, 'id');

    return $result != false ? $this->redirect->id : 0;
  }
}
