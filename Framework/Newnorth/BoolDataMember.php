<?
namespace Framework\Newnorth;

class BoolDataMember extends DataMember {
	/* Instance methods */

	public function Parse($Value) {
		if(is_bool($Value)) {
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
		return $this->Parse($Value);
	}

	public function Set(DataType $DataType, array $Parameters) {
		if($this->IsDynamic) {
			$PrimaryKey = $this->DataManager->PrimaryKey->Name;

			return $this->DataManager->UpdateByArray(
				new \Framework\Newnorth\DbEqualTo('`'.$PrimaryKey.'`', $DataType->$PrimaryKey),
				[$this->Name => $this->ToDbExpression($Parameters[0])]
			);
		}
		else {
			throw new RuntimeException(
				'Data member is static.',
				['Data member' => $this->Name, 'Data type' => $DataType, 'Parameters' => $Parameters]
			);
		}
	}
}
?>