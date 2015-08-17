<?
namespace Framework\Newnorth;

class FloatDataMember extends DataMember {
	/* Instance methods */

	public function Parse($Value) {
		return (float)$Value;
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