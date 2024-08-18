<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


namespace ClawCorp\Component\Claw\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;
use ClawCorpLib\Traits\Controller;
use ClawCorpLib\Lib\EventInfo;

/**
 * 
 * Vendors list controller class.
 *
 */
class VendorsController extends AdminController
{
  use Controller;

  public function __construct(
    $config = [],
    MVCFactoryInterface $factory = null,
    ?CMSApplication $app = null,
    ?Input $input = null,
  ) {
    parent::__construct($config, $factory, $app, $input);

    $this->controllerSetup();
  }

  public function reorder()
  {
    // Check for request forgeries.
    $this->checkToken();

    $filter = $this->app->getInput()->get('filter', '', 'string');
    $event = array_key_exists('event', $filter) ? $filter['event'] : '';

    if (!EventInfo::isValidEventAlias($event)) {
      $this->setRedirect(
        'index.php?option=com_claw&view=speeddatinginfos',
        'Event selection not valid for reordering.',
        'error'
      );
      return false;
    }

    /** @var \ClawCorp\Component\Claw\Administrator\Model\SponsorshipModel $model */
    $model = $this->getModel();
    $model->reorder($event);

    $this->app->enqueueMessage('Vendors for ' . $event . 'have been reordered.', 'info');
    return true;
  }
}
