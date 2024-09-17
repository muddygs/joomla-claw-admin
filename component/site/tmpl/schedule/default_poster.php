<?php
$json = json_decode($item->poster);
$poster = explode('#', $json->imagefile)[0];

$dirname = dirname($poster);
$basename = basename($poster);
$thumbname = $dirname . DIRECTORY_SEPARATOR . 'thumb_' . $basename;

// Valid file?
if (file_exists($thumbname)):
?>
  <button id="show-img-$id" type="button" class="btn btn-default p-0 align-top" data-bs-toggle="modal" data-bs-target="#modal-$id">
    <img src="<?= $thumbname ?>" />
  </button>
  <div id="modal-$id" class="modal fade" aria-labelledby="modal-{$id}Label" aria-hidden="true" tabindex="-1" role="dialog">
    <div class="modal-dialog" data-dismiss="modal">
      <div class="modal-content">
        <div class="model-header">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
        </div>
        <div class="modal-body">
          <img src="<?= $poster ?>" class="img-responsive" style="width: 100%;">
        </div>
      </div>
    </div>
  </div>
<?php
endif;
