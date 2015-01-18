<?
namespace Framework\Twitter;

use \Framework\Newnorth\ConfigException

class Twitter {
	/* Static variables */

	private static $Protocol = 'https';

	private static $Host = 'api.twitter.com';

	private static $IsConfigLoaded = false;

	private static $Token;

	private static $TokenSecret;

	private static $ConsumerKey;

	private static $ConsumerSecret;

	/* Methods */

	private static function LoadConfig() {
		if(Twitter::$IsConfigLoaded) {
			return;
		}

		if(!HasConfig('Twitter')) {
			throw new ConfigException('Twitter API not configured.');
		}

		$Config = GetConfig('Twitter');

		if(!isset($Config['Token'][0])) {
			throw new ConfigException('"Token" for Twitter API not set.');
		}

		Twitter::$Token = $Config['Token'];

		if(!isset($Config['TokenSecret'][0])) {
			throw new ConfigException('"TokenSecret" for Twitter API not set.');
		}

		Twitter::$TokenSecret = $Config['TokenSecret'];

		if(!isset($Config['ConsumerKey'][0])) {
			throw new ConfigException('"ConsumerKey" for Twitter API not set.');
		}

		Twitter::$ConsumerKey = $Config['ConsumerKey'];

		if(!isset($Config['ConsumerSecret'][0])) {
			throw new ConfigException('"ConsumerSecret" for Twitter API not set.');
		}

		Twitter::$ConsumerSecret = $Config['ConsumerSecret'];
	}

	private static function CreateAuth($Method, $Url, $Query) {
		$OAuth = array(
			'oauth_consumer_key' => Twitter::$ConsumerKey,
			'oauth_token' => Twitter::$Token,
			'oauth_nonce' => (string)mt_rand(),
			'oauth_timestamp' => time(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_version' => '1.0'
		);

		$OAuth['oauth_signature'] = Twitter::CreateSignature($Method, $Url, $Query, $OAuth);

		$Auth = http_build_query($OAuth, '', ', ');
		$Auth = urldecode($Auth);
		$Auth = 'OAuth '.$Auth;

		return $Auth;
	}

	private static function CreateSignature($Method, $Url, $Query, $OAuth) {
		$Data = array_merge($OAuth, $Query);
		ksort($Data);
		$Data = http_build_query($Data, '', '&');
		$Data = urldecode($Data);
		$Data = $Method.'&'.rawurlencode($Url).'&'.rawurlencode($Data);

		$Key = rawurlencode(Twitter::$ConsumerSecret)."&".rawurlencode(Twitter::$TokenSecret);

		$Hash = rawurlencode(base64_encode(hash_hmac('sha1', $Data, $Key, true)));

		return $Hash;
	}

	private static function ExecuteRequest($Url, $Auth) {
		$CUrl = curl_init();

		curl_setopt_array(
			$CUrl,
			array(
				CURLOPT_HTTPHEADER => array('Authorization: '.$Auth),
				CURLOPT_HEADER => false,
				CURLOPT_URL => $Url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false
			)
		);

		$Result = curl_exec($CUrl);

		curl_close($CUrl);

		return $Result;
	}

	public static function GetUserTimeline($Query) {
		Twitter::LoadConfig();
		$Query = array_map('rawurlencode', $Query);
		$Path = '/1.1/statuses/user_timeline.json';
		$Url = Twitter::$Protocol.'://'.Twitter::$Host.$Path;
		$Uri = $Url.'?'.http_build_query($Query);
		$Auth = Twitter::CreateAuth('GET', $Url, $Query);
		$Result = Twitter::ExecuteRequest($Uri, $Auth);
		$Result = json_decode($Result);
		return $Result;
	}
}
?>