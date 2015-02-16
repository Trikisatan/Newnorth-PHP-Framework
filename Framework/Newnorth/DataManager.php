<?
namespace Framework\Newnorth;

abstract class DataManager {
	/* Variables */

	public $DataType = null;

	public $Connection = null;

	/* Magic methods */

	public function __toString() {
		return '';
	}

	/* Methods */

	protected function _Insert(DbInsertQuery $Query) {
		$Result = $this->Connection->Insert($Query);

		if($Result === false) {
			return false;
		}

		return $this->Connection->LastInsertId();
	}

	protected function _Delete(DbDeleteQuery $Query) {
		return $this->Connection->Delete($Query);
	}

	protected function _Find(DbSelectQuery $Query) {
		$Result = $this->Connection->Find($Query);

		if($Result === false) {
			return null;
		}

		if($Result->FetchAssoc()) {
			return new $this->DataType($Result->Row);
		}

		return null;
	}

	protected function _FindAll(DbSelectQuery $Query) {
		$Result = $this->Connection->FindAll($Query);

		if($Result === false) {
			return [];
		}

		$Items = [];

		while($Result->FetchAssoc()) {
			$Items[] = new $this->DataType($Result->Row);
		}

		return $Items;
	}

	protected function _Count(DbSelectQuery $Query) {
		return $this->Connection->Count($Query);
	}
}
?>