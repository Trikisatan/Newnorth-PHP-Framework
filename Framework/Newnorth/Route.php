<?
namespace Framework\Newnorth;

class Route {
	/* Variables */

	private $Name;

	private $Pattern;

	private $ReversablePattern;

	private $Defaults;

	private $Translations;

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

	/* Methods */

	public function GetName() {
		return $this->Name;
	}

	public function Match($Subject, &$Match) {
		if(0 < preg_match($this->Pattern, $Subject, $Match)) {
			$this->SetDefaults($Match);
			return true;
		}

		return false;
	}

	public function SetDefaults(&$Data) {
		foreach($this->Defaults as $Key => $Value) {
			if(!isset($Data[$Key])) {
				$Data[$Key] = $Value;
			}
		}
	}

	public function Translate(&$Data, $Locale) {
		// If there's no translations, no translation is needed.
		if(count($this->Translations) == 0) {
			return true;
		}
		// If there's translations, translation is needed.
		else if(!isset($this->Translations[$Locale])) {
			return false;
		}
		else {
			foreach($this->Translations[$Locale] as $Variable => $Translations) {
				if(!isset($Data[$Variable])) {
					continue;
				}

				$IsUpdated = false;

				foreach($Translations as $Translation => $Value) {
					if($Data[$Variable] === $Value) {
						$Data[$Variable] = $Translation;
						$IsUpdated = true;
						break;
					}
				}

				if(!$IsUpdated) {
					return false;
				}
			}

			return true;
		}
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

		foreach($Parameters as $Part => $Value) {
			$Url = preg_replace('/\/(?:\+|\*)'.$Part.'\//', '/'.$Value.'/', $Url);
		}

		$Url = preg_replace('/\/(?:\+|\*)Locale\//', '/'.$Locale.'/', $Url);
		$Url = preg_replace('/\/(?:\*)(?:[^\/]+)(?:\/|$)/', '/', $Url);
		$Url = preg_replace('/\/(?:\*)(?:\/|$)/', '/', $Url);

		if(!$this->Match($Url, $Match)) {
			return false;
		}

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
}
?>