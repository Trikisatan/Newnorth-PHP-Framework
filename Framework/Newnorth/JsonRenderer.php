<?
namespace Framework\Newnorth;

class JsonRenderer {
	/* Static methods */

	public static function Render($Object, $Variable) {
		if($Variable === null) {
			$Variable = 'Data';
		}

		$Output[0] = ob_get_contents();

		ob_clean();

		echo json_encode($Object->$Variable);

		$Output[1] = ob_get_contents();

		ob_clean();

		$Object->_Translations->Translate($Output[1]);

		echo $Output[0].$Output[1];
	}
}
?>