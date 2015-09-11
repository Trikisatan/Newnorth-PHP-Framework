<?
namespace Framework\Newnorth;

class BoolValueDataMember extends \Framework\Newnorth\AValueDataMember {
	/* Instance methods */

	public function Parse($Value) {
		if($Value === null) {
			return null;
		}
		else if(is_bool($Value)) {
			return $Value;
		}
		else if(is_string($Value)) {
			return $Value === '1';
		}
		else {
			return (bool)$Value;
		}
	}

	public function ToDbExpression($Value) {
		if($Value === null) {
			return null;
		}
		else {
			return $this->Parse($Value);
		}
	}
}
?>