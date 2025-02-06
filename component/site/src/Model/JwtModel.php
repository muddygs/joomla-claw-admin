<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\Model;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\JwtStates;
use ClawCorpLib\Lib\Jwtwrapper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Methods to handle public class listing.
 */
class JwtModel extends BaseDatabaseModel
{
  public function JwtstateInit($email, $subject)
  {
    $nonce = Jwtwrapper::getNonce();
    $email = trim($email);

    $jsonValues = [
      'state' => 'error',
      'token' => ''
    ];

    if (filter_var($email, FILTER_VALIDATE_EMAIL) && array_key_exists($subject, Jwtwrapper::jwt_token_pages)) {
      $jwt = new Jwtwrapper($nonce);
      $result = $jwt->initTokenRequest($email, $nonce, $subject);
      $jsonValues['state'] = $result ? JwtStates::init->value : JwtStates::error->value;
    }

    return json_encode($jsonValues);
  }

  public function JwtstateState($subject): array
  {
    $jsonValues = [];
    $nonce = Jwtwrapper::getNonce();
    $jwt = new Jwtwrapper($nonce);
    list($state, $token) = $jwt->getDatabaseState($nonce, $subject);
    $jsonValues['state'] = $state;
    $jsonValues['token'] = $token;

    return $jsonValues;
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

  public function JwtRevoke($token): string
  {
    $jsonValues = [
      'state' => 'error',
      'token' => ''
    ];

    $nonce = Jwtwrapper::getNonce();
    $jwt = new Jwtwrapper($nonce);
    $payload = $jwt->confirmToken($token, JwtStates::revoked);

    if ($payload != null) {
      $jwt->updateDatabaseState($payload, JwtStates::revoked);
      $jsonValues['state'] = $payload->state;
    }
    $jwt->closeWindow();
    return json_encode($jsonValues);
  }

  public function JwtmonValidate(string $token): array
  {
    $result = [
      'time_remaining' => 0,
      'state' => JwtStates::error->value
    ];

    $payload = Jwtwrapper::confirmToken($token, JwtStates::issued);
    if ($payload != null) {
      $exp = intval($payload->exp);
      $remaining = max(0, $exp - time());
      $result['state'] = $payload->state;
      $result['time_remaining'] = $remaining;
    }

    return $result;
  }
}
