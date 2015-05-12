<?
namespace Framework\Newnorth;

class HtmlRenderer {
	/* Static methods */

	public static function Render($Object, $PlaceHolder, $Return) {
		$Directory = $Object->_Directory;

		if($PlaceHolder === null) {
			$File = $Object->_Name.'.php.Content.phtml';
		}
		else {
			$File = $Object->_Name.'.php.'.$PlaceHolder.'.phtml';
		}

		if($Object instanceof Control) {
			if($Return) {
				$OldOutput = ob_get_contents();

				ob_clean();

				HtmlRenderer::RenderContents($Object, $Directory, $File);

				$NewOutput = ob_get_contents();

				ob_clean();

				echo $OldOutput;

				return $NewOutput;
			}
			else {
				HtmlRenderer::RenderContents($Object, $Directory, $File);
			}
		}
		else {
			if($Return) {
				$OldOutput = ob_get_contents();

				ob_clean();

				HtmlRenderer::RenderContents(null, $Directory, $File);

				$NewOutput = ob_get_contents();

				ob_clean();

				echo $OldOutput;

				return $NewOutput;
			}
			else {
				HtmlRenderer::RenderContents(null, $Directory, $File);
			}
		}
	}

	private static function RenderContents($Control, $Directory, $File) {
		include($Directory.$File);
	}
}
?>