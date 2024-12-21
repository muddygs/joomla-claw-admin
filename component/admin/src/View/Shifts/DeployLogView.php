<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\View\Shifts;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

// Dump shifts deploy log to bootstrap table
class DeployLogView extends BaseHtmlView
{
  public array $input;

  function display($tpl = null)
  {
    # TODO: put back button here

    $headings = ['Event ID', 'Title', 'Start', 'End', 'Need', 'Weight'];

    if (!count($this->logs)):
?>
      <h2>No events deployed</h2>
    <?php
      return;
    endif;

    ?>
    <table class="table table-bordered">
      <thead class="table-dark">
        <?php
        echo '<th>' . implode('</th> <th>', $headings) . '</th>';
        ?>
      </thead>
      <tbody>
        <?php
        foreach ($this->logs as $row):
          echo '<tr><td>' . implode('</td><td>', $row) . '</td></tr>';
        endforeach;
        ?>
      </tbody>
    </table>
<?php
  }
}
