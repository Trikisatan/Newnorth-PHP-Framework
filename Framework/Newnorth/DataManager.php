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

	public function Find(DbSelectQuery $Query) {
		$Result = $this->Connection->Find($Query);

		if($Result === false) {
			return null;
		}

		if($Result->FetchAssoc()) {
			return new $this->DataType($Result->Row);
		}

		return null;
	}

	public function FindAll(DbSelectQuery $Query) {
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

	public function Count(DbSelectQuery $Query) {
		return $this->Connection->Count($Query);
	}
}
?>