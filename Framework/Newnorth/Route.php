<?
namespace Framework\Newnorth;

class Route {
	/* Instance variables */

	public $Name;

	public $Pattern;

	public $ReversablePattern;

	public $Defaults;

	public $Translations;

	/* Magic methods */

	public function __construct($Name, $Pattern, $Requirements, $Translations, $Defaults) {
		$this->Name = $Name;

		$this->Pattern = preg_replace('/(\/)\+\@?([^\/]+)(?=\/|$)/', '(?:\1(?<\2>.+))', $Pattern);
		$this->Pattern = preg_replace('/(\/)\*\@?([^\/]+)(?=\/|$)/', '(?:\1(?<\2>.*))?', $this->Pattern);
		foreach($Requirements as $Key => $Value) {
			$this->Pattern = preg_replace('/(\<'.$Key.'\>)\.\+/', '\1'.$Value, $this->Pattern);
			$this->Pattern = preg_replace('/(\<'.$Key.'\>)\.\*/', '\1'.$Value, $this->Pattern);
		}
		$this->Pattern = preg_replace('/(\/)\*(\/|$)/', '\1(.*?)\2', $this->Pattern);
		$this->Pattern = preg_replace('/(\/)\+(\/|$)/', '\1(.+?)\2', $this->Pattern);
		$this->Pattern = '/^'.str_replace('/', '\/', $this->Pattern).'$/';

		// Turn "/+@Var/" into "/+Var/"
		$this->ReversablePattern = preg_replace('/(\/\+)\@?([^\/]+)/', '\1\2', $Pattern);
		// Turn "/*@Var/" into "/*Var/"
		$this->ReversablePattern = preg_replace('/(\/\*)\@?([^\/]+)/', '\1\2', $this->ReversablePattern);

		$this->Defaults = $Defaults;
		$this->Translations = $Translations;
	}

	public function __toString() {
		return $this->Name;
	}

	/* Instance methods */

	public function GetName() {
		return $this->Name;
	}

	public function Match($Url, &$Parameters) {
		return 0 < preg_match($this->Pattern, $Url, $Parameters);
	}

	public function SetDefaults(&$Parameters) {
		foreach($this->Defaults as $ParameterName => $ParameterValue) {
			if(!isset($Parameters[$ParameterName])) {
				$Parameters[$ParameterName] = $ParameterValue;
			}
		}
	}

	public function Translate(&$Parameters, $Locale) {
		if(isset($this->Translations[$Locale])) {
			foreach($this->Translations[$Locale] as $ParameterName => $Translations) {
				$ParameterValue = isset($Parameters[$ParameterName]) ? $Parameters[$ParameterName] : '';

				$IsRequired = isset($Parameters[$ParameterName]);

				$IsUpdated = false;

				foreach($Translations as $UpdatedValue => $OriginalValue) {
					if($ParameterValue === $OriginalValue) {
						$ParameterValue = $UpdatedValue;

						$IsUpdated = true;

						break;
					}
				}

				if($IsUpdated) {
					$Parameters[$ParameterName] = $ParameterValue;
				}
				else if($IsRequired) {
					return false;
				}
			}
		}

		return true;
	}

	public function ReversedTranslate(&$Data, $Locale) {
		if(!isset($this->Translations[$Locale])) {
			return;
		}

		foreach($Data as $Key => $Value) {
			if(substr($Key, -1) === '?') {
				$Key = substr($Key, 0, -1);
			}

			if(isset($this->Translations[$Locale][$Key][$Value])) {
				$Data[$Key] = $this->Translations[$Locale][$Key][$Value];
			}
		}
	}

	public function ReversedMatch($Parameters, $Locale, &$Url) {
		$Url = $this->ReversablePattern;

		foreach($Parameters as $ParameterName => $ParameterValue) {
			if(substr($ParameterName, -1) === '?') {
				$ParameterName = substr($ParameterName, 0, -1);
			}

			if(isset($this->Translations[$Locale][$ParameterName][$ParameterValue])) {
				$ParameterValue = $this->Translations[$Locale][$ParameterName][$ParameterValue];
			}

			if(isset($ParameterValue[0])) {
				$Url = preg_replace('/\/(?:\+|\*)'.$ParameterName.'\//', '/'.$ParameterValue.'/', $Url);
			}
			else {
				$Url = preg_replace('/\/\*'.$ParameterName.'\//', '/', $Url);
			}
		}

		$Url = preg_replace('/\/(?:\+|\*)Locale\//', '/'.$Locale.'/', $Url);
		$Url = preg_replace('/\/(?:\*)(?:[^\/]+)(?:\/|$)/', '/', $Url);
		$Url = preg_replace('/\/(?:\*)(?:\/|$)/', '/', $Url);

		if($this->Match($Url, $Match)) {
			if($this->Translate($Match, $Locale)) {
				$this->SetDefaults($Match);

				foreach($Parameters as $Key => $Value) {
					$IsRequired = true;

					if(substr($Key, -1) === '?') {
						$Key = substr($Key, 0, -1);

						$IsRequired = false;
					}

					if(isset($Match[$Key])) {
						if($Match[$Key] !== $Value) {
							return false;
						}
					}
					else if($IsRequired) {
						return false;
					}
				}

				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}
}
?>