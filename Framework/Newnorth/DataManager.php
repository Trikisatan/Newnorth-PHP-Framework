<?php
namespace Framework\Newnorth;

abstract class DataManager {
	/* Variables */
	public $DataType = null;

	/* Magic methods */
	public function __toString() {
		return '';
	}

	/* Methods */
	public function Find($Connection, $Query, $Values = array()) {
		$Result = $Connection->Select($Query, $Values);

		if($Result === false) {
			return null;
		}

		$Item = $Result->FetchAssoc() ? new $this->DataType($Result->GetRow()) : null;

		$Result->Close();

		return isset($Item) ? $Item : null;
	}
	public function FindAll($Connection, $Query, $Values = array()) {
		$Result = $Connection->Select($Query, $Values);

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