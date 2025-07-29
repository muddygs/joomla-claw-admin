<?php

/** @var \Joomla\CMS\WebAsset\WebAssetManager */
$wa = $this->app->getDocument()->getWebAssetManager();
$wa->useScript('com_claw.toast');
?>

<div class="position-fixed top-50 start-50 translate-middle p-3" style="z-index: 11">
  <div id="liveToast" class="toast rounded-pill" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header rounded-pill">
      <p style="line-height:1rem;" class="small m-1">Event added to cart.<br>Click Cart Button (above) to check out.</p>
      <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>
