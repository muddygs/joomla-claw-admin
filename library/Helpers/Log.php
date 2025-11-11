<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Helpers;

final class Log
{
  public array $log = [];

  public function __construct() {}

  // TODO: format as some sort of table that makes sense, probably collect more than msg
  public function Log(string $msg, string $class = "")
  {
    if ($class) $msg = '<span class="' . $class . '">' . $msg . '</span>';
    $this->log[] = $msg;
  }

  public function FormatLog(): string
  {
    return '<p>' . implode('</p><p>', $this->log) . '</p>';
  }

  public function __invoke(string $msg, string $class = ''): void
  {
    $this->Log($msg, $class);
  }
}
