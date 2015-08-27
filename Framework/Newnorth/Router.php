<?
namespace Framework\Newnorth;

class Router {
	/* Static methods */

	public static function RerouteErrorPage($Exception) {
		header('HTTP/1.0 500 Internal Server Error');

		$Parameters = $GLOBALS['Config']->ErrorHandling['Pages']['Error'];

		$Parameters['Error'] = $Exception;

		foreach($GLOBALS['Parameters'] as $Key => $Value) {
			if(!isset($Parameters[$Key])) {
				$Parameters[$Key] = $Value;
			}
		}

		if(!isset($Parameters['Locale']) && isset($GLOBALS['Config']->Defaults['Locale'][0])) {
			$Parameters['Locale'] = $GLOBALS['Config']->Defaults['Locale'];
		}

		throw new RerouteException($Parameters);
	}

	public static function RerouteForbiddenPage() {
		header('HTTP/1.0 403 Forbidden');

		$Parameters = $GLOBALS['Config']->ErrorHandling['Pages']['Forbidden'];

		foreach($GLOBALS['Parameters'] as $Key => $Value) {
			if(!isset($Parameters[$Key])) {
				$Parameters[$Key] = $Value;
			}
		}

		if(!isset($Parameters['Locale']) && isset($GLOBALS['Config']->Defaults['Locale'][0])) {
			$Parameters['Locale'] = $GLOBALS['Config']->Defaults['Locale'];
		}

		throw new RerouteException($Parameters);
	}

	public static function RerouteNotFoundPage() {
		header('HTTP/1.0 404 Not Found');

		$Parameters = $GLOBALS['Config']->ErrorHandling['Pages']['NotFound'];

		foreach($GLOBALS['Parameters'] as $Key => $Value) {
			if(!isset($Parameters[$Key])) {
				$Parameters[$Key] = $Value;
			}
		}

		if(!isset($Parameters['Locale']) && isset($GLOBALS['Config']->Defaults['Locale'][0])) {
			$Parameters['Locale'] = $GLOBALS['Config']->Defaults['Locale'];
		}

		throw new RerouteException($Parameters);
	}

	public static function Reroute($Path = '', array $Parameters = []) {
		$Url = Router::GetUrl($Path, $Parameters);

		Router::ParseUrl($Url, $Route, $RealRoute, $Parameters);

		throw new RerouteException($Parameters);
	}

	public static function Redirect($Path = '', array $Parameters = [], $QueryString = '') {
		if($QueryString === '') {
			throw new RedirectException(Router::GetFullUrl($Path, $Parameters));
		}
		else {
			throw new RedirectException(Router::GetFullUrl($Path, $Parameters).'?'.$QueryString);
		}
	}

	public static function ParseUrl($Url, Route &$Route = null, Route &$RealRoute = null, array &$Parameters = null) {
		if($GLOBALS['Routing']->Route->ParseUrl($Url, $Route, $RealRoute, $Parameters = [])) {
			$Parameters['Route'] = $Route->FullName;

			$Parameters['RealRoute'] = $RealRoute->FullName;

			foreach($Route->Parameters as $ParameterName => $ParameterValue) {
				if(!isset($Parameters[$ParameterName])) {
					$Parameters[$ParameterName] = $ParameterValue;
				}
			}

			foreach($RealRoute->Parameters as $ParameterName => $ParameterValue) {
				if(!isset($Parameters[$ParameterName])) {
					$Parameters[$ParameterName] = $ParameterValue;
				}
			}

			if(!isset($Parameters['Application'][0])) {
				$Parameters['Application'] = 'Default';
			}

			if(!isset($Parameters['Layout'][0])) {
				$Parameters['Layout'] = 'Default';
			}

			$Parameters['Page'] = $RealRoute->FullName;

			if(!isset($Parameters['Locale']) && isset($GLOBALS['Config']->Defaults['Locale'][0])) {
				$Parameters['Locale'] = $GLOBALS['Config']->Defaults['Locale'];
			}

			return true;
		}
		else {
			return false;
		}
	}

	public static function GetRoute(Route $CurrentRoute = null, $Path = '', Route &$Route = null) {
		if(isset($Path[0])) {
			if($Path[0] === '/') {
				if(isset($Path[1])) {
					return $GLOBALS['Routing']->Route->GetRoute(explode('/', substr($Path, 1)), $Route);
				}
				else {
					return $GLOBALS['Routing']->Route->GetRoute([], $Route);
				}
			}
			else if($CurrentRoute === null) {
				throw new RuntimeException(
					'Unable to get relative route from a non-current route.',
					[
						'Requested path' => $Path
					]
				);
			}
			else {
				return $CurrentRoute->GetRoute(explode('/', $Path), $Route);
			}
		}
		else if($CurrentRoute === null) {
			throw new RuntimeException(
				'Unable to get relative route from a non-current route.',
				[
					'Requested path' => $Path
				]
			);
		}
		else {
			return $CurrentRoute;
		}
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
			return $GLOBALS['Routing']->Route->GetUrl(explode('/', $GLOBALS['Parameters']['Route']), false, $Parameters).(isset($QueryString[0]) ? '?'.$QueryString : '');
		}
	}

	public static function GetFullUrl($Path = '', array $Parameters = []) {
		// Add current parameters that is not already set.
		foreach($GLOBALS['Parameters'] as $Key => $Value) {
			if(!isset($Parameters[$Key])) {
				$Parameters[$Key] = $Value;
			}
		}

		if($Path === '') {
			return $GLOBALS['Route']->GetFullUrl([], $Parameters);
		}
		else if($Path[0] === '/') {
			$Path = explode('/', $Path);

			return $GLOBALS['Routing']->Route->GetFullUrl($Path, $Parameters);
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
			$Path = explode('/', $Path);

			return $GLOBALS['Route']->GetFullUrl($Path, $Parameters);
		}
	}
}
?>