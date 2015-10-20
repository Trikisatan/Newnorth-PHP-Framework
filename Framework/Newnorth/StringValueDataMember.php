<?
namespace Framework\Newnorth;

class StringValueDataMember extends \Framework\Newnorth\AValueDataMember {
	/* Instance methods */

	public function Parse($Value) {
		if($Value === null) {
			return null;
		}
		else if(is_bool($Value)) {
			return $Value ? '1' : '0';
		}
		else {
			return (string)$Value;
		}
	}

	public function ToDbExpression($Value) {
		if($Value === null) {
			return null;
		}
		else {
			return '"'.$this->Parse($Value).'"';
		}
	}
}
?>