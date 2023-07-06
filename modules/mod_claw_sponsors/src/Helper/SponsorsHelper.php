<?php

/**
 * @package     CLAW.Sponsors
 * @subpackage  mod_claw_sponsors
 *
 * @copyright   (C) 2023 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Module\Sponsors\Site\Helper;

use ClawCorp\Module\Sponsors\Site\Helper\SponsorsHelper as HelperSponsorsHelper;
use Joomla\CMS\Factory;

use ClawCorpLib\Enums\SponsorshipType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_claw_sponsors
 *
 * @since  1.5
 */
class SponsorsHelper
{
  public static function echoLogos(bool $master = true)
  {
    $db = Factory::getContainer()->get('DatabaseDriver');

    // Index on rows maps to grouping value by sponsor_type radio in elements
    $logos = (object) [
      'legacymaster' => [],
      'legacysustaining' => [],
      'master' => [],
      'sustaining' => []
    ];

    $query = $db->getQuery(true);

    $query->select('*')
      ->from('#__claw_sponsors')
      ->where('published = 1')
      ->order('type, ordering');

    $db->setQuery($query);
    $rows = $db->loadObjectList();

    foreach ($rows as $row) {
      switch ($row->type) {
        case SponsorshipType::Legacy_Master->value:
          $logos->legacymaster[] = $row;
          break;
        case SponsorshipType::Legacy_Sustaining->value:
          $logos->legacysustaining[] = $row;
          break;
        case SponsorshipType::Master->value:
          $logos->master[] = $row;
          break;
        case SponsorshipType::Sustaining->value:
          $logos->sustaining[] = $row;
          break;
      }
    }

    if ($master) :
      SponsorsHelper::writeCss(count($logos->legacymaster) * 2 + count($logos->master), count($logos->legacysustaining) * 2 + count($logos->sustaining));
?>
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="d-flex flex-column mb-2" id="master_sponsors">
              <div class="w-100 justify-content-center">
                <div class="text-white bg-danger master_sponsor_header">
                  <h3 style="text-align:center; font-variant:all-petite-caps; font-size:14pt;">Master Sponsors</h3>
                </div>
              </div>
              <div class="d-flex flex-wrap justify-content-center">
                <?php
                SponsorsHelper::writeSponsors($logos->legacymaster, 'mastersponsor2x');
                SponsorsHelper::writeSponsors($logos->master, 'mastersponsor');
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php
    else :
    ?>
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="d-flex flex-column mb-3 justify-content-center" id="sustaining_sponsors">
              <div class="w-100 justify-content-center">
                <div class="flex-fill text-white bg-danger sponsor_header">
                  <h3 style="text-align:center; font-variant:all-petite-caps; font-size:14pt;">Sustaining Sponsors</h3>
                </div>
              </div>

              <div class="d-flex flex-wrap justify-content-center">
                <?php
                SponsorsHelper::writeSponsors($logos->legacysustaining, 'sustainingsponsor2x');
                SponsorsHelper::writeSponsors($logos->sustaining, 'sustainingsponsor');
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php
    endif;
  }

  public static function writeCss($countm = 0, $counts = 0)
  {
    $masterwidth = 100;
    if ($countm > 10) $masterwidth = round(100 * (10 / $countm));
    $sustainingwidth = $counts;
    ?>
    <style>
      @media screen and (max-width: 576px) {
        .mastersponsor2x {
          width: 100px;
        }

        .mastersponsor {
          width: 50px;
        }

        .sustainingsponsor2x {
          width: 100px;
        }

        .sustainingsponsor {
          width: 50px;
        }
      }

      @media screen and (min-width: 577px) {
        .mastersponsor2x {
          width: 150px;
        }

        .mastersponsor {
          width: 75px;
        }

        .sustainingsponsor2x {
          width: 100px;
        }

        .sustainingsponsor {
          width: 50px;
        }
      }

      @media screen and (min-width: 992px) {
        .mastersponsor2x {
          width: <?php echo $masterwidth * 2 ?>px;
        }

        .mastersponsor {
          width: <?php echo $masterwidth ?>px;
        }

        .sustainingsponsor2x {
          width: calc(2*1080px / <?php echo $sustainingwidth ?>);
          max-width: 150px;
        }

        .sustainingsponsor {
          width: calc(1080px / <?php echo $sustainingwidth ?>);
          max-width: 75px;
        }
      }
    </style>
<?php
  }

  public static function writeJavascript($c)
  {
    $sponsor = count($c->sustaining);
    $sponsord = count($c->legacysustaining);
    $master = count($c->master);
    $masterd = count($c->legacymaster);

    $s = $sponsor + $sponsord * 2;
    $sm = $master + $masterd * 2;

    $javascript = <<< javascript
<script>
jQuery(document).ready(function() {
	updateCss();
});

var rtime;
var timeout = false;
var delta = 200;
jQuery(window).resize(function() {
    rtime = new Date();
    if (timeout === false) {
        timeout = true;
        setTimeout(resizeend, delta);
    }
});

function resizeend() {
    if (new Date() - rtime < delta) {
        setTimeout(resizeend, delta);
    } else {
        timeout = false;
        updateCss();
    }               
}

function headingWidth(m,w,width,doubles,s)
{
	var heading_width = 0;
	
	if ( m == 1 )
	{
		heading_width = s * width;
	}
	else
	{
		while ( heading_width < w )
		{
			if ( doubles > 1 && heading_width + 2 * width > w ) break;

			if ( doubles > 0 )
			{
				heading_width = heading_width + 2 * width;
				s = s - 2;
				doubles--;
				
				continue;
			}

			if ( s > 1 && heading_width + width > w ) break;
			
			if ( s > 0 && heading_width + width < w)
			{
				heading_width = heading_width + width;
				s--;
				continue;
			}
			else
			{
				break;
			}
			
			if ( doubles == 0 && s == 0 ) break;
		}
	}
	
	return heading_width;
}

/* 
 * m := rows
 * w := div width
 * width := individual logo width
 * doubles := count of doubles
 * s := count of singles
 */

function updateCss()
{
	var w = document.getElementById("sustaining_sponsors").offsetWidth;
	var s = $s;
	var doubles = $sponsord;
	
	var m = 1;
	while ( m * w / s < 50 && ((m+1)*w/s) < 75 ) m++;
	while ( s/m != Math.floor(s/m) ) s++;
	var width = Math.floor(m * w / s );
	var heading_width = headingWidth(m,w,width,doubles,s);
	jQuery(".sponsor_header").css("width", heading_width + 'px');
	jQuery(".sponsor").css("width", width + 'px');
	jQuery(".sponsor2x").css("width", 2 * width + 'px');
	
	var sponsor_width = width;
	
	w = document.getElementById("master_sponsors").offsetWidth;
	s = $sm;
	doubles = $masterd;
	
	var target_width = sponsor_width *2 > 100 ? 100 : sponsor_width * 2;

	m = 1;
	while ( m * w / s < target_width*0.75 && ((m+1) * w / s) < target_width ) m++;
	while ( s/m != Math.floor(s/m) ) s++;
	
	width = Math.floor(m * w / s );
	
	// Want master sponsors larger
	if ( width < sponsor_width *1.25 )
	{
		m++;
		width = Math.floor(m * w / s  );
		if ( width > target_width ) width=Math.floor(sponsor_width*1.25);
	}

	if ( width > 100 ) width=100;
	
	heading_width = headingWidth(m,w,width,doubles,s);
	
	jQuery(".master_sponsor_header").css("width", heading_width + 'px');
	jQuery(".master").css("width", width + 'px');
	jQuery(".master2x").css("width", 2 * width + 'px');
}
</script>

<style>
.sponsor {
}
</style>
javascript;

    echo SponsorsHelper::minify_js($javascript);
  }

  public static function writeSponsors($sponsors, $class)
  {
    foreach ($sponsors as $row) {
      $sponsor = $row->name;
      $logo = $row->logo_small;
      $url = $row->link;

      if (substr_count($class, '2x') > 0) {
      }

      echo HelperSponsorsHelper::getImageTagHome($logo, $class, $sponsor, $url);
    }
  }

  public static function getImageTagHome($img, $class, $name, $url)
  {
    $endurl = '';

    if (!empty($url)) {
      $url = "<a href=\"$url\" target=\"_blank\" rel=\"noopener\">";
      $endurl = '</a>';
    }

    $tag = "<div class=\"${class}\">$url<img src=\"$img\" alt=\"$name\" title=\"$name\" />$endurl</div>";

    return $tag;
  }

  // JavaScript Minifier
  // Source: https://gist.github.com/Rodrigo54/93169db48194d470188f
  public static function minify_js($input)
  {
    if (trim($input) === "") return $input;
    return preg_replace(
      array(
        // Remove comment(s)
        '#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',
        // Remove white-space(s) outside the string and regex
        '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',
        // Remove the last semicolon
        '#;+\}#',
        // Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`
        '#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',
        // --ibid. From `foo['bar']` to `foo.bar`
        '#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i'
      ),
      array(
        '$1',
        '$1$2',
        '}',
        '$1$3',
        '$1.$3'
      ),
      $input
    );
  }
}
