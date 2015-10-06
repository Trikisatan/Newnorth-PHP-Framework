<?
namespace Framework\Newnorth;

class Translations {
	/* Instance variables */

	public $FilePath;

	public $Items = [];

	/* Magic methods */

	public function __construct($FilePath = 'Translations.ini') {
		$this->FilePath = $FilePath;
	}

	/* Instance methods */

	public function Initialize() {
		if(file_exists($this->FilePath)) {
			$this->Items = ParseIniFile($this->FilePath, false, true);
		}
	}

	public function Translate(&$Contents) {
		do {
			$Changes = 0;

			$Targets = $this->FindTargets($Contents);

			foreach($Targets as $Target) {
				if($this->ProcessTarget($Contents, $Target)) {
					++$Changes;
				}
			}
		} while(0 < $Changes);
	}

	private function FindTargets($Contents) {
		if(0 < preg_match_all('/(?<!%)%(.+?)%(?!%)/', $Contents, $Matches)) {
			$Targets = [];

			foreach($Matches[1] as $Target) {
				if(!in_array($Target, $Targets, true)) {
					$Targets[] = $Target;
				}
			}

			return $Targets;
		}
		else {
			return [];
		}
	}

	private function ProcessTarget(&$Contents, $Target) {
		$Options = explode('|', $Target);

		foreach($Options as $Option) {
			if(preg_match('/^([^\(\)]+?)(?:\((?:\\\\?"|&quot;)(.*?)(?:\\\\?"|&quot;)(?:, ?(?:\\\\?"|&quot;)"(.*?)(?:\\\\?"|&quot;)")*\))?$/', $Option, $Match) === 1) {
				$Translation = $Match[1];

				$Translation = str_replace('\\/', '/', $Translation);

				if(isset($this->Items[$Translation])) {
					$Translation = $this->Items[$Translation];

					for($I = 2; $I < count($Match); ++$I) {
						$Translation = str_replace('$'.($I - 2), $Match[$I], $Translation);
					}

					$Contents = preg_replace('/(?<!%)%'.preg_quote($Target, '/').'%(?!%)/', $Translation, $Contents);

					return true;
				}
			}
		}

		return false;
	}
}
?>