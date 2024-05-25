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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

// HTMX response w/embedded HTML (no template)
class HtmxPackagesView extends BaseHtmlView
{
  public array $input;

  function display($tpl = null)
  {
		/** @var \ClawCorp\Component\Claw\Administrator\Model\CoupongeneratorModel */
		$model = $this->getModel();
    $options = $model->packageOptions($this->input);

    /** @var \Joomla\CMS\Application\CMSApplication $app */
    $app = Factory::getApplication();
    /** @var \Joomla\CMS\Document\HtmlDocument $document */
    $document = $app->getDocument();
    $document->setMimeEncoding('text/html');

    $optionElements = [];
    $optionElements[] = HTMLHelper::_('select.option', '0', 'Select a Package');

    foreach ( $options AS $id => $title ) {
      $optionElements[] = HTMLHelper::_('select.option', $id, $title);
    }

    echo HTMLHelper::_('select.options', $optionElements, 'value', 'text', '0', true);
  }
}
