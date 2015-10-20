<?
namespace Framework\Newnorth;

abstract class ADataManager {
	/* Instance variables */

	public $DataType;

	public $Connection = null;

	public $Database = null;

	public $Table = null;

	public $PrimaryKey = null;

	public $DataMembers = [];

	public $DataReferences = [];

	public $DataLists = [];

	/* Magic methods */

	public function __call($Function, $Parameters) {
		if(preg_match('/^Update([A-Z][0-9A-Za-z]*)By([A-Z][0-9A-Za-z]*)$/', $Function, $Matches) === 1) {
			$Column = $Matches[1];

			$Expression = $Matches[2];

			if($this->UpdateBy($Column, $Expression, $Parameters)) {
				return true;
			}
		}
		else if(strpos($Function, 'DeleteBy') === 0) {
			if($this->DeleteBy(substr($Function, 8), $Parameters, $Result)) {
				return $Result;
			}
		}
		else if(strpos($Function, 'FindBy') === 0) {
			if($this->FindBy(substr($Function, 6), $Parameters, $Result)) {
				return $Result;
			}
		}
		else if(strpos($Function, 'FindAllBy') === 0) {
			if($this->FindAllBy(substr($Function, 9), $Parameters, $Result)) {
				return $Result;
			}
		}
		else if(strpos($Function, 'CountBy') === 0) {
			if($this->CountBy(substr($Function, 7), $Parameters, $Result)) {
				return $Result;
			}
		}

		throw new RuntimeException(
			'Object method doesn\'t exist.',
			[
				'Function' => $Function,
				'Parameters' => $Parameters,
			]
		);
	}

	public function __toString() {
		return '`'.$this->Database.'`.`'.$this->Table.'`';
	}

	/* Instance methods */

	public abstract function InitializeDataMembers();

	public abstract function InitializeReferenceDataMembers();

	public abstract function InitializeDataReferences();

	public abstract function InitializeDataLists();

	public function AddDataMember(\Framework\Newnorth\ADataMember $DataMember) {
		return $this->DataMembers[$DataMember->Alias] = $DataMember;
	}

	public function AddDataReference(array $Parameters) {
		$DataReference = new \Framework\Newnorth\DataReference($Parameters);

		$this->DataReferences[$DataReference->Alias] = $DataReference;

		return $DataReference;
	}

	public function AddDataList(array $Parameters) {
		$DataList = new \Framework\Newnorth\DataList($Parameters);

		$this->DataLists[$DataList->SingularAlias] = $DataList;

		$this->DataLists[$DataList->PluralAlias] = $DataList;

		return $DataList;
	}

	public abstract function CreateSelectQuery();

	public function InsertByQuery(\Framework\Newnorth\DbInsertQuery $Query, $Source) {
		$Result = $this->Connection->Insert($Query);

		if($Result === false) {
			return false;
		}
		else {
			$Id = $this->Connection->LastInsertId();

			$Item = $this->{'FindBy'.$this->PrimaryKey->Alias}($Id);

			foreach($this->DataMembers as $DataMember) {
				if($DataMember->UseLogInsert) {
					$DataMember->LogInsert($Item, $Source);
				}
			}

			foreach($this->DataLists as $DataList) {
				if($DataList->PluralAlias !== null) {
					$Item->{$DataList->PluralAlias} = [];

					$Item->{'Is'.$DataList->PluralAlias.'Loaded'} = true;
				}
			}

			$this->OnInserted($Item, $Source);

			return $Item;
		}
	}

	public abstract function OnInserted(\Framework\Newnorth\DataType $Item, $Source);

	private function UpdateBy($Column, $Expression, $Parameters) {
		$Value = $Parameters[0];

		array_splice($Parameters, 0, 1);

		if($this->FindAllBy($Expression, $Parameters, $Items)) {
			foreach($Items as $Item) {
				$Item->{'Set'.$Column}($Value);
			}

			return true;
		}
		else {
			return false;
		}
	}

	public function UpdateByQuery(DbUpdateQuery $Query) {
		$Result = $this->Connection->Update($Query);

		if($Result === false) {
			return false;
		}
		else {
			return $this->Connection->AffectedRows();
		}
	}

	public function Delete($Item, $Source) {
		$Item->OnDelete($Source);

		$this->OnDelete($Item, $Source);

		$Query = new \Framework\Newnorth\DbDeleteQuery();

		$Query->AddSource($this);

		$Query->Conditions = new \Framework\Newnorth\DbEqualTo($this->PrimaryKey, $this->PrimaryKey->ToDbExpression($Item->{$this->PrimaryKey->Alias}));

		$Result = $this->Connection->Delete($Query);

		if($Result === false) {
			return false;
		}
		else if($this->Connection->AffectedRows() === 1) {
			foreach($this->DataMembers as $DataMember) {
				if($DataMember->UseLogDelete) {
					$DataMember->LogDelete($Item, $Source);
				}
			}

			$this->OnDeleted($Item, $Source);

			return true;
		}
		else {
			throw new RuntimeException(
				'More than 1 row affected.',
				[]
			);
		}
	}

	public abstract function OnDelete(\Framework\Newnorth\DataType $Item, $Source);

	public abstract function OnDeleted(\Framework\Newnorth\DataType $Item, $Source);

	private function DeleteBy($Expression, $Parameters, &$Result) {
		$DataMembers = explode('And', $Expression);

		for($I = 0; $I < count($DataMembers); ++$I) {
			if(isset($this->DataMembers[$DataMembers[$I]])) {
				$DataMembers[$I] = $this->DataMembers[$DataMembers[$I]];
			}
			else {
				return false;
			}
		}

		$Query = $this->CreateSelectQuery();

		$Query->Conditions = new \Framework\Newnorth\DbAnd();

		foreach($DataMembers as $DataMember) {
			$Query->Conditions->EqualTo($DataMember, $DataMember->ToDbExpression($Parameters[0]));

			array_splice($Parameters, 0, 1);
		}

		$Result = $this->DeleteByQuery(
			$Query,
			$Parameters[0]
		);

		return true;
	}

	public function DeleteByQuery(\Framework\Newnorth\DbSelectQuery $Query, $Source) {
		$Items = $this->FindAllByQuery($Query);

		if(0 < count($Items)) {
			$Result = true;

			foreach($Items as $Item) {
				$Result = $this->Delete($Item, $Source) && $Result;
			}

			return $Result;
		}
		else {
			return false;
		}
	}

	private function FindBy($Expression, $Parameters, &$Result) {
		$DataMembers = explode('And', $Expression);

		for($I = 0; $I < count($DataMembers); ++$I) {
			if(isset($this->DataMembers[$DataMembers[$I]])) {
				$DataMembers[$I] = $this->DataMembers[$DataMembers[$I]];
			}
			else {
				return false;
			}
		}

		$Query = $this->CreateSelectQuery();

		$Query->Conditions = new \Framework\Newnorth\DbAnd();

		foreach($DataMembers as $DataMember) {
			$Query->Conditions->EqualTo($DataMember, $DataMember->ToDbExpression($Parameters[0]));

			array_splice($Parameters, 0, 1);
		}

		if(isset($Parameters[0])) {
			foreach($Parameters[0] as $Sort) {
				$Query->AddSort($Sort['Column'], $Sort['Order']);
			}

			array_splice($Parameters, 0, 1);
		}

		$Result = $this->FindByQuery($Query);

		return true;
	}

	public function FindByQuery(\Framework\Newnorth\DbSelectQuery $Query) {
		$Result = $this->Connection->Find($Query);

		if($Result === false) {
			return null;
		}
		else if($Result->FetchAssoc()) {
			return new $this->DataType($this, $Result->GetProcessedRow());
		}
		else {
			return null;
		}
	}

	private function FindAllBy($Expression, $Parameters, &$Result) {
		$DataMembers = explode('And', $Expression);

		for($I = 0; $I < count($DataMembers); ++$I) {
			if(isset($this->DataMembers[$DataMembers[$I]])) {
				$DataMembers[$I] = $this->DataMembers[$DataMembers[$I]];
			}
			else {
				return false;
			}
		}

		$Query = $this->CreateSelectQuery();

		$Query->Conditions = new \Framework\Newnorth\DbAnd();

		foreach($DataMembers as $DataMember) {
			$Query->Conditions->EqualTo($DataMember, $DataMember->ToDbExpression($Parameters[0]));

			array_splice($Parameters, 0, 1);
		}

		if(isset($Parameters[0])) {
			foreach($Parameters[0] as $Sort) {
				$Query->AddSort($Sort['Column'], $Sort['Order']);
			}

			array_splice($Parameters, 0, 1);
		}

		$Result = $this->FindAllByQuery($Query);

		return true;
	}

	public function FindAllByQuery(\Framework\Newnorth\DbSelectQuery $Query) {
		$Result = $this->Connection->FindAll($Query);

		if($Result === false) {
			return [];
		}
		else {
			$Items = [];

			while($Result->FetchAssoc()) {
				$Items[] = new $this->DataType($this, $Result->GetProcessedRow());
			}

			return $Items;
		}
	}

	public function Count() {
		return $this->CountByQuery($this->CreateSelectQuery());
	}

	private function CountBy($Expression, $Parameters, &$Result) {
		$DataMembers = explode('And', $Expression);

		for($I = 0; $I < count($DataMembers); ++$I) {
			if(isset($this->DataMembers[$DataMembers[$I]])) {
				$DataMembers[$I] = $this->DataMembers[$DataMembers[$I]];
			}
			else {
				return false;
			}
		}

		$Query = $this->CreateSelectQuery();

		$Query->Conditions = new \Framework\Newnorth\DbAnd();

		foreach($DataMembers as $DataMember) {
			$Query->Conditions->EqualTo($DataMember, $DataMember->ToDbExpression($Parameters[0]));

			array_splice($Parameters, 0, 1);
		}

		$Result = $this->CountByQuery($Query);

		return true;
	}

	public function CountByQuery(\Framework\Newnorth\DbSelectQuery $Query) {
		return $this->Connection->Count($Query);
	}
}
?>