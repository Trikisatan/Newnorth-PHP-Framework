<?
namespace Framework\Newnorth;

abstract class DataManager {
	/* Instance variables */

	public $DataType = null;

	public $Connection = null;

	/* Instance methods */

	public function InsertByQuery(DbInsertQuery $Query) {
		$Result = $this->Connection->Insert($Query);

		if($Result) {
			return $this->Connection->LastInsertId();
		}
		else {
			return false;
		}
	}

	public function DeleteByQuery(DbDeleteQuery $Query) {
		$Result = $this->Connection->Delete($Query);

		if($Result) {
			return $this->Connection->AffectedRows();
		}
		else {
			return false;
		}
	}

	public function FindByQuery(DbSelectQuery $Query) {
		$Result = $this->Connection->Find($Query);

		if($Result === false) {
			return null;
		}

		if($Result->FetchAssoc()) {
			return new $this->DataType($Result->GetProcessedRow());
		}

		return null;
	}

	public function FindAllByQuery(DbSelectQuery $Query) {
		$Result = $this->Connection->FindAll($Query);

		if($Result === false) {
			return [];
		}

		$Items = [];

		while($Result->FetchAssoc()) {
			$Items[] = new $this->DataType($Result->GetProcessedRow());
		}

		return $Items;
	}

	public function CountByQuery(DbSelectQuery $Query) {
		return $this->Connection->Count($Query);
	}
}
?>