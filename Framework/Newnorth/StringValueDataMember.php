<?
namespace Framework\Newnorth;

class StringValueDataMember extends \Framework\Newnorth\AValueDataMember {
	/* Instance methods */

	public function Parse($Value) {
		if($Value === null) {
			return null;
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