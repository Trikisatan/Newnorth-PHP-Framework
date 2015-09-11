<?
namespace Framework\Newnorth;

class Route {
	/* Instance variables */

	public $Parent;

	public $RealRoute;

	public $Name;

	public $FullName;

	public $Pattern;

	public $TranslatedPatterns = [];

	public $ReversablePattern;

	public $TranslatedReversablePatterns = [];

	public $Parameters = [];

	public $Routes = [];

	/* Magic methods */

	public function __construct($Parent, $Name, $Data) {
		$this->Parent = $Parent;

		if(isset($Data['RealRoute'][0])) {
			$this->RealRoute = $Data['RealRoute'];
		}
		else {
			$this->RealRoute = null;
		}

		if($this->Parent === null) {
			$this->Name = 'Index';
		}
		else {
			$this->Name = $Name;
		}

		if($this->Parent === null || $this->Parent->Parent === null) {
			$this->FullName = $this->Name;
		}
		else {
			$this->FullName = $this->Parent->FullName.'/'.$this->Name;
		}

		if($this->Parent === null) {
			if(isset($Data['Pattern'][0])) {
				$this->Pattern = '/^\/'.str_replace('/', '\/', $Data['Pattern']).'\/(.*?)$/';
			}
			else {
				$this->Pattern = '/^\/(.*?)$/';
			}
		}
		else if(isset($Data['Pattern'][0])) {
			$this->Pattern = '/^'.str_replace('/', '\/', $Data['Pattern']).'\/(.*?)$/';
		}

		if($this->Pattern !== null) {
			$this->Pattern = preg_replace('/(?<=\^|\\\\\/)\*(.*?)(?:\((.*?)\))?\\\\\//', '(?:(?<$1>$2)\/)?', $this->Pattern);

			$this->Pattern = preg_replace('/(?<=\^|\\\\\/)\+(.*?)(?:\((.*?)\))?\\\\\//', '(?<$1>$2)\/', $this->Pattern);

			if(isset($Data['Translations'])) {
				foreach($Data['Translations'] as $Locale => $Translation) {
					$this->TranslatedPatterns[$Locale] = str_replace('@', str_replace('/', '\/', $Translation), $this->Pattern);
				}
			}
		}

		if($this->Parent === null) {
			if(isset($Data['Pattern'][0])) {
				$this->ReversablePattern = '/'.$Data['Pattern'].'/';
			}
			else {
				$this->ReversablePattern = '/';
			}
		}
		else if(isset($Data['Pattern'][0])) {
			$this->ReversablePattern = $Data['Pattern'].'/';
		}

		if($this->ReversablePattern !== null) {
			if(isset($Data['Translations'])) {
				foreach($Data['Translations'] as $Locale => $Translation) {
					$this->TranslatedReversablePatterns[$Locale] = str_replace('@', $Translation, $this->ReversablePattern);
				}
			}
		}

		$this->Parameters = isset($Data['Parameters']) ? $Data['Parameters'] : [];

		if(isset($Data['Routes'])) {
			foreach($Data['Routes'] as $Name => $Data) {
				$this->Routes[$Name] = new Route($this, $Name, $Data);
			}
		}

		\Framework\Newnorth\RegisterObject($this);
	}

	public function __toString() {
		return '/'.$this->FullName.'Route';
	}

	/* Instance methods */

	public function ParseUrl($Url, &$Route, Route &$RealRoute = null, array &$Parameters) {
		if($this->RealRoute !== null && !Router::GetRoute($this, $this->RealRoute, $RealRoute)) {
			throw new RuntimeException(
				'Real route not found.',
				[
					'Route' => $this->FullName,
					'Real route' => $this->RealRoute,
				]
			);
		}

		if($this->Pattern === null) {
			if($RealRoute === null) {
				return false;
			}
			else if($Route === null) {
				return $RealRoute->ParseUrl($Url, $Route = $this, $RealRoute, $Parameters);
			}
			else {
				return $RealRoute->ParseUrl($Url, $Route, $RealRoute, $Parameters);
			}
		}
		else if(0 < count($this->TranslatedPatterns)) {
			return $this->ParseUrlWithTranslation($Url, $Route, $RealRoute, $Parameters);
		}
		else {
			return $this->ParseUrlWithoutTranslation($Url, $Route, $RealRoute, $Parameters);
		}
	}

	private function ParseUrlWithTranslation($Url, &$Route, Route &$RealRoute = null, array &$Parameters) {
		foreach($this->TranslatedPatterns as $Locale => $Pattern) {
			if(isset($Locale[0], $Parameters["Locale"]) && $Locale !== $Parameters["Locale"]) {
				continue;
			}

			if(preg_match($Pattern, $Url, $Matches) === 1) {
				end($Matches);

				$Url = preg_replace($Pattern, '$'.key($Matches), $Url);

				// If a locale is used for this route, add it to its parameters.
				$Parameters["Locale"] = $Locale;

				// Add new parameters obtained via the pattern.
				foreach($Matches as $Key => $Value) {
					if(isset($Value[0]) && !is_int($Key)) {
						$Parameters[$Key] = $Value;
					}
				}

				if(isset($Url[0])) {
					return $this->ContinueParsingUrl($Url, $Route, $RealRoute, $Parameters);
				}
				else {
					return $this->FinishParsingUrl($Url, $Route, $RealRoute, $Parameters);
				}
			}
		}

		return false;
	}

	private function ParseUrlWithoutTranslation($Url, &$Route, Route &$RealRoute = null, array &$Parameters) {
		if(preg_match($this->Pattern, $Url, $Matches) === 1) {
			end($Matches);

			$Url = preg_replace($this->Pattern, '$'.key($Matches), $Url);

			// Add new parameters obtained via the pattern.
			foreach($Matches as $Key => $Value) {
				if(isset($Value[0]) && !is_int($Key)) {
					$Parameters[$Key] = $Value;
				}
			}

			if(isset($Url[0])) {
				return $this->ContinueParsingUrl($Url, $Route, $RealRoute, $Parameters);
			}
			else {
				return $this->FinishParsingUrl($Url, $Route, $RealRoute, $Parameters);
			}
		}
		else {
			return false;
		}
	}

	private function ContinueParsingUrl($Url, &$Route, Route &$RealRoute = null, array &$Parameters) {
		if($Route === null) {
			foreach($this->Routes as $PossibleRoute) {
				$NewRoute = null;

				$NewRealRoute = null;

				$NewParameters = $Parameters;

				if($PossibleRoute->ParseUrl($Url, $NewRoute, $NewRealRoute, $NewParameters)) {
					$Route = $NewRoute;

					$RealRoute = $NewRealRoute;

					$Parameters = $NewParameters;

					return true;
				}
			}
		}
		else if($Route instanceof \Framework\Newnorth\Route) {
			foreach($Route->Routes as $PossibleRoute) {
				$NewRoute = null;

				$NewRealRoute = null;

				$NewParameters = $Parameters;

				if($PossibleRoute->ParseUrl($Url, $NewRoute, $NewRealRoute, $NewParameters)) {
					$Route = $NewRoute;

					$RealRoute = $NewRealRoute;

					$Parameters = $NewParameters;

					return true;
				}
			}
		}

		if($RealRoute !== null) {
			foreach($RealRoute->Routes as $PossibleRoute) {
				if($Route === null) {
					$NewRoute = $this->FullName.'/'.$PossibleRoute->Name;
				}
				else if($Route instanceof \Framework\Newnorth\Route) {
					$NewRoute = $Route->FullName.'/'.$PossibleRoute->Name;
				}
				else {
					$NewRoute = $Route.'/'.$PossibleRoute->Name;
				}

				$NewRealRoute = $PossibleRoute;

				$NewParameters = $Parameters;

				if($PossibleRoute->ParseUrl($Url, $NewRoute, $NewRealRoute, $NewParameters)) {
					$Route = $NewRoute;

					$RealRoute = $NewRealRoute;

					$Parameters = $NewParameters;

					return true;
				}
			}
		}

		return false;
	}

	private function FinishParsingUrl($Url, &$Route, Route &$RealRoute = null, array &$Parameters) {
		if($Route === null) {
			$Route = $this;
		}

		if($this->RealRoute === null) {
			$RealRoute = $this;
		}
		else if(!Router::GetRoute($this, $this->RealRoute, $RealRoute)) {
			throw new RuntimeException(
				'Real route not found.',
				[
					'Current route' => $this->FullName,
					'Real route' => $this->RealRoute,
				]
			);
		}

		return true;
	}

	public function GetRoute(array $Path, Route &$Route = null) {
		if(0 < count($Path)) {
			$Node = array_shift($Path);

			if($Node === '..') {
				return $this->Parent->GetRoute($Path, $Route);
			}
			else if(!isset($this->Routes[$Node])) {
				throw new RuntimeException(
					'Subroute not found.',
					[
						'Route' => $this->FullName,
						'Subroute' => $Node,
					]
				);
			}
			else {
				return $this->Routes[$Node]->GetRoute($Path, $Route);
			}
		}
		else {
			$Route = $this;

			return true;
		}
	}

	public function CreateUrl(array $Path, array $Parameters, &$Url) {
		if(0 < count($Path)) {
			$Node = array_shift($Path);

			$RealRoute = \Framework\Newnorth\GetObject('', $this->RealRoute.'Route');

			if(isset($this->Routes[$Node]) && $this->Routes[$Node]->CreateUrl($Path, $Parameters, $Url)) {
				$Url = $this->CreateUrl_GetReversablePattern($Parameters).$Url;

				return true;
			}
			else if(isset($RealRoute->Routes[$Node]) && $RealRoute->Routes[$Node]->CreateUrl($Path, $Parameters, $Url)) {
				$Url = $this->CreateUrl_GetReversablePattern($Parameters).$Url;

				return true;
			}
			else {
				return false;
			}
		}
		else {
			$Url = $this->CreateUrl_GetReversablePattern($Parameters);

			return true;
		}
	}

	private function CreateUrl_GetReversablePattern($Parameters) {
		if($this->Pattern === null) {
			if($this->RealRoute === null) {
				throw new RuntimeException(
					'Pattern not set.',
					[
						'Route' => $this->FullName,
						'Parameters' => $Parameters,
					]
				);
			}
			else if(!Router::GetRoute($this, $this->RealRoute, $RealRoute)) {
				throw new RuntimeException(
					'Real route not found.',
					[
						'Route' => $this->FullName,
						'Real route' => $this->RealRoute,
					]
				);
			}
			else {
				return $RealRoute->CreateUrl_GetReversablePattern($Parameters);
			}
		}
		else {
			if(0 < count($this->TranslatedReversablePatterns)) {
				if(!isset($Parameters['Locale'])) {
					if(isset($GLOBALS['Config']->Defaults['Locale'][0])) {
						$Parameters['Locale'] = $GLOBALS['Config']->Defaults['Locale'];
					}
					else {
						throw new RuntimeException('Locale not set.');
					}
				}

				if(!isset($this->TranslatedReversablePatterns[$Parameters['Locale']])) {
					throw new RuntimeException('Route not available for current locale.');
				}
				else {
					return $this->CreateUrl_GetReversablePattern_ApplyParameters($this->TranslatedReversablePatterns[$Parameters['Locale']], $Parameters);
				}
			}
			else {
				return $this->CreateUrl_GetReversablePattern_ApplyParameters($this->ReversablePattern, $Parameters);
			}
		}
	}

	private function CreateUrl_GetReversablePattern_ApplyParameters($ReversablePattern, $Parameters) {
		if(0 < preg_match_all('/(^|\/)\+(.*?)(?:\((.*?)\))?(?=\/)/', $ReversablePattern, $Matches, PREG_SET_ORDER)) {
			foreach($Matches as $Match) {
				if(isset($Parameters[$Match[2]])) {
					$ReversablePattern = str_replace($Match[0], $Match[1].$Parameters[$Match[2]], $ReversablePattern);
				}
				else {
					throw new RuntimeException('Parameter "'.$Match[2].'" not set.');
				}
			}
		}

		if(0 < preg_match_all('/(^|\/)\*(.*?)(?:\((.*?)\))?(?=\/)/', $ReversablePattern, $Matches, PREG_SET_ORDER)) {
			foreach($Matches as $Match) {
				if(isset($Parameters[$Match[2]])) {
					$ReversablePattern = str_replace($Match[0], $Match[1].$Parameters[$Match[2]], $ReversablePattern);
				}
				else {
					$ReversablePattern = str_replace($Match[0], $Match[1], $ReversablePattern);
				}
			}
		}

		return $ReversablePattern;
	}
}
?>