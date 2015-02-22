<?
namespace Framework\Twitter;

use \Framework\Newnorth\ConfigException;

class Twitter {
	/* Static variables */

	private static $Protocol = 'https';

	private static $Host = 'api.twitter.com';

	private static $IsConfigLoaded = false;

	private static $ConsumerKey = '';

	private static $ConsumerSecret = '';

	private static $Token = '';

	private static $TokenSecret = '';

	/* Methods */

	private static function LoadConfig() {
		if(Twitter::$IsConfigLoaded) {
			return;
		}

		if(\Framework\Newnorth\Application::HasConfig('Twitter')) {
			$Config = \Framework\Newnorth\Application::GetConfig('Twitter');

			if(isset($Config['ConsumerKey'])) {
				Twitter::$ConsumerKey = $Config['ConsumerKey'];
			}

			if(isset($Config['ConsumerSecret'])) {
				Twitter::$ConsumerSecret = $Config['ConsumerSecret'];
			}

			if(isset($Config['Token'])) {
				Twitter::$Token = $Config['Token'];
			}

			if(isset($Config['TokenSecret'])) {
				Twitter::$TokenSecret = $Config['TokenSecret'];
			}
		}
	}

	private static function CreateAuth($Method, $Url, $AuthParameters = [], $Query = []) {
		$Auth = array_merge(
			[
				'oauth_consumer_key' => Twitter::$ConsumerKey,
				'oauth_nonce' => (string)mt_rand(),
				'oauth_timestamp' => time(),
				'oauth_signature_method' => 'HMAC-SHA1',
				'oauth_version' => '1.0',
			],
			$AuthParameters
		);

		$Auth['oauth_signature'] = Twitter::CreateSignature($Auth, $Method, $Url, $Query);

		$Auth = 'OAuth '.urldecode(http_build_query($Auth, '', ', '));

		return $Auth;
	}

	private static function CreateSignature($Auth, $Method, $Url, $Query) {
		$Data = array_merge($Auth, $Query);

		ksort($Data);

		$Data = http_build_query($Data, '', '&');
		$Data = urldecode($Data);
		$Data = $Method.'&'.rawurlencode($Url).'&'.rawurlencode($Data);

		$Key = rawurlencode(Twitter::$ConsumerSecret)."&".rawurlencode(Twitter::$TokenSecret);

		$Hash = rawurlencode(base64_encode(hash_hmac('sha1', $Data, $Key, true)));

		return $Hash;
	}

	private static function ExecuteRequest($Url, $Auth, $Data = null) {
		$CUrl = curl_init();

		$CUrlOptions = [
			CURLOPT_HTTPHEADER => array('Authorization: '.$Auth),
			CURLOPT_HEADER => false,
			CURLOPT_URL => $Url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
		];

		if($Data !== null) {
			$CUrlOptions[CURLOPT_POSTFIELDS] = $Data;
		}

		curl_setopt_array($CUrl, $CUrlOptions);

		$Result = curl_exec($CUrl);

		curl_close($CUrl);

		return $Result;
	}

	public static function InitiateLogin($CallbackUrl = null) {
		Twitter::LoadConfig();

		$Url = 'https://api.twitter.com/oauth/request_token';

		$AuthParameters = [];

		if($CallbackUrl !== null) {
			$AuthParameters['oauth_callback'] = rawurlencode($CallbackUrl);
		}

		$Auth = Twitter::CreateAuth('GET', $Url, $AuthParameters);

		$Response = Twitter::ExecuteRequest($Url, $Auth);

		if(0 < preg_match('/^oauth_token=(.+?)&oauth_token_secret=(.+?)&oauth_callback_confirmed=([a-z]+)$/', $Response, $Match)) {
			$Response = [
				'oauth_token' => $Match[1],
				'oauth_token_secret' => $Match[2],
				'oauth_callback_confirmed' => $Match[3],
			];

			\Framework\Newnorth\Redirect('https://api.twitter.com/oauth/authenticate?oauth_token='.$Response['oauth_token']);
		}
		else {
			if(error_reporting() !== 0) {
				throw new \Framework\Newnorth\RuntimeException(
					'Failed to initialize Twitter login',
					[
						'CallbackUrl' => $CallbackUrl,
						'Response' => $Response,
					]
				);
			}

			return false;
		}
	}

	public static function FinalizeLogin($Token, $Verifier) {
		Twitter::LoadConfig();

		$Url = 'https://api.twitter.com/oauth/access_token';

		$AuthParameters = [
			'oauth_token' => $Token,
		];

		$Data = [
			'oauth_verifier' => $Verifier,
		];

		$Auth = Twitter::CreateAuth('POST', $Url, $AuthParameters);

		$Response = Twitter::ExecuteRequest($Url, $Auth, $Data);

		if(0 < preg_match('/^oauth_token=(.+?)&oauth_token_secret=(.+?)&user_id=([0-9]+)&screen_name=(.+?)$/', $Response, $Match)) {
			return [
				'oauth_token' => $Match[1],
				'oauth_token_secret' => $Match[2],
				'user_id' => $Match[3],
				'screen_name' => $Match[4],
			];
		}
		else {
			if(error_reporting() !== 0) {
				throw new \Framework\Newnorth\RuntimeException(
					'Failed to finalize Twitter login',
					[
						'Token' => $Token,
						'Verifier' => $Verifier,
						'Response' => $Response,
					]
				);
			}

			return false;
		}
	}

	public static function GetUserTimeline($Query) {
		Twitter::LoadConfig();
		$Query = array_map('rawurlencode', $Query);
		$Path = '/1.1/statuses/user_timeline.json';
		$Url = Twitter::$Protocol.'://'.Twitter::$Host.$Path;
		$Uri = $Url.'?'.http_build_query($Query);

		$Auth = Twitter::CreateAuth(
			'GET',
			$Url,
			[
				'oauth_token' => Twitter::$Token,
			],
			$Query
		);

		$Result = Twitter::ExecuteRequest($Uri, $Auth);
		$Result = json_decode($Result);

		return $Result;
	}
}
?>