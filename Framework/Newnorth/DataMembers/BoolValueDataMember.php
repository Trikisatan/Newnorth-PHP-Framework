<?
namespace Framework\Newnorth\DataMembers;

class BoolValueDataMember extends \Framework\Newnorth\DataMembers\AValueDataMember {
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