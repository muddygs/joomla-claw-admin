<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\View\Coupongenerator;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

// HTMX response w/embedded HTML (no template)
class HtmxGenerateView extends BaseHtmlView
{
  public array $input;

  function display($tpl = null)
  {
    /** @var \ClawCorp\Component\Claw\Administrator\Model\CoupongeneratorModel */
    $model       = $this->getModel();
    $result = $model->createCoupons($this->input);

    /** @var \Joomla\CMS\Application\CMSApplication $app */
    $app = Factory::getApplication();
    /** @var \Joomla\CMS\Document\HtmlDocument $document */
    $document = $app->getDocument();
    $document->setMimeEncoding('text/html');

    // Just the error and done
    if ($result->error) {
?>
      <p class="text-error"><?= $result->msg ?></p>
    <?php
      return;
    }

    // Otherwise, create output table if no error

    ?>
    <h1>Coupon Results</h1>
    <div class="table-responsive">
      <table class="table table-striped table-bordered">
        <thead>
          <tr>
            <th style="padding:3px;">Name</th>
            <th style="padding:3px;">Discount Code</th>
            <th style="padding:3px;">Email</th>
          </tr>
        </thead>
        <tbody>

          <?php

          foreach ($result->coupons as $coupon) :


          ?>
            <tr>
              <td style="padding:3px;"><?= $coupon->name ?></td>
              <td style="padding:3px;" data-coupon="<?= $coupon->code ?>"><?= $coupon->code ?></td>
              <td style="padding:3px;"><?= $coupon->email ?></td>
            </tr>
          <?php

          endforeach;
          ?>
        </tbody>
      </table>
    </div>
<?php
  }
}

