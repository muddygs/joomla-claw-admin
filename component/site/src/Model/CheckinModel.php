<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\Model;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\JwtStates;
use ClawCorpLib\Lib\Jwtwrapper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;

/**
 * Methods to handle public class listing.
 */
class CheckinModel extends BaseDatabaseModel
{
  public function JwtstateInit($email, $url)
  {
    $nonce = Jwtwrapper::getNonce();
    $email = trim($email);

    $jsonValues = [
      'state' => 'error',
      'token' => ''
    ];

    if ( filter_var($email, FILTER_VALIDATE_EMAIL) && array_key_exists($url, Jwtwrapper::jwt_token_pages)) {
      $jwt = new Jwtwrapper($nonce);
			$result = $jwt->initTokenRequest($email, $nonce, $url);
			$jsonValues['state'] = $result ? JwtStates::init->value : JwtStates::error->value;
		}

    return json_encode($jsonValues);
  }

  public function JwtstateState($url): string
  {
    $jsonValues = [];
    $nonce = Jwtwrapper::getNonce();
    $jwt = new Jwtwrapper($nonce);
    list($state, $token) = $jwt->getDatabaseState($nonce, $url);
		$jsonValues['state'] = $state;
		$jsonValues['token'] = $token;

    return json_encode($jsonValues);
  }

  public function JwtConfirm($token): string
  {
    $jsonValues = [
      'state' => 'error',
      'token' => ''
    ];
    
    $nonce = Jwtwrapper::getNonce();
    $jwt = new Jwtwrapper($nonce);
    $payload = $jwt->confirmToken($token, JwtStates::confirm);

    if ($payload != null) {
      if ($payload->iat + 310 > time()) {
        $jwt->updateDatabaseState($payload, JwtStates::issued);
        $jsonValues['state'] = $payload->state;
      }
    }
    $jwt->closeWindow();
    return json_encode($jsonValues);
  }
}