<?
namespace Framework\Newnorth;

class DataList {
	/* Instance variables */

	public $LocalKey;

	public $ForeignDataManager;

	public $ForeignKey;

	public $ForeignPrimaryKey;

	public $SingularAlias;

	public $PluralAlias;

	public $Sorts = [];

	public $OnDelete = null;

	/* Magic methods */

	public function __construct($Parameters) {
		$this->LocalKey = $Parameters['LocalKey'];

		$this->ForeignDataManager = $Parameters['ForeignDataManager'];

		$this->ForeignPrimaryKey = $Parameters['ForeignDataManager']->PrimaryKey;

		$this->ForeignKey = $Parameters['ForeignKey'];

		$this->SingularAlias = $Parameters['SingularAlias'];

		$this->PluralAlias = $Parameters['PluralAlias'];

		if(isset($Parameters['Sorts'])) {
			$this->Sorts = $Parameters['Sorts'];
		}

		if(isset($Parameters['OnDelete'])) {
			$this->OnDelete = $Parameters['OnDelete'];
		}
	}

	/* Instance methods */

	public function Initialize(\Framework\Newnorth\DataType $DataType) {
		$DataType->{$this->PluralAlias} = null;

		$DataType->{'Is'.$this->PluralAlias.'Loaded'} = false;
	}

	public function Load(\Framework\Newnorth\DataType $DataType) {
		if(!$DataType->{'Is'.$this->PluralAlias.'Loaded'}) {
			$DataType->{$this->PluralAlias} = $this->ForeignDataManager->{'FindAllBy'.$this->ForeignKey->Name}($DataType->{$this->LocalKey->Name}, $this->Sorts);

			$DataType->{'Is'.$this->PluralAlias.'Loaded'} = true;
		}

		return 0 < count($DataType->{$this->PluralAlias});
	}

	public function Create(\Framework\Newnorth\DataType $DataType, array $Data) {
		$this->Load($DataType);

		$Data[$this->ForeignKey->Name] = $DataType->{$this->LocalKey->Name};

		foreach($Data as $Key => $Value) {
			$Data[$Key] = $this->ForeignDataManager->DataMembers[$Key]->ToDbExpression($Value);
		}

		$Id = $this->ForeignDataManager->InsertByArray($Data);

		$Item = $this->ForeignDataManager->{'FindBy'.$this->ForeignPrimaryKey->Name}($Id);

		foreach($this->ForeignDataManager->DataLists as $DataList) {
			$Item->{$DataList->PluralAlias} = [];

			$Item->{'Is'.$DataList->PluralAlias.'Loaded'} = true;
		}

		$DataType->{$this->PluralAlias}[] = $Item;

		return $Item;
	}

	public function Delete(\Framework\Newnorth\DataType $DataType, \Framework\Newnorth\DataType $Item) {
		$this->Load($DataType);

		$this->ForeignDataManager->{'DeleteBy'.$this->ForeignPrimaryKey->Name}($Item->{$this->ForeignPrimaryKey->Name});

		$Index = $this->IndexOf($DataType, $Item);

		array_splice($DataType->{$this->PluralAlias}, $Index, 1);
	}

	public function Remove(\Framework\Newnorth\DataType $DataType, \Framework\Newnorth\DataType $Item) {
		$this->Load($DataType);

		$Item->{'Set'.$this->ForeignKey->Name}(null);

		$Index = $this->IndexOf($DataType, $Item);

		array_splice($DataType->{$this->PluralAlias}, $Index, 1);
	}

	public function IndexOf(\Framework\Newnorth\DataType $DataType, \Framework\Newnorth\DataType $Item) {
		$this->Load($DataType);

		for($I = 0; $I < count($DataType->{$this->PluralAlias}); ++$I) {
			if($DataType->{$this->PluralAlias}[$I]->{$this->ForeignPrimaryKey->Name} === $Item->{$this->ForeignPrimaryKey->Name}) {
				return $I;
			}
		}

		return -1;
	}

	public function IndexOfBy(\Framework\Newnorth\DataType $DataType, array $DataMembers, array $Values) {
		$this->Load($DataType);

		for($I = 0; $I < count($DataType->{$this->PluralAlias}); ++$I) {
			$IsFound = true;

			for($J = 0; $J < count($DataMembers); ++$J) {
				if($DataType->{$this->PluralAlias}[$I]->{$DataMembers[$J]->Name} !== $Values[$J]) {
					$IsFound = false;

					break;
				}
			}

			if($IsFound) {
				return $I;
			}
		}

		return -1;
	}

	public function Has(\Framework\Newnorth\DataType $DataType, \Framework\Newnorth\DataType $Item) {
		$this->Load($DataType);

		for($I = 0; $I < count($DataType->{$this->PluralAlias}); ++$I) {
			if($DataType->{$this->PluralAlias}[$I]->{$this->ForeignPrimaryKey->Name} === $Item->{$this->ForeignPrimaryKey->Name}) {
				return true;
			}
		}

		return false;
	}

	public function HasBy(\Framework\Newnorth\DataType $DataType, array $DataMembers, array $Values) {
		$this->Load($DataType);

		for($I = 0; $I < count($DataType->{$this->PluralAlias}); ++$I) {
			$IsFound = true;

			for($J = 0; $J < count($DataMembers); ++$J) {
				if($DataType->{$this->PluralAlias}[$I]->{$DataMembers[$J]->Name} !== $Values[$J]) {
					$IsFound = false;

					break;
				}
			}

			if($IsFound) {
				return true;
			}
		}

		return false;
	}

	public function CountBy(\Framework\Newnorth\DataType $DataType, array $DataMembers, array $Values) {
		$this->Load($DataType);

		$Count = 0;

		for($I = 0; $I < count($DataType->{$this->PluralAlias}); ++$I) {
			$IsFound = true;

			for($J = 0; $J < count($DataMembers); ++$J) {
				if($DataType->{$this->PluralAlias}[$I]->{$DataMembers[$J]->Name} !== $Values[$J]) {
					$IsFound = false;

					break;
				}
			}

			if($IsFound) {
				++$Count;
			}
		}

		return $Count;
	}
}
?>