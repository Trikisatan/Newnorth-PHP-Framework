<?
namespace Framework\Newnorth\DataMembers;

class FloatValueDataMember extends \Framework\Newnorth\DataMembers\AValueDataMember {
	/* Instance methods */

	public function Parse($Value) {
		if($Value === null) {
			return null;
		}
		else {
			return (float)$Value;
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