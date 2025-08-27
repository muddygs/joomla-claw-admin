<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\Mail\MailerFactoryInterface;

final class Mailer
{
  public function __construct(
    public array $tomail, //parallel arrays
    public array $toname, //parallel arrays 
    public string $fromname,
    public string $frommail,
    public string $subject,
    public string $body = '',
    public array $attachments = [],
    public array $cc = [],
    public array $bcc = [],
    public string $replyTo = '',
  ) {}

  public function send(): bool
  {
    $mailer = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();

    $sender = [
      $this->frommail,
      $this->fromname,
    ];

    $mailer->setSender($sender);
    $mailer->addRecipient($this->tomail, $this->toname);
    $mailer->setSubject($this->subject);
    $mailer->setBody($this->body);
    $mailer->isHTML(true);

    if (!empty($this->attachments)) {
      foreach ($this->attachments as $attachment) {
        /* $path,
           $name = '',
           $encoding = 'base64',
           $type = 'application/octet-stream',
           $disposition = 'attachment' */

        $path = implode(DIRECTORY_SEPARATOR, [
          JPATH_ROOT,
          $attachment,
        ]);

        $filename = basename($attachment);

        if (file_exists($path)) $mailer->addAttachment($path, $filename, 'base64', 'application/octet-stream', 'attachment');
      }
    }

    if (!empty($this->cc)) {
      $mailer->addCC($this->cc);
    }
    if (!empty($this->bcc)) {
      $mailer->addBCC($this->bcc);
    }
    if (!empty($this->replyTo)) {
      $mailer->addReplyTo($this->replyTo);
    }

    try {
      return $mailer->send();
    } catch (\Exception $e) {
      Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
      return false;
    }
  }

  public function appendToMessage(string $message): void
  {
    $this->body .= $message;
  }

  public function arrayToTable(array $data, array $exclusions = []): string
  {
    $table = '<table width="100%" cellpadding="10" border="1" style="border:1px solid #333">';
    foreach ($data as $key => $value) {
      if (in_array($key, $exclusions)) continue;

      // Format the $key a bit
      $key = ucwords(str_replace('_', ' ', $key));
      if (gettype($value) == 'array') {
        $value = implode(',', $value);
      }

      $table .= '<tr>';
      $table .= '<td style="vertical-align:top;"><b>' . $key . '</b></td>';
      $table .= '<td style="vertical-align:top;">' . $value . '</td>';
      $table .= '</tr>';
    }
    $table .= '</table>';
    return $table;
  }
}
