<h1 class="rstpl-title-center text-white">Registration Options for <?= $this->eventConfig->eventInfo->description ?></h1>
<?php


#$this->setLayout($eventLayout);

$eventInfo = $this->eventConfig->eventInfo;

?>
<div class="row">
  <div class="col-lg-8">
    <h1><?= $this->eventDescription ?></h1>
    <?php
    if ($this->coupon != ''):
    ?>
      <p style="margin-bottom:0px !important;">Your Coupon Code: <strong><?= $this->coupon ?></strong></p>
    <?php
    endif;
    ?>
  </div>
  <div class="col-lg-4">
    <div class="d-grid gap-2">
      <a href="/index.php?option=com_eventbooking&view=cart" role="button" class="btn btn-warning btn-lg">
        <span class="fa fa-shopping-cart" aria-hidden="true"></span>&nbsp;Review Cart and Checkout
      </a>
    </div>
  </div>
</div>

<?php
if ($this->mainEvent != null) :
?>
  <p class="text-warning">
    <b>You are already registered. To view all your registrations, click <a href="/account/my-reg">here</a></b> to view My Registrations.
  </p>
<?php
endif;
