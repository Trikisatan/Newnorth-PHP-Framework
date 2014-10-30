<?php
namespace Framework\Newnorth;

class HtmlRenderer {
	/* Methods */
	public static function Render($Object, $PlaceHolder) {
		$Directory = $Object->Directory.$Object->Name;

		if($PlaceHolder === null) {
			$File = 'Content.phtml';
		}
		else {
			$File = $PlaceHolder.'.phtml';
		}

		$Output[0] = ob_get_contents();
		ob_clean();

		if($Object instanceof Control) {
			HtmlRenderer::RenderContents($Object, $Directory, $File);
		}
		else {
			HtmlRenderer::RenderContents(null, $Directory, $File);
		}

		$Output[1] = ob_get_contents();
		ob_clean();

		$Object->Translations->Translate($Output[1]);

		echo $Output[0].$Output[1];
	}
	private static function RenderContents($Control, $Directory, $File) {
		global $Application, $Layout, $Page;

		include($Directory.'/'.$File);
	}
}
?>