<?php

namespace ClawCorpLib\Lib;

use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Enums\SponsorshipType;
use Joomla\CMS\Date\Date;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Factory;

class Sponsor
{
  public string $name = '';
  public string $link = '';
  public SponsorshipType $type;
  public string $description = '';
  public string $logo_small = '';
  public string $logo_large = '';
  public EbPublishedState $published;
  public int $ordering;
  public ?Date $expires;
  public ?Date $mtime;

  private DatabaseDriver $db;

  public function __construct(
    public int $id
  ) {
    if ($this->id == 0) {
      throw new \Exception("Sponsors::__construct() called with no sponsor ID");
    }

    $this->db = Factory::getContainer()->get('DatabaseDriver');
    $this->fromSqlRow();
  }

  private function toSqlObject(): object
  {
    $result = new \stdClass();

    $result->id = $this->id;
    $result->name = $this->name;
    $result->link = $this->link;
    $result->type = $this->type->value;
    $result->description = $this->description;
    $result->logo_small = $this->logo_small;
    $result->logo_large = $this->logo_large;
    $result->published = $this->published->value;
    $result->ordering = $this->ordering;
    $result->expires = is_null($this->expires) ? $this->db->getNullDate() : $this->expires->toSql();
    $result->mtime = (new Date())->toSql();

    return $result;
  }

  private function fromSqlRow()
  {
    $query = $this->db->getQuery(true);
    $query->select('*')
      ->from('#__claw_sponsors')
      ->where('id = :id')
      ->bind(':id', $this->id);
    $this->db->setQuery($query);
    $result = $this->db->loadObject();

    if ($result == null) return;

    $this->id = $result->id;
    $this->name = $result->name;
    $this->link = $result->link;
    $this->type = SponsorshipType::tryFrom($result->type) ?? SponsorshipType::None;
    $this->description = $result->description ?? '';
    $this->logo_small = $result->logo_small;
    $this->logo_large = $result->logo_large;
    $this->published = EbPublishedState::tryFrom($result->published) ?? EbPublishedState::any;
    $this->ordering = $result->ordering;
    $this->expires = str_starts_with($result->expires, '0000') ? null : new Date($result->expires);
    $this->mtime = str_starts_with($result->mtime, '0000') ? null : new Date($result->mtime);
  }

  public function save(): bool
  {
    $db = $this->db;

    $data = $this->toSqlObject();

    if ($this->id == 0) {
      $db->insertObject('#__claw_sponsors', $data);
      $this->id = $db->insertid();
    } else {
      $db->updateObject('#__claw_sponsors', $data, 'id');
    }

    return true;
  }
}
