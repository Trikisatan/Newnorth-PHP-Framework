<?
namespace Framework\Newnorth;

abstract class DataManager {
	/* Instance variables */

	public $DataType = null;

	public $Connection = null;

	/* Instance methods */

	public function InsertByQuery(DbInsertQuery $Query) {
		$Result = $this->Connection->Insert($Query);

		if($Result === false) {
			return false;
		}
		else {
			return $this->Connection->LastInsertId();
		}
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
			return new $this->DataType($Result->GetProcessedRow());
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
				$Items[] = new $this->DataType($Result->GetProcessedRow());
			}

			return $Items;
		}
	}

	public function CountByQuery(DbSelectQuery $Query) {
		return $this->Connection->Count($Query);
	}
}
?>