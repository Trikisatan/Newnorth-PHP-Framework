<?
namespace Framework\Newnorth;

class Translator {
	/* Static methods */

	public static function EscapeTranslations($Contents) {
		return preg_replace('/(?<!%)%([a-zA-Z0-9_\\/\\\\]+?(?:\("(.*?)"\))?)%(?!%)/', '&#x25;$1&#x25;', $Contents);
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