<?php

\defined('_JEXEC') or die;

use ClawCorpLib\Enums\JwtStates;
use Joomla\CMS\Factory;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Jwtwrapper;
use Joomla\CMS\HTML\HTMLHelper;

$uri = Helpers::sessionGet('jwt_redirect','');
if (substr($uri, 0, 1) == '/') $uri = substr($uri, 1);

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

  <?php echo HTMLHelper::_('form.token'); ?>

</form>

<div id="msg"></div>

<?php

if ( $this->state->get('user.admin', false)):
  $wa->useScript('com_claw.jwtdashboard');
?>
<h1>JWT Dashboard (Eastern Time: <?php echo date('g:iA', time()) ?>)</h1>

<table class="table table-striped table-dark">
  <thead>
    <tr>
      <th class="col-1">ID</th>
      <th class="col-2">email</th>
      <th class="col-2">subject</th>
      <th class="col-2">Issued</th>
      <th class="col-2">Expires</th>
      <th class="col-3">Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php

    foreach ($this->records as $r) {
      
      $iat = date('g:iA', $r->iat);
      $exp = date('g:iA', $r->exp);

    ?>
      <tr>
        <td><?= $r->id ?></td>
        <td><?= $r->email ?></td>
        <td><?= $r->subject ?></td>
        <td><?= $iat ?></td>
        <td><?= $exp ?></td>
        <td><?= dashboardButtons($r) ?></td>
      </tr>
    <?php
    }

    ?>
  </tbody>
</table>

<?php
endif;

function dashboardButtons($row)
{
  if ($row->state == JwtStates::init->value) {
?>
    <button id="dbrdc<?php echo $row->id ?>" class="btn btn-danger btn-lg mt-2 mb-2" onClick="doConfirm(<?php echo $row->id ?>)">Confirm</button>
  <?php
  }
  ?>
  <button id="dbrdr<?php echo $row->id ?>" class="btn btn-danger btn-lg mt-2 mb-2" onClick="doRevoke(<?php echo $row->id ?>)">Revoke</button>
<?php
}

