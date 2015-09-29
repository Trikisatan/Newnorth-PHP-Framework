<?
namespace Framework\Newnorth;

class Translator {
	/* Static methods */

	public static function EscapeTranslations($Contents) {
		return str_replace('%', '&#x25;', $Contents);
	}

	public static function GetMissingTranslations($Contents) {
		if(0 < preg_match_all('/(?<!%)%([a-zA-Z0-9_\\/\\\\]+?)(?:\("(.*?)"\))?%(?!%)/', $Contents, $Matches)) {
			return $Matches[0];
		}
		else {
			return [];
		}
	}
}
?>