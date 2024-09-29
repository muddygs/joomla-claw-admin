<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

use ClawCorpLib\Lib\Sponsors;
use ClawCorpLib\Enums\SponsorshipType;
use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Helpers\Mailer;
use ClawCorpLib\Iterators\SponsorArray;
use ClawCorpLib\Lib\Sponsor;

class SponsorsModel extends ListModel
{
  const LEGACY_WIDTH = 2;
  protected $db;

  private array $list_fields = [
    'id',
    'published',
    'name',
    'type',
  ];

  public function __construct($config = array())
  {
    if (empty($config['filter_fields'])) {
      $config['filter_fields'] = [];

      foreach ($this->list_fields as $f) {
        $config['filter_fields'][] = $f;
        $config['filter_fields'][] = 'a.' . $f;
      }
    }

    parent::__construct($config);

    $this->db = $this->getDatabase();
  }

  protected function populateState($ordering = 'name', $direction = 'ASC')
  {
    $app = Factory::getApplication();

    // List state information
    $value = $app->input->get('limit', $app->get('list_limit', 0), 'uint');
    $this->setState('list.limit', $value);

    $value = $app->input->get('limitstart', 0, 'uint');
    $this->setState('list.start', $value);

    $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
    $this->setState('filter.search', $search);

    // List state information.
    parent::populateState($ordering, $direction);
  }

  protected function getStoreId($id = '')
  {
    // Compile the store id.
    $id .= ':' . serialize($this->getState('filter.name'));
    $id .= ':' . $this->getState('filter.search');
    $id .= ':' . $this->getState('filter.state');
    //$id .= ':' . serialize($this->getState('filter.tag'));

    return parent::getStoreId($id);
  }

  protected function getListQuery()
  {
    $db    = $this->db;
    $query = $db->getQuery(true);

    // Select the required fields from the table.
    $query->select(
      $this->getState(
        'list.select',
        array_map(function ($a) use ($db) {
          return $db->quoteName('a.' . $a);
        }, $this->list_fields)
      )
    )
      ->from($db->quoteName('#__claw_sponsors', 'a'));

    // Filter by search in title.
    $search = $this->getState('filter.search');
    $type = $this->getState('filter.type');

    if (!empty($search)) {
      $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
      $query->where('(a.name LIKE ' . $search . ')');
    }

    if (!empty($type)) {
      $query->where('a.type = ' . $db->quote($type));
    }

    // Add the list ordering clause.
    $orderCol  = $this->getState('list.ordering', 'a.name');
    $orderDirn = $this->getState('list.direction', 'ASC');

    $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
    return $query;
  }

  public function eblast()
  {
    $allSponsors = (new Sponsors(published: true))->sponsors;

    $standard   = $this->getSponsorsByType($allSponsors, SponsorshipType::Master);
    $legacy   = $this->getSponsorsByType($allSponsors, SponsorshipType::Legacy_Master);

    $this->createBanner($standard, $legacy, 'MASTER SPONSORS', 'master_sponsors', 100, 150);

    $standard   = $this->getSponsorsByType($allSponsors, SponsorshipType::Sustaining);
    $legacy   = $this->getSponsorsByType($allSponsors, SponsorshipType::Legacy_Sustaining);

    $this->createBanner($standard, $legacy, 'SUSTAINING SPONSORS', 'sustaining_sponsors', 60, 80);

    // Email results
    $mailer = new Mailer(
      tomail: ['news@clawinfo.org'],
      toname: ['Webmaster'],
      fromname: 'CLAW',
      frommail: 'nobody@clawinfo.org',
      subject: 'Updated sponsor banners for eblasts',
      attachments: [
        '/images/0_static_graphics/master_sponsors.png',
        '/images/0_static_graphics/master_sponsors.txt',
        '/images/0_static_graphics/sustaining_sponsors.png',
        '/images/0_static_graphics/sustaining_sponsors.txt',
      ],
    );

    $mailer->appendToMessage('Sponsors graphics for eblasts have been updated. Please see attached files for the updated banners.');

    $send = $mailer->Send();
    if ($send !== true) {
      Factory::getApplication()->enqueueMessage('Error sending email', 'error');
    } else {
      Factory::getApplication()->enqueueMessage('Notification Sent');
    }
  }

  private function createBanner(
    SponsorArray $standard,
    SponsorArray $legacy,
    string $title,
    string $basename,
    int $minSize,
    int $maxSize
  ) {

    $imageWidth = 600;
    $titleHeight = 20;
    $origin_y = $titleHeight;

    // determine if maxWidth needs to be reduced
    //$totalCells = $legacy_top ? $standard->count + $legacy->count : $standard->count;
    $totalCells = count($standard) + count($legacy) * SponsorsModel::LEGACY_WIDTH;

    $remainingCells = $totalCells;

    if (0 == $totalCells) return;

    $rows = $size = 0;

    while ($size < $minSize) {
      $rows++;
      $size = floor($imageWidth / $totalCells * $rows);
    }

    $cellsPerRow = ceil($totalCells / $rows);

    if (count($legacy) > 0) {
      $cellsPerRow += $cellsPerRow % SponsorsModel::LEGACY_WIDTH;
      $size = floor($imageWidth / $cellsPerRow);
    } else if ($size * $cellsPerRow > $imageWidth) {
      //$size = floor($imageWidth / $cellsPerRow);
      $cellsPerRow++;
      $size = floor($imageWidth / $cellsPerRow);
    }

    if ($size > $maxSize) $size = $maxSize;

    // Do the legacy sponsors fill the first n rows?
    // Need to guarantee they fit

    // if ( $size * $legacy->count > $imageWidth )
    // {
    //     $size = $imageWidth / $legacy->count / 2;
    // }

    //if ( $legacy_top ) $rows++;

    $imageHeight = $titleHeight + $rows * $size;
    $origin_x = floor(($imageWidth - $size * $cellsPerRow) / 2);

    $im = @imagecreatetruecolor($imageWidth, $imageHeight);
    imagesavealpha($im, true);
    imagealphablending($im, true);

    $white = imagecolorallocate($im, 255, 255, 255);
    $red = imagecolorallocate($im, 220, 53, 69);

    imagefill($im, 0, 0, $white);
    $imgmap = $this->initImgMap($title, $basename);

    // Heading
    // TODO: There's the potential that there are enough legacy sponsors to exceed the
    // row width, but that's currently not handled

    $font = JPATH_ROOT . '/media/com_claw/fonts/RobotoSlab-Bold.ttf';

    imagefilledrectangle($im, $origin_x, 0, $origin_x + $size * $cellsPerRow - 1, $titleHeight - 1, $red);
    $sizeinfo = imagettfbbox(12, 0, $font, $title);
    $fw = $sizeinfo[2] - $sizeinfo[0];
    $fh = $sizeinfo[1] - $sizeinfo[7];
    imagettftext($im, 12, 0, ($imageWidth - $fw) / 2, $titleHeight - ($titleHeight - $fh) / 2 - 1, $white, $font, $title);

    $workingSponsorsIndex = 0;

    $workingSponsors = [...$legacy, ...$standard];

    $sponsor = reset($workingSponsors);

    for ($row = 0; $row < $rows; $row++) {
      $cellIndex = 0;

      while ($sponsor !== false && $origin_x + $size - 1 < $imageWidth && $remainingCells > 0 && $cellIndex < $cellsPerRow) {
        $relativeWidth = $workingSponsorsIndex < count($legacy) ? SponsorsModel::LEGACY_WIDTH : 1;

        $workingSponsorsIndex++;
        $remainingCells -= $relativeWidth;

        $map = $this->addLogo($im, $sponsor, $size, $origin_x, $origin_y);
        $imgmap .= $map . "\n";

        $origin_x += $size * $relativeWidth;
        $cellIndex += $relativeWidth;
        $sponsor = next($workingSponsors);
      }


      // Update origins for next row
      if ($remainingCells < $cellsPerRow) {
        $origin_x = round(($imageWidth - $remainingCells * $size) / 2);
      } else {
        $origin_x = round(($imageWidth - $size * $cellsPerRow) / 2);
      }

      $origin_y += $size;
    }

    imagejpeg($im, JPATH_ROOT . '/images/0_static_graphics/' . $basename . '.jpg', 90);
    imagepng($im, JPATH_ROOT . '/images/0_static_graphics/' . $basename . '.png', 9);
    imagedestroy($im);

    $imgmap = $imgmap . '</map></body></html>';

    $file = fopen(JPATH_ROOT . '/images/0_static_graphics/' . $basename . '.txt', 'w+');
    fwrite($file, $imgmap);
    fclose($file);
  }

  private function initImgMap(string $title, string $basename)
  {
    $mapname = preg_replace('/\W/', '', strtolower($basename));
    $s = <<<HTML
<html>
<head></head>
<body>
<img src="$basename.png" alt="$title" usemap="#$mapname">
<map name="$mapname">
HTML;

    return $s;
  }

  private function addLogo(\GdImage $im, Sponsor $sponsor, int $size, int $ox, int $oy): bool|string
  {
    $img_map = "";

    // Add logo
    $logo = JPATH_ROOT . '/' . explode('#', $sponsor->logo_large)[0];
    if (!file_exists($logo)) {
      return false;
    }

    if (str_ends_with($logo, '.png')) {
      $logoImage = @imagecreatefrompng($logo);
    } else {
      $logoImage = @imagecreatefromjpeg($logo);
    }

    $sw = imagesx($logoImage);
    $sh = imagesy($logoImage);

    $relativeWidth = $sponsor->type == SponsorshipType::Legacy_Master || $sponsor->type == SponsorshipType::Legacy_Sustaining ? 2 : 1;

    $scaled = imagecreatetruecolor($size * $relativeWidth, $size);
    imagecopyresampled($scaled, $logoImage, 0, 0, 0, 0, $size * $relativeWidth, $size, $sw, $sh);

    imagecopymerge($im, $scaled, $ox, $oy, 0, 0, $size * $relativeWidth, $size, 100);

    if (!empty($sponsor->name) && !empty($sponsor->link)) {
      //error_log("<pre>OX: $ox OY: $oy $logo\n</pre>");
      $img_map = sprintf('<area shape="rect" coords="%d,%d,%d,%d" href="%s" alt="%s">', $ox, $oy, $ox + $size * $relativeWidth - 1, $oy + $size - 1, $sponsor->link, $sponsor->name);
    }

    imagedestroy($logoImage);
    imagedestroy($scaled);

    return $img_map;
  }

  private function getSponsorsByType(SponsorArray $allSponsors, SponsorshipType $sponsorType): SponsorArray
  {
    $sponsors = new SponsorArray();

    /** @var \ClawCorpLib\Lib\Sponsor */
    foreach ($allSponsors as $sponsorItem) {
      if ($sponsorItem->published != EbPublishedState::published) continue;
      if ($sponsorItem->type != $sponsorType) continue;
      $sponsors[$sponsorItem->id] = $sponsorItem;
    }

    return $sponsors;
  }
}
