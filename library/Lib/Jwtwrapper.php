<?php
namespace ClawCorpLib\Lib;
\defined('_JEXEC') or die('Restricted access');


use Joomla\CMS\Factory;
use Joomla\CMS\User\UserHelper;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Enums\JwtStates;
use Exception;
use UnexpectedValueException;

require_once(JPATH_LIBRARIES . '/claw/External/jwt/vendor/autoload.php');

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use Joomla\CMS\Uri\Uri;

class postData
{
  var string $token = '';
  var $decoded;
  var $post = [];

  public function __construct(string $token)
  {
    $this->token = $token;
  }

  public function addPost($key, $value): void
  {
    $this->post[$key] = $value;
  }

  public function getPost($key): string
  {
    if (!array_key_exists($key, $this->post)) die(__FILE__ . ": Unknown key: $key");
    return $this->post[$key];
  }
}


class Jwtwrapper
{
  private array $payload = [];

  const jwt_token_pages =
  [
    'volunteer-roll-call' => [
      'exp' => 5 * 3600,
      'group' => 'VolunteerCoord',
      'description' => 'Volunteer Roll Call',
      'icon' => 'check'
    ],
    'badge-checkin' => [
      'exp' => 5 * 3600,
      'group' => 'Registration',
      'description' => 'Badge Checkin',
      'icon' => 'id-badge'
    ],
    'badge-print' => [
      'exp' => 5 * 3600,
      'group' => 'Registration',
      'description' => 'Badge Print',
      'icon' => 'print'
    ],
    'meals-checkin' => [
      'exp' => 2 * 3600,
      'group' => 'Registration',
      'description' => 'Meals Checkin',
      'icon' => 'utensils'
    ]
  ];


  public function __construct(
    private string $nonce
  ) {
    # nonce cannot resolve empty
    if (trim($this->nonce) == '') die('Invalid nonce name');
    $payload['nonce'] = $this->nonce;
  }

  public static function getNonce(): string
  {
    /** @var \Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();
    $session = $app->getSession();
    $sessionId = $session->getId();
    return $sessionId;
  }

  /**
   * Update class to match a token; token must have state="issued", matching nonce, and matching subject.
   * @param string $token Token string
   * @return object Payload object or null
   */

  public function loadFromToken(string $token): ?object
  {
    try {
      $payload = JwtWrapper::decodeUnverified($token);
    } catch (Exception $e) {
      return null;
    }

    foreach ($payload as $k => $v) $this->setPayloadValue($k, $v);

    $secret = '';
    if (property_exists($payload, 'nonce')) {
      $this->nonce = $payload->nonce;
      $secret = $this->getSecret();
    }

    if ('' == $secret) return null;

    // Fully decode token, guarantee payload object keys
    $decoded = Jwtwrapper::decodeVerified($token, $secret);

    if (null == $decoded) return null;

    $db = Factory::getDbo();
    $query = $db->getQuery(true);
    $query->select($db->qn('state'))
      ->from('#__claw_jwt')
      ->where('nonce = ' . $db->q($payload->nonce))
      ->where('secret=' . $db->q($secret))
      ->where('subject=' . $db->q($decoded->subject))
      ->where('state=' . $db->q(JwtStates::issued->value));
    $db->setQuery($query);
    $state = $db->loadResult();

    if ($state == null) return null;
    $decoded->state = $state;
    return $decoded;
  }

  /**
   * Returns the payload without verifying signature
   * 
   * @param string $token The JWT token
   * 
   * @return object Payload object
   */
  public static function decodeUnverified(string $token): object
  {
    $tks = \explode('.', $token);
    if (\count($tks) != 3) {
      throw new UnexpectedValueException('Wrong number of segments');
    }
    list($headb64, $bodyb64, $cryptob64) = $tks;
    if (null === ($header = \Firebase\JWT\JWT::jsonDecode(\Firebase\JWT\JWT::urlsafeB64Decode($headb64)))) {
      throw new UnexpectedValueException('Invalid header encoding');
    }
    if (null === $payload = \Firebase\JWT\JWT::jsonDecode(\Firebase\JWT\JWT::urlsafeB64Decode($bodyb64))) {
      throw new UnexpectedValueException('Invalid claims encoding');
    }

    return $payload;
  }

  /**
   * Decode a JWT token with a given secret using HS384
   * 
   * @param string $token The JSON web token
   * @param string $secret The key to the token
   * 
   * @return object JWT payload or null (on error)
   */
  public static function decodeVerified(string $token, string $secret): ?object
  {
    $payload = null;

    try {
      $payload = JWT::decode($token, new Key($secret, 'HS384'));
    } catch (Exception $e) {
      return null;
    }

    // Verify the required payload keys exist
    foreach (['email', 'iat', 'exp', 'nonce', 'state'] as $k) {
      if (!property_exists($payload, $k)) return null;
    }

    return $payload;
  }

  // If secret isn't set, initialize
  // @return 
  public function getSecret(bool $allowInit = false): string
  {
    $db = Factory::getDbo();

    $query = $db->getQuery(true);
    $query->select('*')
      ->from('#__claw_jwt')
      ->where('nonce = :nonce')
      ->where('subject = :subject')
      ->bind(':nonce', $this->nonce)
      ->bind(':subject', $this->payload['subject']);
    $db->setQuery($query);
    $results = $db->loadObjectList();

    if (sizeof($results) == 0 && !$allowInit) return '';
    if (!array_key_exists('email', $this->payload) || $this->payload['email'] == '') return '';

    $secret = '';

    foreach ($results as $result) {
      if ($result->state == JwtStates::revoked->value) continue;
      $secret = $result->secret;
    }

    // Init if necessary
    if ($secret == '' && $allowInit) {
      $query = $db->getQuery(true);
      $query->delete('#__claw_jwt')
        ->where('nonce = :nonce')
        ->where('subject = :subject')
        ->bind(':nonce', $this->nonce)
        ->bind(':subject', $this->payload['subject']);
      $db->setQuery($query);
      $result = $db->execute();

      $secret = UserHelper::genRandomPassword(50);

      $insert = (object)[
        'id' => 0,
        'iat' => 0,
        'exp' => 0,
        'secret' => $secret,
        'email' => $this->payload['email'],
        'nonce' => $this->nonce,
        'subject' => $this->payload['subject']
      ];
      $result = $db->insertObject('#__claw_jwt', $insert);
      if (!$result) die('Database error creating user secret');
    }

    return $secret;
  }

  /**
   * Sets the key/value pair up in the payload
   */
  public function setPayloadValue(string $key, string $value): void
  {
    $this->payload[$key] = $value;
  }

  /**
   * From the columns of the jwt database table, create a fully issued token
   * @param object Database row object
   * @return string Issued jwt token
   */
  public function issueToken(object $row): string
  {
    $this->setPayloadValue('iat', intval($row->iat));
    $this->setPayloadValue('exp', intval($row->exp));
    $this->setPayloadValue('email', $row->email);
    $this->setPayloadValue('nonce', $row->nonce);
    $this->setPayloadValue('state', JwtStates::issued->value);
    $this->setPayloadValue('subject', $row->subject);
    $confirmToken = JWT::encode($this->payload, $row->secret, 'HS384');
    return $confirmToken;
  }

  /**
   * Sets up for CONFIRM and REJECTED tokens for the email to the user
   * @param string $secret Encryption key
   * @param string $nonce Identifier
   * @return array Two tokens: confirm and revoke (in that order)
   */
  public function initEmailTokens(string $secret, string $nonce, string $subject): array
  {
    $db = Factory::getDbo();

    // $page = sessionGet('jwt_token_page');

    if (!array_key_exists($subject, Jwtwrapper::jwt_token_pages)) {
      die(__FILE__ . ': Invalid page requested: ' . $subject);
    }

    $validSeconds = Jwtwrapper::jwt_token_pages[$subject]['exp'];

    if ($validSeconds < 1 || $validSeconds > 86400) {
      die('Validity time must be between 1 and 86,400 seconds');
    }

    $iat = time();
    $exp = time() + $validSeconds;

    $this->payload['iat'] = $iat;
    $this->payload['exp'] = $exp;
    $this->payload['nonce'] = $nonce;
    $this->payload['subject'] = $subject;

    $this->payload['state'] = JwtStates::confirm->value;
    $confirmToken = JWT::encode($this->payload, $secret, 'HS384');

    $this->payload['state'] = JwtStates::revoked->value;
    $revokeToken = JWT::encode($this->payload, $secret, 'HS384');

    $state = JwtStates::init->value;
    $query = $db->getQuery(true);
    $query->update('#__claw_jwt')
      ->set('state=:state')->bind(':state', $state)
      ->set('iat=:iat')->bind(':iat', $iat)
      ->set('exp=:exp')->bind(':exp', $exp)
      ->where('nonce=:nonce')->bind(':nonce', $nonce)
      ->where('subject=:subject')->bind(':subject', $subject);
    $db->setQuery($query);
    $db->execute();

    return [$confirmToken, $revokeToken];
  }

  public static function redirectOnInvalidToken(string $page, string $token): void
  {
    /** @var Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();

    if ('' == $token) {
      $app->redirect('/link');
    }

    $jwt = new Jwtwrapper('stub');
    $decoded = $jwt->loadFromToken($token);

    if (null == $decoded || $decoded->subject != $page ) {
      $app->redirect('/link');
    }
  }

  public static function updateDatabaseState(object $payload, JwtStates $state)
  {
    $db = Factory::getDbo();

    // The only values permitted by the database's definition:
    if ($state != JwtStates::issued && $state != JwtStates::revoked) return;
    if (!property_exists($payload, 'email') || !property_exists($payload, 'nonce') || !property_exists($payload, 'subject')) return;

    $email = $payload->email;
    $nonce = $payload->nonce;
    $subject = $payload->subject;

    if ('' == $email || '' == $nonce || '' == $subject) return;

    $s = $state->value;

    $query = $db->getQuery(true);
    $query->update('#__claw_jwt')
      ->set('state=:state')->bind(':state', $s)
      ->where('nonce=:nonce')->bind(':nonce', $nonce)
      ->where('email=:email')->bind(':email', $email)
      ->where('subject=:subject')->bind(':subject', $subject);
    $db->setQuery($query);
    $db->execute();

    // Cleanup old tokens
    $query = $db->getQuery(true);
    $query->delete('#__claw_jwt')
      ->where('exp < :exp')->bind(':exp', time());
    $db->setQuery($query);
    $db->execute();
  }

  public static function getDatabaseState(string $nonce, string $subject): array
  {
    $db = Factory::getDbo();

    $query = $db->getQuery(true);
    $query->select('*')
      ->from('#__claw_jwt')
      ->where('nonce = :nonce')
      ->where('subject = :subject')
      ->bind(':nonce', $nonce)
      ->bind(':subject', $subject);

    $db->setQuery($query);
    $rows = $db->loadObjectList();

    $result = ['error', ''];

    foreach ($rows as $row) {
      if ($row->state != JwtStates::issued->value) {
        $result =  [$row->state, ''];
        continue;
      } else {
        // Must be issued, so generate new token to return to the client
        $jwt = new jwtwrapper($row->nonce);
        $token = $jwt->issueToken($row);
        $result = [$row->state, $token];
        break;
      }
    }

    return $result;
  }

  public static function setDatabaseState(int $rowId, JwtStates $state): bool
  {
    $db = Factory::getDbo();

    $query = $db->getQuery(true);
    $s = $state->value;
    
    $query->update('#__claw_jwt')
      ->set('state=:state')->bind(':state', $s)
      ->where('id=:id')->bind(':id', $rowId);
    $db->setQuery($query);
    return $db->execute();
  }

  /**
   * Retrieves array of all non-expired JWT tracking rows in the database
   * @return array Array of db objects
   */
  public static function getJwtRecords(): array
  {
    $db = Factory::getDbo();
    $time = time();

    $revoked = JwtStates::revoked->value;

    // TODO: used multiple times, so make a function
    // Cleanup old tokens
    $query = $db->getQuery(true);
    $query->delete('#__claw_jwt')
      ->where('exp < :exp')->bind(':exp', $time);
    $db->setQuery($query);
    $db->execute();

    $query = $db->getQuery(true);
    $query->select('*')
      ->from('#__claw_jwt')
      ->where('state != :state')->bind(':state', $revoked);
    $db->setQuery($query);
    $results = $db->loadObjectList();

    return $results != null ? $results : [];
  }

  /**
   * Does all the work to initialize a token. It is not authorized yet, but the
   * links to authorize or reject are created and emailed to the user (assuming the
   * person has the correct associated group assigned to the page).
   * @param string $email Email address of the user (and thus Joomla account) NOTE: Joomla requires unique email per account
   * @param string $nonce Some indicator of ownership, usually Joomla token name
   * @param string $subject Some subject within jwt_token_pages::page
   * @return bool FALSE on error
   */
  function initTokenRequest(string $email, string $nonce, string $subject): bool
  {
    $db = Factory::getDbo();

    if (!array_key_exists($subject, Jwtwrapper::jwt_token_pages)) return false;
    if ('' == $email || '' == $nonce) return false;

    // Delete existing if another init for this nonce (Joomla session) occurs
    $query = $db->getQuery(true);
    $query->delete('#__claw_jwt')
      ->where('nonce=:nonce')->bind(':nonce', $nonce)
      ->where('subject=:subject')->bind(':subject', $subject);
    $db->setQuery($query);
    $db->execute();

    $id = Helpers::getUserIdByEmail($db, $email);
    if (0 == $id) return false;

    $groups = Helpers::getUserGroupsByName($id);
    if (count($groups) == 0) return false;

    if (!array_key_exists('Super Users', $groups) && !array_key_exists(Jwtwrapper::jwt_token_pages[$subject]['group'], $groups)) return false;

    $jwt = new Jwtwrapper($nonce);

    $jwt->setPayloadValue('uid', $id);
    $jwt->setPayloadValue('state', JwtStates::init->value);
    $jwt->setPayloadValue('email', $email);
    $jwt->setPayloadValue('subject', $subject);

    // Initialize the secret
    $secret = $jwt->getSecret(allowInit: true);

    [$confirmToken, $revokeToken] = $jwt->initEmailTokens($secret, $nonce, $subject);

    if ('' != $secret) {
      Jwtwrapper::emailLink($email, $confirmToken, $revokeToken, 'Registration link for ' . $subject);
    }

    return true;
  }

  public static function confirmToken(string $token, JwtStates $requiredState): ?object
  {
    if (trim($token) == '') return null;

    try {
      $payload = Jwtwrapper::decodeUnverified($token);
    } catch (Exception $e) {
      return null;
    }

    if (!property_exists($payload, 'nonce') || !property_exists($payload, 'email')) return null;

    $jwt = new Jwtwrapper($payload->nonce);
    $jwt->setPayloadValue('email', $payload->email);
    $jwt->setPayloadValue('subject', $payload->subject);
    $secret = $jwt->getSecret();

    if ('' == $secret) return null;

    // Fully decode token
    $decoded = Jwtwrapper::decodeVerified($token, $secret);

    if ($decoded == null) return null;

    // nonce should be the current Joomla session, unless confirming/revoking the token
    // via the email link
    /** @var Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();
    $session = $app->getSession();
    $sessionId = $session->getId();
    if (
      $requiredState != JwtStates::confirm &&
      $requiredState != JwtStates::revoked && $payload->nonce != $sessionId
    ) {
      return null;
    }

    if ($requiredState != JwtStates::confirm && $requiredState != JwtStates::revoked) {
      // read database to update state from there
      $s = $requiredState->value;
      
      $db = Factory::getDbo();
      $query = $db->getQuery(true);
      $query->select($db->qn('state'))
        ->from('#__claw_jwt')
        ->where('nonce = ' . $db->q($payload->nonce))
        ->where('secret=' . $db->q($secret))
        ->where('subject=' . $db->q($decoded->subject))
        ->where('state=' . $db->q($s));
      $db->setQuery($query);
      $state = $db->loadResult();

      if ($state == null) return null;
      $decoded->state = $state;
    }

    if ($requiredState == JwtStates::confirm && $decoded->state != JwtStates::confirm->value) return null;
    if ($requiredState == JwtStates::revoked && $decoded->state != JwtStates::revoked->value) return null;

    return $decoded;
  }

  public static function emailLink(string $email, string $confirmToken, string $revokeToken, string $subject): bool
  {
    // Email results
    $mailer = Factory::getMailer();

    $mailer->setSender(['noreply@clawinfo.org', 'CLAW-NO-REPLY']);
    $mailer->setSubject($subject);
    $mailer->addRecipient($email);
    $mailer->isHtml(true);

    $root = Uri::getInstance();
    $root->setPath('/');

    $confirmLink = $root->root() . "index.php?option=com_claw&view=checkin&format=raw&task=jwtconfirm&token=$confirmToken";
    $revokeLink =  $root->root() . "index.php?option=com_claw&view=checkin&format=raw&task=jwtrevoke&token=$revokeToken";

    $body =  Jwtwrapper::htmlButton($confirmLink, 'Confirm', '#28a745');
    $body .= Jwtwrapper::htmlButton($revokeLink,  'Revoke',  '#dc3545');
    $mailer->setBody($body);

    return $mailer->Send();
  }

  public static function htmlButton(string $link, string $message, string $background): string
  {
    $button = <<< HTML
    <table border="0" cellpadding="0" cellspacing="0" role="presentation"
      style="border-collapse:separate;line-height:100%;width:50%">
    <tr>
      <td align="center" bgcolor="$background" role="presentation"
        style="border:none;border-radius:6px;cursor:auto;padding:11px 20px;background:$background;" valign="middle">
        <a href="$link" style="background:$background;color:#ffffff;font-family:Helvetica, sans-serif;font-size:18px;font-weight:600;line-height:120%;Margin:0;text-decoration:none;text-transform:none;" target="_blank">
          $message
        </a>
      </td>
    </tr>
  </table><br>
  HTML;
    return $button;
  }

  public function closeWindow()
  {
	  echo "<script>window.close();</script>";
  }
}
