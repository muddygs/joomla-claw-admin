<?php
\defined('_JEXEC') or die;

$pathinfo = pathinfo($this->poster);

$thumbname = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . 'thumb_' . $pathinfo['filename'] . '.jpg';

if (file_exists(JPATH_ROOT . '/' . $thumbname)):
?>
  <button id="show-img-<?= $this->id ?>" type="button" class="btn btn-default p-0 align-top" data-bs-toggle="modal" data-bs-target="#modal-<?= $this->id ?>">
    <img src="<?= $thumbname ?>" />
  </button>
  <div id="modal-<?= $this->id ?>" class="modal fade" aria-labelledby="modal-<?= $this->id ?>Label" aria-hidden="true" tabindex="-1" role="dialog">
    <div class="modal-dialog" data-dismiss="modal">
      <div class="modal-content">
        <div class="model-header">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
        </div>
        <div class="modal-body">
          <img src="<?= $this->poster ?>" class="img-responsive" style="width: 100%;">
        </div>
      </div>
    </div>
  </div>
<?php
endif;
