<?
namespace Framework\Newnorth;

class IntTranslationDataMember extends IntDataMember {
	/* Instance methods */

	public function Set(DataType $DataType, array $Parameters) {
		if($this->IsDynamic) {
			return $this->DataManager->Update(
				$DataType->{$DataType->DataManager->PrimaryKey->Name},
				$Parameters[0],
				[$this->Name => $this->ToDbExpression($Parameters[1])]
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
			return $this->DataManager->Update(
				$DataType->{$DataType->DataManager->PrimaryKey->Name},
				$Parameters[0],
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
			return $this->DataManager->Update(
				$DataType->{$DataType->DataManager->PrimaryKey->Name},
				$Parameters[0],
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