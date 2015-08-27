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
	}

	public function __toString() {
		return $this->FullName;
	}

	/* Instance methods */

	public function ParseUrl($Url, Route &$Route = null, Route &$RealRoute = null, array &$Parameters) {
		if($this->Pattern === null) {
			if($this->RealRoute === null) {
				return false;
			}
			else if(!Router::GetRoute($this, $this->RealRoute, $RealRoute)) {
				throw new RuntimeException(
					'Real route not found.',
					[
						'Route' => $this->__toString(),
						'Real route' => $this->RealRoute,
					]
				);
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

	private function ParseUrlWithTranslation($Url, Route &$Route = null, Route &$RealRoute = null, array &$Parameters) {
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

	private function ParseUrlWithoutTranslation($Url, Route &$Route = null, Route &$RealRoute = null, array &$Parameters) {
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

	private function ContinueParsingUrl($Url, Route &$Route = null, Route &$RealRoute = null, array &$Parameters) {
		if($Route === null) {
			$PossibleRoutes = $this->Routes;
		}
		else {
			$PossibleRoutes = $Route->Routes;
		}

		foreach($PossibleRoutes as $PossibleRoute) {
			$PossibleParameters = $Parameters;

			if($PossibleRoute->ParseUrl($Url, $Route = null, $RealRoute = null, $PossibleParameters)) {
				$Parameters = $PossibleParameters;

				return true;
			}
		}

		return false;
	}

	private function FinishParsingUrl($Url, Route &$Route = null, Route &$RealRoute = null, array &$Parameters) {
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
					'Current route' => $this->__toString(),
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
						'Route' => $this->__toString(),
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

	public function GetUrl(array $Path, $IsRelative, array $Parameters) {
		if(0 < count($Path)) {
			$Node = array_shift($Path);

			if($Node === '..') {
				return '../'.$this->Parent->GetUrl($Path, true, $Parameters);
			}
			else if($Node === '...') {
				return $this->Parent->GetUrl($Path, true, $Parameters);
			}
			else if(!isset($this->Routes[$Node])) {
				throw new RuntimeException(
					'Subroute not found.',
					[
						'Route' => $this->__toString(),
						'Subroute' => $Node,
					]
				);
			}
			else if($IsRelative) {
				return $this->Routes[$Node]->GetUrl($Path, false, $Parameters);
			}
			else {
				return $this->GetUrl_GetReversablePattern($Parameters).$this->Routes[$Node]->GetUrl($Path, false, $Parameters);
			}
		}
		else if($IsRelative) {
			return '';
		}
		else {
			return $this->GetUrl_GetReversablePattern($Parameters);
		}
	}

	private function GetUrl_GetReversablePattern($Parameters) {
		if($this->Pattern === null) {
			if($this->RealRoute === null) {
				throw new RuntimeException(
					'Pattern not set.',
					[
						'Route' => $this->__toString(),
						'Parameters' => $Parameters,
					]
				);
			}
			else if(!Router::GetRoute($this, $this->RealRoute, $RealRoute)) {
				throw new RuntimeException(
					'Real route not found.',
					[
						'Route' => $this->__toString(),
						'Real route' => $this->RealRoute,
					]
				);
			}
			else {
				return $RealRoute->GetUrl_GetReversablePattern($Parameters);
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
					return $this->GetUrl_GetReversablePattern_ApplyParameters($this->TranslatedReversablePatterns[$Parameters['Locale']], $Parameters);
				}
			}
			else {
				return $this->GetUrl_GetReversablePattern_ApplyParameters($this->ReversablePattern, $Parameters);
			}
		}
	}

	private function GetUrl_GetReversablePattern_ApplyParameters($ReversablePattern, $Parameters) {
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

	public function GetFullUrl(array $Path, array $Parameters) {
		if(0 < count($Path)) {
			$Node = array_shift($Path);

			if($Node === '') {
				return $this->GetFullUrl($Path, $Parameters);
			}
			else if($Node === '..') {
				return $this->Parent->GetFullUrl($Path, $Parameters);
			}
			else if(!isset($this->Routes[$Node])) {
				throw new RuntimeException(
					'Subroute not found.',
					[
						'Route' => $this->__toString(),
						'Subroute' => $Node,
					]
				);
			}
			else {
				return $this->Routes[$Node]->GetFullUrl($Path, $Parameters);
			}
		}
		else if($this->Parent === null) {
			return $this->GetFullUrl_GetReversablePattern($Parameters);
		}
		else {
			return $this->Parent->GetFullUrl($Path, $Parameters).$this->GetFullUrl_GetReversablePattern($Parameters);
		}
	}

	private function GetFullUrl_GetReversablePattern($Parameters) {
		if($this->Pattern === null) {
			if($this->RealRoute === null) {
				throw new RuntimeException(
					'Pattern not set.',
					[
						'Route' => $this->__toString(),
						'Parameters' => $Parameters,
					]
				);
			}
			else if(!Router::GetRoute($this, $this->RealRoute, $RealRoute)) {
				throw new RuntimeException(
					'Real route not found.',
					[
						'Route' => $this->__toString(),
						'Real route' => $this->RealRoute,
					]
				);
			}
			else {
				return $RealRoute->GetFullUrl_GetReversablePattern($Parameters);
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
					return $this->GetFullUrl_GetReversablePattern_ApplyParameters($this->TranslatedReversablePatterns[$Parameters['Locale']], $Parameters);
				}
			}
			else {
				return $this->GetFullUrl_GetReversablePattern_ApplyParameters($this->ReversablePattern, $Parameters);
			}
		}
	}

	private function GetFullUrl_GetReversablePattern_ApplyParameters($ReversablePattern, $Parameters) {
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