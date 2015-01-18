<?
namespace Framework\MySQL;

class Result {
	/* Variables */
	private $Result;
	private $Rows;
	private $Row;

	/* Constructor */
	public function __construct($Result) {
		$this->Result = $Result;
		$this->Rows = $Result->num_rows;
	}
	public function __toString() {
		return '';
	}

	/* Methods */
	public function GetAmountOfRows() {
		return $this->Rows;
	}
	public function Fetch() {
		return ($this->Row = $this->Result->fetch_row()) !== null;
	}
	public function FetchAssoc() {
		return ($this->Row = $this->Result->fetch_assoc()) !== null;
	}
	public function GetRow() {
		return $this->Row;
	}
	public function IsNull($Column) {
		return $this->Row[$Column] === null;
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
	public function Close() {
		$this->Result->close();
	}
}
?>