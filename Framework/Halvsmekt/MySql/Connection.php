<?php
namespace Framework\Halvsmekt\MySql;

class Connection {
	/* Variables */
	private $Connection;

	/* Constructor */
	public function __construct($Data) {
		$this->Connection = @new \mysqli($Data['Hostname'], $Data['Username'], $Data['Password'], $Data['Database']);

		if($this->Connection->connect_errno !== 0) {
			ConfigError(
				'Unable to connect to MySQL.',
				array(
					'ErrorNumber' => $this->Connection->connect_errno,
					'ErrorMessage' => $this->Connection->connect_error,
				)
			);
		}

		$this->Connection->set_charset($Data['CharSet']);
	}
	public function __toString() {
		return '';
	}

	/* Methods */
	public function Query($QueryString, $Values = array()) {
		foreach($Values as $Name => $Value) {
			if($Value === null) {
				$QueryString = preg_replace('/'.preg_quote($Name).'(?=\W|$)/', 'NULL', $QueryString);
			}
			else if(is_int($Value)) {
				$QueryString = preg_replace('/'.preg_quote($Name).'(?=\W|$)/', $Value, $QueryString);
			}
			else {
				$QueryString = preg_replace('/'.preg_quote($Name).'(?=\W|$)/', '"'.$this->EscapeString($Value).'"', $QueryString);
			}
		}

		$Result = $this->Connection->query($QueryString);

		if($Result === false) {
			$Trace = debug_backtrace();
			$History = '';
			for($I = 1; $I < count($Trace); ++$I)
				$History .= '... In <b>'.$Trace[$I]['file'].'</b> on line <b>'.$Trace[$I]['line'].'</b><br />';
			trigger_error('MySQL error in <b>'.$Trace[0]['file'].'</b> on line <b>'.$Trace[0]['line'].'</b><br />'.$History.'#'.$this->Connection->errno.': '.$this->Connection->error.'<br />', E_USER_ERROR);
				
			return false;
		}
		else if($Result === true) {
			return true;
		}
		else {
			return new Result($Result);
		}
	}
	public function Insert($Table, $Values) {
		$Query = array('', '');

		foreach($Values as $Column => $Value) {
			$Query[0] .= ', `'.$Column.'`';

			if($Value === null) {
				$Query[1] .= ', NULL';
			}
			else if($Value === true) {
				$Query[1] .= ', 1';
			}
			else if($Value === false) {
				$Query[1] .= ', 0';
			}
			else {
				$Query[1] .= ', "'.$this->EscapeString($Value).'"';
			}
		}

		$Query[0] = substr($Query[0], 2);
		$Query[1] = substr($Query[1], 2);

		return $this->Query(
			'INSERT INTO `'.$Table.'` ('.
				$Query[0].
			') VALUES ('.
				$Query[1].
			')'
		);
	}
	public function InsertIgnore($Table, $Values) {
		$Query = array('', '');

		foreach($Values as $Column => $Value) {
			$Query[0] .= ', `'.$Column.'`';

			if($Value === null) {
				$Query[1] .= ', NULL';
			}
			else if($Value === true) {
				$Query[1] .= ', 1';
			}
			else if($Value === false) {
				$Query[1] .= ', 0';
			}
			else {
				$Query[1] .= ', "'.$this->EscapeString($Value).'"';
			}
		}

		$Query[0] = substr($Query[0], 2);
		$Query[1] = substr($Query[1], 2);

		return $this->Query(
			'INSERT IGNORE INTO `'.$Table.'` ('.
				$Query[0].
			') VALUES ('.
				$Query[1].
			')'
		);
	}
	public function Delete($Table, $Keys) {
		$Where = '';

		foreach($Keys as $Column => $Value) {
			if($Value === null) {
				$Where .= ' AND `'.$Column.'` = NULL';
			}
			else {
				$Where .= ' AND `'.$Column.'` = "'.$this->EscapeString($Value).'"';
			}
		}

		$Where = substr($Where, 5);

		return $this->Query(
			'DELETE FROM '.
				'`'.$Table.'` '.
			'WHERE '.
				$Where
		);
	}
	public function Update($Table, $Values, $Keys) {
		$Set = '';

		foreach($Values as $Column => $Value) {
			if(is_int($Column)) {
				$Set .= ', '.$Value;
			}
			else if($Value === null) {
				$Set .= ', `'.$Column.'` = NULL';
			}
			else if($Value === true) {
				$Set .= ', `'.$Column.'` = 1';
			}
			else if($Value === false) {
				$Set .= ', `'.$Column.'` = 0';
			}
			else {
				$Set .= ', `'.$Column.'` = "'.$this->EscapeString($Value).'"';
			}
		}

		$Set = substr($Set, 2);
		$Where = '';

		foreach($Keys as $Column => $Value) {
			if(is_int($Column)) {
				$Where .= ' AND '.$Value;
			}
			else if(is_int(strpos($Column, '?'))) {
				$Value = str_replace('?', $this->EscapeString($Value), $Column);
				$Where .= ' AND '.$Value;
			}
			else if($Value === null) {
				$Where .= ' AND `'.$Column.'` = NULL';
			}
			else {
				$Where .= ' AND `'.$Column.'` = "'.$this->EscapeString($Value).'"';
			}
		}

		$Where = substr($Where, 5);

		return $this->Query(
			'UPDATE '.
				'`'.$Table.'` '.
			'SET '.
				$Set.' '.
			'WHERE '.
				$Where
		);
	}
	public function Select($Query, $Values = array()) {
		foreach($Values as $Name => $Value) {
			if($Value === null) {
				$Query = str_replace($Name, 'NULL', $Query);
			}
			else if(is_int($Value)) {
				$Query = str_replace($Name, $Value, $Query);
			}
			else {
				$Query = str_replace($Name, '"'.$this->EscapeString($Value).'"', $Query);
			}
		}

		return $this->Query($Query);
	}
	public function Lock($Tables) {
		$Query = null;

		foreach($Tables as $Table => $LockType) {
			if($Query === null) {
				$Query = '`'.$Table.'` '.$LockType;
			}
			else {
				$Query .= ', `'.$Table.'` '.$LockType;
			}
		}

		$this->Query('LOCK TABLES '.$Query);
	}
	public function Unlock($Tables) {
		$this->Query('UNLOCK TABLES');
	}
	public function EscapeString($String) {
		return $this->Connection->real_escape_string($String);
	}
	public function LastInsertId() {
		return $this->Connection->insert_id;
	}
	public function AffectedRows() {
		return $this->Connection->affected_rows;
	}
	public function FoundRows() {
		$Result = $this->Query('SELECT FOUND_ROWS()');
		return $Result->Fetch() ? $Result->GetInt(0) : 0;
	}
}
?>