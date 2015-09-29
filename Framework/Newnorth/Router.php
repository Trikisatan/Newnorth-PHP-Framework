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

	public static function RerouteBadRequestPage() {
		header('HTTP/1.0 400 Bad Request');

		$Parameters = $GLOBALS['Config']->ErrorHandling['Pages']['BadRequest'];

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
		$Url = Router::CreateUrl($Path, $Parameters);

		Router::ParseUrl($Url, $Route, $RealRoute, $Parameters);

		throw new RerouteException($Parameters);
	}

	public static function Redirect($Path = '', array $Parameters = [], $QueryString = '') {
		throw new RedirectException(Router::CreateUrl($Path, $Parameters, $QueryString));
	}

	public static function ParseUrl($Url, Route &$Route = null, Route &$RealRoute = null, array &$Parameters = null) {
		if($GLOBALS['Routing']->Route->ParseUrl($Url, $Route, $RealRoute, $Parameters = [])) {
			if($Route instanceof \Framework\Newnorth\Route) {
				$Parameters['Route'] = $Route->FullName;
			}
			else {
				$Parameters['Route'] = $Route;
			}

			$Parameters['RealRoute'] = $RealRoute->FullName;

			if($Route instanceof \Framework\Newnorth\Route) {
				foreach($Route->Parameters as $ParameterName => $ParameterValue) {
					if(!isset($Parameters[$ParameterName])) {
						$Parameters[$ParameterName] = $ParameterValue;
					}
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

	public static function CreateUrl($Path = '', array $Parameters = [], $QueryString = '') {
		// Add current parameters that is not already set.
		foreach($GLOBALS['Parameters'] as $Key => $Value) {
			if(!isset($Parameters[$Key])) {
				$Parameters[$Key] = $Value;
			}
		}

		if($GLOBALS['Route'] instanceof \Framework\Newnorth\Route) {
			if($GLOBALS['Route']->FullName === 'Index') {
				$Route = '';
			}
			else {
				$Route = $GLOBALS['Route']->FullName;
			}
		}
		else {
			$Route = $GLOBALS['Route'];
		}

		if($Path === '') {
			if($Route === '') {
				if($GLOBALS['Routing']->Route->CreateUrl([], $Parameters, $Url)) {
					return $Url.(isset($QueryString[0]) ? '?'.$QueryString : '');
				}
			}
			else if($GLOBALS['Routing']->Route->CreateUrl(explode('/', $Route), $Parameters, $Url)) {
				return $Url.(isset($QueryString[0]) ? '?'.$QueryString : '');
			}
		}
		else if($Path === '/') {
			if($GLOBALS['Routing']->Route->CreateUrl([], $Parameters, $Url)) {
				return $Url.(isset($QueryString[0]) ? '?'.$QueryString : '');
			}
		}
		else if($Path[0] === '/') {
			if($GLOBALS['Routing']->Route->CreateUrl(explode('/', substr($Path, 1)), $Parameters, $Url)) {
				return $Url.(isset($QueryString[0]) ? '?'.$QueryString : '');
			}
		}
		else if($GLOBALS['Route'] === null) {
			throw new RuntimeException(
				'Unable to get URL to a relative route from a non-existing route.',
				[
					'Current Parameters' => $GLOBALS['Parameters'],
					'Requested path' => $Path,
					'Requested parameters' => $Parameters,
				]
			);
		}
		else {
			while(2 < strlen($Path) && substr($Path, 0, 3) === '../') {
				if(isset($Route[0])) {
					$Index = strrpos($Route, '/');

					if($Index === -1) {
						$Route = '';
					}
					else {
						$Route = substr($Route, 0, $Index);
					}
				}
				else {
					$Route = null;
				}

				$Path = substr($Path, 3);
			}

			if($Path === '..') {
				if(isset($Route[0])) {
					$Index = strrpos($Route, '/');

					if($Index === -1) {
						$Route = '';
					}
					else {
						$Route = substr($Route, 0, $Index);
					}
				}
				else {
					$Route = null;
				}

				$Path = '';
			}

			if(isset($Path[0])) {
				if(!isset($Route[0])) {
					if($GLOBALS['Routing']->Route->CreateUrl(explode('/', $Path), $Parameters, $Url)) {
						return $Url.(isset($QueryString[0]) ? '?'.$QueryString : '');
					}
				}

				if(isset($Route[0])) {
					if($GLOBALS['Routing']->Route->CreateUrl(explode('/', $Route.'/'.$Path), $Parameters, $Url)) {
						return $Url.(isset($QueryString[0]) ? '?'.$QueryString : '');
					}
				}
			}
			else {
				if(!isset($Route[0])) {
					if($GLOBALS['Routing']->Route->CreateUrl([], $Parameters, $Url)) {
						return $Url.(isset($QueryString[0]) ? '?'.$QueryString : '');
					}
				}

				if(isset($Route[0])) {
					if($GLOBALS['Routing']->Route->CreateUrl(explode('/', $Route), $Parameters, $Url)) {
						return $Url.(isset($QueryString[0]) ? '?'.$QueryString : '');
					}
				}
			}
		}

		throw new RuntimeException(
			'Route not found.',
			[
				'Route' => $Route,
				'Path' => $Path,
			]
		);
	}
}
?>