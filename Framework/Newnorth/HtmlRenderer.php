<?
namespace Framework\Newnorth;

class HtmlRenderer {
	/* Static methods */

	public static function Render($Object, $PlaceHolder, $Return, $Parameters) {
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

				HtmlRenderer::RenderContents(
					$GLOBALS['Application'],
					$GLOBALS['Layout'],
					$GLOBALS['Page'],
					$Object,
					$Directory,
					$File,
					$Parameters
				);

				$NewOutput = ob_get_contents();

				ob_clean();

				echo $OldOutput;

				return $NewOutput;
			}
			else {
				HtmlRenderer::RenderContents(
					$GLOBALS['Application'],
					$GLOBALS['Layout'],
					$GLOBALS['Page'],
					$Object,
					$Directory,
					$File,
					$Parameters
				);
			}
		}
		else {
			if($Return) {
				$OldOutput = ob_get_contents();

				ob_clean();

				HtmlRenderer::RenderContents(
					$GLOBALS['Application'],
					$GLOBALS['Layout'],
					$GLOBALS['Page'],
					null,
					$Directory,
					$File,
					$Parameters
				);

				$NewOutput = ob_get_contents();

				ob_clean();

				echo $OldOutput;

				return $NewOutput;
			}
			else {
				HtmlRenderer::RenderContents(
					$GLOBALS['Application'],
					$GLOBALS['Layout'],
					$GLOBALS['Page'],
					null,
					$Directory,
					$File,
					$Parameters
				);
			}
		}
	}

	private static function RenderContents($Application, $Layout, $Page, $Control, $Directory, $File, $Parameters) {
		include($Directory.$File);
	}
}
?>