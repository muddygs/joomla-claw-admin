<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Jwtwrapper;

$uri = Helpers::sessionGet('jwt_redirect','');
if (substr($uri, 0, 1) == '/') $uri = substr($uri, 1);

/*
if ( '' == $uri ):
?>
<h1>Authentication Request</h1>
<p class="text-danger">Direct access not permitted. Please use coordinator/manager page directly.</p>
<?php
	return;
endif;
*/

/** @var Joomla\CMS\Application\SiteApplication */
$app = Factory::getApplication();
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('com_claw.jwtlink');
?>
<h1>Authentication Request</h1>
<h3>Please enter your CLAW account email. If you are authorized, a link will be sent to your
email.</h3>

<form action="" method="post" name="Link" id="claw-link-request" class="form-horizontal">

  <div class="input-group mb-3">
    <select name="url" id="url" class="form-select" aria-label="Select checkin area">
      <option selected>Make a selection</option>
      <?php
      foreach ( Jwtwrapper::jwt_token_pages AS $page => $rules ):
        echo '<option value="'.$page.'">'.$rules['description'].'</option>';
      endforeach;
      ?>
    </select>
  </div>

	<label for="email" class="form-label">Enter your coordinator email</label>
  <div class="input-group mb-3">
  	<input type="text" class="form-control" name="email" id="email" aria-label="Email Entry Field">
		<input name="submit" id="submit" type="button" value="Send Validation" class="btn btn-danger" onclick="submitjwtEmail()"/>
  </div>

</form>

<div id="msg"></div>
