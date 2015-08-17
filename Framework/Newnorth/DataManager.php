<?
namespace Framework\Newnorth;

abstract class DataManager {
	/* Instance variables */

	public $DataType = null;

	public $Connection = null;

	protected $Database = null;

	protected $Table = null;

	public $PrimaryKey = null;

	public $DataMembers = [];

	/* Instance methods */

	public function AddBoolDataMember($Name, $IsDynamic) {
		return $this->DataMembers[$Name] = new BoolDataMember($this, $Name, $IsDynamic);
	}

	public function AddBoolTranslationDataMember(TranslationDataManager $DataManager, $Name, $IsDynamic) {
		return $this->DataMembers[$Name] = new BoolTranslationDataMember($DataManager, $Name, $IsDynamic);
	}

	public function AddFloatDataMember($Name, $IsDynamic) {
		return $this->DataMembers[$Name] = new FloatDataMember($this, $Name, $IsDynamic);
	}

	public function AddFloatTranslationDataMember(TranslationDataManager $DataManager, $Name, $IsDynamic) {
		return $this->DataMembers[$Name] = new FloatTranslationDataMember($DataManager, $Name, $IsDynamic);
	}

	public function AddIntDataMember($Name, $IsDynamic) {
		return $this->DataMembers[$Name] = new IntDataMember($this, $Name, $IsDynamic);
	}

	public function AddIntTranslationDataMember(TranslationDataManager $DataManager, $Name, $IsDynamic) {
		return $this->DataMembers[$Name] = new IntTranslationDataMember($DataManager, $Name, $IsDynamic);
	}

	public function AddStringDataMember($Name, $IsDynamic) {
		return $this->DataMembers[$Name] = new StringDataMember($this, $Name, $IsDynamic);
	}

	public function AddStringTranslationDataMember(TranslationDataManager $DataManager, $Name, $IsDynamic) {
		return $this->DataMembers[$Name] = new StringTranslationDataMember($DataManager, $Name, $IsDynamic);
	}

	public function InsertByQuery(DbInsertQuery $Query) {
		$Result = $this->Connection->Insert($Query);

		if($Result === false) {
			return false;
		}
		else {
			return $this->Connection->LastInsertId();
		}
	}

	public function InsertByArray(array $Data) {
		$Query = new \Framework\Newnorth\DbInsertQuery();

		$Query->Source = '`'.$this->Database.'`.`'.$this->Table.'`';

		foreach($Data as $Column => $Value) {
			$Query->AddColumn('`'.$Column.'`');

			$Query->AddValue($Value);
		}

		return $this->InsertByQuery($Query);
	}

	public function InsertUpdateByQuery(DbInsertUpdateQuery $Query) {
		$Result = $this->Connection->InsertUpdate($Query);

		if($Result === false) {
			return false;
		}
		else {
			return $this->Connection->LastInsertId();
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

	public function UpdateByArray(DbCondition $Conditions = null, array $Changes) {
		$Query = new \Framework\Newnorth\DbUpdateQuery();

		$Query->AddSource('`'.$this->Database.'`.`'.$this->Table.'`');

		$Query->Conditions = $Conditions;

		foreach($Changes as $Column => $Value) {
			$Query->AddChange('`'.$Column.'`', $Value);
		}

		return $this->Connection->Update($Query);
	}

	public function DeleteByQuery(DbDeleteQuery $Query) {
		$Result = $this->Connection->Delete($Query);

		if($Result === false) {
			return false;
		}
		else {
			return $this->Connection->AffectedRows();
		}
	}

	public function FindByQuery(DbSelectQuery $Query) {
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

	public function FindAllByQuery(DbSelectQuery $Query) {
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

	public function CountByQuery(DbSelectQuery $Query) {
		return $this->Connection->Count($Query);
	}
}
?>