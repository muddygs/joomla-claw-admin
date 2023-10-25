<?php

namespace ClawCorpLib\Helpers;

use ClawCorpLib\Lib\Aliases;
use Joomla\CMS\User\UserHelper;

class Bootstrap
{
  public static $tabGuids = [];

  public static function getSponsorTag($sponsorImg, $sponsorName, $sponsorLink)
  {
    $tag = '';

    $link = empty($sponsorLink) ? '' : $sponsorLink;

    if (!empty($link)) {
      $tag = "<a href=\"$link\" alt=\"$sponsorName\" title=\"$sponsorName\" target=\"_blank\">";
    }

    $img = Aliases::sponsorIconDir() . $sponsorImg;
    $tag = $tag . "<img src=\"$img\" class=\"img-fluid mx-auto\"/>";
    if (!empty($link)) {
      $tag = $tag . '</a>';
    }

    return $tag;
  }

  /**
   * Writes out HTML for pilled tabs in Bootstrap 5
   * @return Nothing
   */
  public static function writePillTabs(array $tabTitles, array $tabContent, string $activeTab = '')
  {
    $guid = Bootstrap::writePillTabList($tabTitles, $activeTab);
    Bootstrap::writePillTabContent($guid, $tabTitles, $tabContent, $activeTab);
  }

  public static function writePillTabList(array $tabTitles, string $activeTab = ''): string
  {
    $guid = UserHelper::genRandomPassword(8);
    if ($activeTab == '') $activeTab = $tabTitles[0];
?>
    <ul class="nav nav-pills nav-fill mb-3" id="pills-tab-<?php echo $guid ?>" role="tablist">
      <?php
      foreach ($tabTitles as $title) {
        $active = $title == $activeTab ? 'active' : '';
        $aria = $title == $activeTab ? 'true' : 'false';
        $tabName = strtolower($title);
        $tabName = preg_replace("/[^\w]/", '', $tabName);
      ?>
        <li class="nav-item" role="presentation">
          <button class="nav-link <?php echo $active ?>" id="pills-<?php echo $tabName ?>-tab" data-bs-toggle="pill" data-bs-target="#pills-<?php echo $tabName ?>" role="tab" aria-controls="pills-<?php echo $tabName ?>" aria-selected="<?php echo $aria ?>"><?php echo $title ?></button>
        </li>
      <?php
      }
      ?>
    </ul>
    <?php
    return $guid;
  }

  public static function writePillTabContent(string $guid, array $tabTitles, array $tabContent, string $activeTab = '')
  {
    if ($activeTab == '') $activeTab = $tabTitles[0];

    if (!array_key_exists($guid, Bootstrap::$tabGuids)) {
    ?>
      <div class="tab-content" id="pills-tab-<?php echo $guid ?>Content">
      <?php
    }

    reset($tabContent);

    foreach ($tabTitles as $title) {
      $active = $title == $activeTab ? 'show active' : '';
      $tabName = strtolower($title);
      $tabName = preg_replace("/[^\w]/", '', $tabName);

      ?>
        <div class="tab-pane fade <?php echo $active ?>" id="pills-<?php echo $tabName ?>" role="tabpanel" aria-labelledby="pills-<?php echo $tabName ?>-tab">
          <?php
          echo current($tabContent);
          next($tabContent);
          ?>
        </div>
      <?php
    }

    if (!array_key_exists($guid, Bootstrap::$tabGuids)) {
      Bootstrap::$tabGuids[$guid] = 0;
      ?>
      </div>
    <?php
    }
  }

  /**
   * Generates HTML based on Bootstrap 5 flex. Input is array with icon and content.
   */
  public static function writeGrid(array $content, array $tags = [], bool $asString = false)
  {
    $result = '<div class="container px-2 py-2" id="icon-grid-vip">
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-2 px-4 py-2 justify-content-center">';

    $tagSize = count($tags);

    foreach ($content as $icon => $content) {
      $result .= '<div class="col d-flex align-items-start">';
      $result .= '<div class="flex-grow-0"><span style="color:#ffae00; width:2.5em" class="py-2 py-md-0 text-center fa fa-2x fa-' . $icon . '"></span></div>';
      $result .= '<div class="py-2 py-md-0">';
      foreach ($content as $i => $t) {
        if ($i < $tagSize) $result .= $tags[$i][0];
        $result .= $t;
        if ($i < $tagSize) $result .= $tags[$i][1];
      }
      $result .= '</div>';
      $result .= '</div>';
    }

    $result .= '</div></div>';

    if ($asString) return $result;
    echo $result;
  }

  public static function rawHeader(array $js = [], array $css = [])
  {
    $ts = 'ts=' . time();
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
      <meta charset=UTF-8>
      <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
      <meta http-equiv="Pragma" content="no-cache" />
      <meta http-equiv="Expires" content="0" />
      <title>READY TO PRINT</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <?php
      foreach ( $js AS $j ) {
        echo "<script src=\"$j?$ts\"></script>\n";
      }
    ?>
      <link href="/templates/shaper_helixultimate/css/font-awesome.min.css" rel="stylesheet" />
      <?php
      foreach ( $css AS $c ) {
        echo "<link href=\"$c?$ts\" rel=\"stylesheet\" />\n";
      }
    ?>
    </head>

    <body>
  <?php
  }

  public static function rawFooter()
  {
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    </body>
    </html>
    <?php
  }
}
