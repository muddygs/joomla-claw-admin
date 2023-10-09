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
use ClawCorpLib\Lib\Checkin;
use ClawCorpLib\Lib\Jwtwrapper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;

/**
 * Methods to handle public class listing.
 */
class CheckinModel extends BaseDatabaseModel
{
  public function JwtstateInit($email, $subject)
  {
    $nonce = Jwtwrapper::getNonce();
    $email = trim($email);

    $jsonValues = [
      'state' => 'error',
      'token' => ''
    ];

    if ( filter_var($email, FILTER_VALIDATE_EMAIL) && array_key_exists($subject, Jwtwrapper::jwt_token_pages)) {
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

    if ( $payload != null ) {
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
		if ($payload != null ) {
			$exp = intval($payload->exp);
			$remaining = max(0, $exp-time());
			$result['state'] = $payload->state;
			$result['time_remaining'] = $remaining;
    }

    return $result;
  }

  public function JwtSearch(string $token, string $search, string $page)
  {
    Jwtwrapper::redirectOnInvalidToken(page: $page, token: $token);

    $searchResults = Checkin::search($search, $page);
		header('Content-Type: application/json');
		return $searchResults;
  }

  public function JwtValue(string $token, string $registration_code, string $page)
  {
    Jwtwrapper::redirectOnInvalidToken(page: $page, token: $token);

    $checkinRecord = new Checkin($registration_code);
		$r = $checkinRecord->r->toObject();
    return $r;
  }

  public function JwtCheckin(string $token, string $registration_code, string $page)
  {
    Jwtwrapper::redirectOnInvalidToken(page: $page, token: $token);

    $checkinRecord = new Checkin($registration_code);
    $checkinRecord->doCheckin();

    $r = [ 'result' => '1'];
    return $r;
  }

  public function JwtGetCount(string $token)
  {
    Jwtwrapper::redirectOnInvalidToken(page: 'badge-print', token: $token);
    return Checkin::getUnprintedBadgeCount();
  }

  public function JwtMealCheckin(string $token, string $registration_code, string $meal)
  {
    Jwtwrapper::redirectOnInvalidToken(page: 'meals-checkin', token: $token);

    $checkinRecord = new Checkin($registration_code);

    if ( !$checkinRecord->isValid ) {
      return [
        'state' => 'error',
        'message' => 'Invalid registration code'
      ];
    }

    $msg = $checkinRecord->doMealCheckin($meal);
    return $msg;
  }

}