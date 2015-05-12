<?
namespace Framework\Newnorth;

class HtmlRenderer {
	/* Static methods */

	public static function Render($Object, $PlaceHolder) {
		$Directory = $Object->_Directory;

		if($PlaceHolder === null) {
			$File = $Object->_Name.'.php.Content.phtml';
		}
		else {
			$File = $Object->_Name.'.php.'.$PlaceHolder.'.phtml';
		}

		if($Object instanceof Control) {
			HtmlRenderer::RenderContents($Object, $Directory, $File);
		}
		else {
			HtmlRenderer::RenderContents(null, $Directory, $File);
		}
	}

	private static function RenderContents($Control, $Directory, $File) {
		include($Directory.$File);
	}
}
?>