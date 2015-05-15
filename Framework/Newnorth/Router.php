<?
namespace Framework\Newnorth;

class Router {
	/* Static methods */

	public static function RerouteErrorPage() {
		header('HTTP/1.0 500 Internal Server Error');

		$Parameters = $GLOBALS['Config']->ErrorHandling['Pages']['Error'];

		// Try to add locale if not already set.
		if(!isset($Parameters['Locale'])) {
			if(isset($GLOBALS['Parameters']['Locale'])) {
				$Parameters['Locale'] = $GLOBALS['Parameters']['Locale'];
			}
			else if(isset($GLOBALS['Config']->Defaults['Locale'][0])) {
				$Parameters['Locale'] = $GLOBALS['Config']->Defaults['Locale'];
			}
		}

		throw new RerouteException($Parameters);
	}

	public static function RerouteForbiddenPage() {
		header('HTTP/1.0 403 Forbidden');

		$Parameters = $GLOBALS['Config']->ErrorHandling['Pages']['Forbidden'];

		// Try to add locale if not already set.
		if(!isset($Parameters['Locale'])) {
			if(isset($GLOBALS['Parameters']['Locale'])) {
				$Parameters['Locale'] = $GLOBALS['Parameters']['Locale'];
			}
			else if(isset($GLOBALS['Config']->Defaults['Locale'][0])) {
				$Parameters['Locale'] = $GLOBALS['Config']->Defaults['Locale'];
			}
		}

		throw new RerouteException($Parameters);
	}

	public static function RerouteNotFoundPage() {
		header('HTTP/1.0 404 Not Found');

		$Parameters = $GLOBALS['Config']->ErrorHandling['Pages']['NotFound'];

		// Try to add locale if not already set.
		if(!isset($Parameters['Locale'])) {
			if(isset($GLOBALS['Parameters']['Locale'])) {
				$Parameters['Locale'] = $GLOBALS['Parameters']['Locale'];
			}
			else if(isset($GLOBALS['Config']->Defaults['Locale'][0])) {
				$Parameters['Locale'] = $GLOBALS['Config']->Defaults['Locale'];
			}
		}

		throw new RerouteException($Parameters);
	}

	public static function Reroute($Path = '', array $Parameters = []) {
		$Url = Router::GetUrl($Path, $Parameters);

		Router::ParseUrl($Url, $Route, $Parameters);

		throw new RerouteException($Parameters);
	}

	public static function Redirect($Path = '', array $Parameters = [], $QueryString = '') {
		throw new RedirectException(self::GetUrl($Path, $Parameters, $QueryString));
	}

	public static function ParseUrl($Url, Route &$Route = null, array &$Parameters = null) {
		return $GLOBALS['Routing']->Route->ParseUrl($Url, $Route, $Parameters = []);
	}

	public static function GetUrl($Path = '', array $Parameters = [], $QueryString = '') {
		// Add current parameters that is not already set.
		foreach($GLOBALS['Parameters'] as $Key => $Value) {
			if(!isset($Parameters[$Key])) {
				$Parameters[$Key] = $Value;
			}
		}

		if(isset($Path[0])) {
			if($Path[0] === '/') {
				if(isset($Path[1])) {
					return $GLOBALS['Routing']->Route->GetUrl(explode('/', substr($Path, 1)), false, $Parameters).(isset($QueryString[0]) ? '?'.$QueryString : '');
				}
				else {
					return $GLOBALS['Routing']->Route->GetUrl([], false, $Parameters).(isset($QueryString[0]) ? '?'.$QueryString : '');
				}
			}
			else if($GLOBALS['Route'] === null) {
				throw new RuntimeException(
					'Unable to get URL to a relative route from a non-existing route.',
					[
						'Current Parameters' => $GLOBALS['Parameters'],
						'Requested path' => $Path,
						'Requested parameters' => $Parameters,
						'Requested query string' => $QueryString,
					]
				);
			}
			else {
				return $GLOBALS['Route']->GetUrl(explode('/', $Path), true, $Parameters).(isset($QueryString[0]) ? '?'.$QueryString : '');
			}
		}
		else if($GLOBALS['Parameters']['Page'] === $GLOBALS['Routing']->Route->FullName) {
			return $GLOBALS['Routing']->Route->GetUrl([], false, $Parameters).(isset($QueryString[0]) ? '?'.$QueryString : '');
		}
		else {
			return $GLOBALS['Routing']->Route->GetUrl(explode('/', $GLOBALS['Parameters']['Page']), false, $Parameters).(isset($QueryString[0]) ? '?'.$QueryString : '');
		}
	}
}
?>