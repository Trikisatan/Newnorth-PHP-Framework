<?php
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
	public function Find($Columns, $Tables, $Conditions) {
		$Result = $this->Connection->Select($Columns, $Tables, $Conditions);

		if($Result === false) {
			return null;
		}

		$Item = $Result->FetchAssoc() ? new $this->DataType($Result->GetRow()) : null;

		$Result->Close();

		return isset($Item) ? $Item : null;
	}
	public function FindAll($Columns, $Tables, $Conditions) {
		$Result = $this->Connection->Select($Columns, $Tables, $Conditions);

		if($Result === false) {
			return null;
		}

		$Items = array();

		while($Result->FetchAssoc()) {
			$Items[] = new $this->DataType($Result->GetRow());
		}

		$Result->Close();

		return $Items;
	}
}
?>