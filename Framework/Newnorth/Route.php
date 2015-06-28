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

	public function ParseUrl($Url, Route &$Route = null, Route &$RealRoute = null, array &$Parameters) {
		if($this->Pattern !== null) {
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
								if($PossibleRoute->ParseUrl($Url, $Route, $RealRoute, $NewParameters)) {
									$Parameters = $NewParameters;

									return true;
								}
							}
						}
						else {
							$Route = $this;

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

							$Parameters = $NewParameters;

							$Parameters['Route'] = $Route->FullName;

							$Parameters['RealRoute'] = $RealRoute->FullName;

							foreach($RealRoute->Parameters as $ParameterName => $ParameterValue) {
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
						if($PossibleRoute->ParseUrl($Url, $Route, $RealRoute, $NewParameters)) {
							$Parameters = $NewParameters;

							return true;
						}
					}

					return false;
				}
				else {
					$Route = $this;

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

					$Parameters = $NewParameters;

					$Parameters['Route'] = $Route->FullName;

					$Parameters['RealRoute'] = $RealRoute->FullName;

					foreach($RealRoute->Parameters as $ParameterName => $ParameterValue) {
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
		else {
			return false;
		}
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
			throw new RuntimeException(
				'Pattern not set.',
				[
					'Route' => $this->__toString(),
					'Parameters' => $Parameters,
				]
			);
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
			throw new RuntimeException(
				'Pattern not set.',
				[
					'Route' => $this->__toString(),
					'Parameters' => $Parameters,
				]
			);
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