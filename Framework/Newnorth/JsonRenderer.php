<?
namespace Framework\Newnorth;

class JsonRenderer {
	/* Static methods */

	public static function Render($Object, $Placeholder, $Return, $Parameters) {
		if($Return) {
			return json_encode($Object->_Data);
		}
		else {
			echo json_encode($Object->_Data);
		}
	}
}
?>