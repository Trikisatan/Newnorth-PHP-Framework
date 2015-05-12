<?
namespace Framework\Newnorth;

class JsonRenderer {
	/* Static methods */

	public static function Render($Object, $Placeholder) {
		echo json_encode($Object->_Data);
	}
}
?>