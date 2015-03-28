<?
namespace Framework\MySQL;

use \Framework\Newnorth\DbResult;

class Result extends DbResult {
	/* Instance variables */

	private $Base;

	/* Constructor */

	public function __construct($Base) {
		$this->Base = $Base;
		$this->Rows = $Base->num_rows;
	}

	public function __destruct() {
		$this->Base->close();
	}

	public function __toString() {
		return '';
	}

	/* Instance methods */

	public function Fetch() {
		return ($this->Row = $this->Base->fetch_row()) !== null;
	}

	public function FetchAssoc() {
		return ($this->Row = $this->Base->fetch_assoc()) !== null;
	}

	public function GetProcessedRow() {
		$Row = [];

		foreach($this->Row as $Key => $Value) {
			$this->ProcessRow($Row, 0, explode('-', $Key), $Value);
		}

		return $Row;
	}

	private function ProcessRow(&$Row, $KeyLevel, $Key, $Value) {
		if($KeyLevel === count($Key) - 1) {
			$Row[$Key[$KeyLevel]] = $Value;
		}
		else {
			if(!isset($Row[$Key[$KeyLevel]])) {
				$Row[$Key[$KeyLevel]] = [];
			}

			$this->ProcessRow($Row[$Key[$KeyLevel]], $KeyLevel + 1, $Key, $Value);
		}
	}

	public function GetBoolean($Column) {
		return $this->Row[$Column] === null ? null : (bool)$this->Row[$Column];
	}

	public function GetFloat($Column) {
		return $this->Row[$Column] === null ? null : (float)$this->Row[$Column];
	}

	public function GetInt($Column) {
		return $this->Row[$Column] === null ? null : (int)$this->Row[$Column];
	}

	public function GetString($Column) {
		return $this->Row[$Column] === null ? null : (string)$this->Row[$Column];
	}

	public function IsFalse($Column) {
		return $this->Row[$Column] === MYSQL_FALSE;
	}

	public function IsTrue($Column) {
		return $this->Row[$Column] === MYSQL_TRUE;
	}

	public function IsNull($Column) {
		return $this->Row[$Column] === null;
	}
}
?>