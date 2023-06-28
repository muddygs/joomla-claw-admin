<?php
defined('_JEXEC') or die('Restricted access');

namespace ClawCorpLib\Lib;

use Joomla\CMS\Factory;
use Joomla\CMS\User\UserHelper;

use ClawCorpLib\Helpers\Bootstrap;
use ClawCorpLib\Helpers\Helpers;
use Exception;
use UnexpectedValueException;

require_once(JPATH_LIBRARIES . '/claw/External/jwt/vendor/autoload.php');

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

enum jwt_token_state : string {
	case init = 'new';
	case issued = 'issued';
	case revoked = 'revoked';
	case expired = 'expired';
	case confirm = 'confirm';
	case error = 'error';
}

/**
 * Key is link URL
 * @package 
 */
abstract class jwt_token_pages {
	const page = 
	[
		'volunteer-roll-call' => [
			'exp' => 5*3600,
			'group' => 'VolunteerCoord',
			'description' => 'Volunteer Roll Call',
			'icon' => 'check'],
		'badge-checkin' => [
			'exp' => 5*3600,
			'group' => 'Registration',
			'description' => 'Badge Checkin',
			'icon' => 'id-badge'],
		'badge-print' => [
			'exp' => 5*3600,
			'group' => 'Registration',
			'description' => 'Badge Print',
			'icon' => 'print'],
		'meals-checkin' => [
			'exp' => 2*3600,
			'group' => 'Registration',
			'description' => 'Meals Checkin',
			'icon' => 'utensils']
	];
}

class postData {
	var string $token = '';
	var $decoded;
	var $post = [];

	public function __construct(string $token) {
		$this->token = $token;
	}

	public function addPost($key, $value): void {
		$this->post[$key] = $value;
	}

	public function getPost($key): string {
		if (!array_key_exists($key, $this->post)) die (__FILE__.": Unknown key: $key");
		return $this->post[$key];
	}
}


class jwtwrapper
{
	private array $payload = [];

	public function __construct(
		private string $nonce)
	{
		# nonce cannot resolve empty
		if ( trim($this->nonce) == '' ) die('Invalid nonce name');

		if ( $this->nonce == 'stub' ) {
			Helpers::sessionSet('jwt_token_page','');
		}

		$payload['nonce'] = $this->nonce;
	}

	/**
	 * Update class to match a token; token must have state="issued", matching nonce, and matching subject.
	 * @param string $token Token string
	 * @return object Payload object or null
	 */

	public function loadFromToken(string $token ): ?object {
		try {
			$payload = jwtWrapper::decodeUnverified($token);
		}
		catch(Exception $e) {
			return null;
		}

		foreach($payload AS $k => $v ) $this->setPayloadValue($k,$v);

		$secret = '';
		if ( property_exists($payload,'nonce') ) {
			$this->nonce = $payload->nonce;
			$secret = $this->getSecret();
		}
	
		if ( '' == $secret ) return null;
	
		// Fully decode token, guarantee payload object keys
		$decoded = jwtwrapper::decodeVerified($token, $secret);
	
		if ( null == $decoded ) return null;

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->qn('state'))
			->from('#__claw_jwt')
			->where('nonce = ' . $db->q($payload->nonce))
			->where('secret='.$db->q($secret))
			->where('subject='.$db->q($decoded->subject))
			->where('state='.$db->q(jwt_token_state::issued->value));
		$db->setQuery($query);
		$state = $db->loadResult();

		if ( $state == null ) return null;
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
		if (null === ($header = Firebase\JWT\JWT::jsonDecode(Firebase\JWT\JWT::urlsafeB64Decode($headb64)))) {
			throw new UnexpectedValueException('Invalid header encoding');
		}
		if (null === $payload = Firebase\JWT\JWT::jsonDecode(Firebase\JWT\JWT::urlsafeB64Decode($bodyb64))) {
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
	public static function decodeVerified(string $token, string $secret ): ?object
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

		if ( sizeof($results) == 0 && !$allowInit) return '';
		if ( !array_key_exists('email', $this->payload) || $this->payload['email'] == '' ) return '';

		$secret = '';

		foreach ( $results as $result ) {
			if ( $result->state == jwt_token_state::revoked->value ) continue;
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
	public function setPayloadValue(string $key, string $value ): void
	{
		$this->payload[$key] = $value;
	}

	/**
	 * From the columns of the jwt database table, create a fully issued token
	 * @param object Database row object
	 * @return string Issued jwt token
	 */
	public function issueToken(object $row): string {
		$this->setPayloadValue('iat', intval($row->iat));
		$this->setPayloadValue('exp', intval($row->exp));
		$this->setPayloadValue('email', $row->email);
		$this->setPayloadValue('nonce', $row->nonce);
		$this->setPayloadValue('state', jwt_token_state::issued->value);
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

		if ( !array_key_exists($subject, jwt_token_pages::page)) {
			die(__FILE__. ': Invalid page requested: '. $subject);
		}

		$validSeconds = jwt_token_pages::page[$subject]['exp'];

		if ($validSeconds < 1 || $validSeconds > 86400) {
			die('Validity time must be between 1 and 86,400 seconds');
		}

		$iat = time();
		$exp = time() + $validSeconds;

		$this->payload['iat'] = $iat;
		$this->payload['exp'] = $exp;
		$this->payload['nonce'] = $nonce;
		$this->payload['subject'] = $subject;

		$this->payload['state'] = jwt_token_state::confirm->value;
		$confirmToken = JWT::encode($this->payload, $secret, 'HS384');

		$this->payload['state'] = jwt_token_state::revoked->value;
		$revokeToken = JWT::encode($this->payload, $secret, 'HS384');

		$qiat = $db->q($iat);
		$qexp = $db->q($exp);
		$qnonce = $db->q($nonce);
		$qstate = $db->q(jwt_token_state::init->value);
		$qsubject = $db->q($subject);

		$query = <<<EOL
	UPDATE jwt SET `state`=$qstate, `iat`=$qiat, `exp`=$qexp
	WHERE `nonce`=$qnonce AND `subject`=$qsubject
	EOL;
		$db->setQuery($query);
		$db->execute();

		return [$confirmToken, $revokeToken];
	}

	public static function redirectOnInvalidToken(string $page, bool $json = false): ?postData {
		$app = Factory::getApplication('site');

		$uri_path = \Joomla\CMS\Uri\Uri::getInstance()->getPath();
		if ( !$json) Helpers::sessionSet('jwt_redirect', $uri_path);
		Helpers::sessionSet('jwt_token_page', $page);

		$post = file_get_contents("php://input");
		$form = json_decode($post, true);

		$token = '';

		if ( $form != null && array_key_exists('token', $form)) {
			$token = $form['token'];
		} else {
			$token = trim($app->input->get('token', '', 'string'));
		}

		if ( '' == $token) {
			if ( $json ) return null;
			$app->redirect('/getlink');
			return null;
		}

		$jwt = new jwtwrapper('stub');
		$decoded = $jwt->loadFromToken($token);

		if (null == $decoded) {
			if ($json) return null;
			$app->redirect('/getlink');
			return null;
		}

		$result = new postData($token);
		$result->decoded = $decoded;

		if ($form != null) {
			foreach ($form as $k => $v) {
				if ($k == 'token') continue;
				$result->addPost($k, $v);
			}
		}

		return $result;
	}

	public static function writeLinks(string $target = ""): void
	{
		$content = [];

		foreach ( jwt_token_pages::page AS $l => $p )
		{
			$c = <<< HTML
  <a href="/{$l}" role="button" target="$target" class="btn btn-outline-light">{$p['description']}</a>
HTML;

	  	$content[$p['icon']] = [$c];
		}

		Bootstrap::writeGrid($content);
	}
}
