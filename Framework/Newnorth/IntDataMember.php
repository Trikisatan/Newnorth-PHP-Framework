<?
namespace Framework\Newnorth;

class IntDataMember extends DataMember {
	/* Instance methods */

	public function Parse($Value) {
		if($Value === null) {
			return null;
		}
		else {
			return (int)$Value;
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

	public function Decrement(DataType $DataType, array $Parameters) {
		if($this->IsDynamic) {
			$PrimaryKey = $this->DataManager->PrimaryKey->Name;

			return $this->DataManager->UpdateByArray(
				new \Framework\Newnorth\DbEqualTo('`'.$PrimaryKey.'`', $DataType->$PrimaryKey),
				[$this->Name => '`'.$this->Name.'` - 1']
			);
		}
		else {
			throw new RuntimeException(
				'Data member is static.',
				['Data member' => $this->Name, 'Data type' => $DataType, 'Parameters' => $Parameters]
			);
		}
	}

	public function Increment(DataType $DataType, array $Parameters) {
		if($this->IsDynamic) {
			$PrimaryKey = $this->DataManager->PrimaryKey->Name;

			return $this->DataManager->UpdateByArray(
				new \Framework\Newnorth\DbEqualTo('`'.$PrimaryKey.'`', $DataType->$PrimaryKey),
				[$this->Name => '`'.$this->Name.'` + 1']
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