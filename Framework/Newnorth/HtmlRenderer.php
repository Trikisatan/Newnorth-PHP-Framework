<?php
namespace Framework\Newnorth;

class HtmlRenderer {
	/* Methods */
	public static function Render($Control, $Path, $Translations) {
		$Output[0] = ob_get_contents();
		ob_clean();

		HtmlRenderer::RenderContents($Control, $Path);

		$Output[1] = ob_get_contents();
		ob_clean();

		$Translations->Translate($Output[1]);

		echo $Output[0].$Output[1];
	}
	private static function RenderContents($Control, $Path) {
		$Application = Application::GetInstance();
		$Layout = Layout::GetInstance();
		$Page = Page::GetInstance();

		include($Path);
	}
}
?>