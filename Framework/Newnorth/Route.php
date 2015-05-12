<?
namespace Framework\Newnorth;

class Route {
	/* Instance variables */

	public $Parent;

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
		else {
			$this->Pattern = '/^'.str_replace('/', '\/', $Data['Pattern']).'\/(.*?)$/';
		}

		$this->Pattern = preg_replace('/(\^|\\\\\/)\*(.*?)(?:\((.*?)\))?\\\\\//', '$1(?:(?<$2>$3)\/)?', $this->Pattern);

		$this->Pattern = preg_replace('/(\^|\\\\\/)\+(.*?)(?:\((.*?)\))?\\\\\//', '$1(?<$2>$3)\/', $this->Pattern);

		if(isset($Data['Translations'])) {
			foreach($Data['Translations'] as $Locale => $Translation) {
				$this->TranslatedPatterns[$Locale] = str_replace('@', str_replace('/', '\/', $Translation), $this->Pattern);
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
		else {
			$this->ReversablePattern = $Data['Pattern'].'/';
		}

		if(isset($Data['Translations'])) {
			foreach($Data['Translations'] as $Locale => $Translation) {
				$this->TranslatedReversablePatterns[$Locale] = str_replace('@', str_replace('/', '\/', $Translation), $this->ReversablePattern);
			}
		}

		$this->Parameters = isset($Data['Parameters']) ? $Data['Parameters'] : [];

		if(!isset($this->Parameters['Application'][0])) {
			$this->Parameters['Application'] = 'Default';
		}

		if(!isset($this->Parameters['Layout'][0])) {
			$this->Parameters['Layout'] = 'Default';
		}

		$this->Parameters['Page'] = $this->FullName;

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

	public function ParseUrl($Url, Route &$Route = null, array &$Parameters) {
		if(0 < count($this->TranslatedPatterns)) {
			foreach($this->TranslatedPatterns as $Locale => $Pattern) {
				if(isset($Locale[0], $Parameters["Locale"]) && $Locale !== $Parameters["Locale"]) {
					continue;
				}

				if(preg_match($Pattern, $Url, $Matches) === 1) {
					end($Matches);

					$Url = preg_replace($Pattern, '$'.key($Matches), $Url);

					$NewParameters = $Parameters;

					// If a locale is used for this route, add it to its parameters.
					$NewParameters["Locale"] = $Locale;

					// Add new parameters obtained via the pattern.
					foreach($Matches as $Key => $Value) {
						if(isset($Value[0]) && !is_int($Key)) {
							$NewParameters[$Key] = $Value;
						}
					}

					if(isset($Url[0])) {
						foreach($this->Routes as $PossibleRoute) {
							if($PossibleRoute->ParseUrl($Url, $Route, $NewParameters)) {
								$Parameters = $NewParameters;

								return true;
							}
						}
					}
					else {
						$Route = $this;

						$Parameters = $NewParameters;

						foreach($this->Parameters as $ParameterName => $ParameterValue) {
							if(!isset($Parameters[$ParameterName])) {
								$Parameters[$ParameterName] = $ParameterValue;
							}
						}

						return true;
					}
				}
			}

			return false;
		}
		else if(preg_match($this->Pattern, $Url, $Matches) === 1) {
			end($Matches);

			$Url = preg_replace($this->Pattern, '$'.key($Matches), $Url);

			// Create and use a copy of the parameters in case this route
			// doesn't prove to be correct in the end, so we easily can switch back.
			$NewParameters = $Parameters;

			// Add new parameters obtained via the pattern.
			foreach($Matches as $Key => $Value) {
				if(isset($Value[0]) && !is_int($Key)) {
					$NewParameters[$Key] = $Value;
				}
			}

			if(isset($Url[0])) {
				foreach($this->Routes as $PossibleRoute) {
					if($PossibleRoute->ParseUrl($Url, $Route, $NewParameters)) {
						$Parameters = $NewParameters;

						return true;
					}
				}

				return false;
			}
			else {
				$Route = $this;

				$Parameters = $NewParameters;

				foreach($this->Parameters as $ParameterName => $ParameterValue) {
					if(!isset($Parameters[$ParameterName])) {
						$Parameters[$ParameterName] = $ParameterValue;
					}
				}

				if(!isset($Parameters['Locale']) && isset($GLOBALS['Config']->Defaults['Locale'][0])) {
					$Parameters['Locale'] = $GLOBALS['Config']->Defaults['Locale'];
				}

				return true;
			}
		}
		else {
			return false;
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

	private function GetUrl_GetReversablePattern_ApplyParameters($ReversablePattern, $Parameters) {
		if(0 < preg_match_all('/(^|\/)\+(.*?)(?:\((.*?)\))?\//', $ReversablePattern, $Matches, PREG_SET_ORDER)) {
			foreach($Matches as $Match) {
				if(isset($Parameters[$Match[2]])) {
					$ReversablePattern = str_replace($Match[0], $Match[1].$Parameters[$Match[2]].'/', $ReversablePattern);
				}
				else {
					throw new RuntimeException('Parameter "'.$Match[2].'" not set.');
				}
			}
		}

		if(0 < preg_match_all('/(^|\/)\*(.*?)(?:\((.*?)\))?\//', $ReversablePattern, $Matches, PREG_SET_ORDER)) {
			foreach($Matches as $Match) {
				if(isset($Parameters[$Match[2]])) {
					$ReversablePattern = str_replace($Match[0], $Match[1].$Parameters[$Match[2]].'/', $ReversablePattern);
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