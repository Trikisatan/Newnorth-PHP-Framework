<?php
namespace Framework\MySQL;

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
			trigger_error('MySQL error #'.$this->Connection->errno.': '.$this->Connection->error.'.', E_USER_ERROR);

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
			else if(is_int($Value)) {
				$Where .= ' AND `'.$Column.'` = '.$Value;
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
	public function Select($Columns, $Tables, $Conditions = array()) {
		$Query = array(
			'Columns' => '*',
			'Tables' => '',
			'Conditions' => ''
		);

		if($Columns !== null) {
			foreach($Columns as $Alias => $Column) {
				if(is_int($Alias)) {
					$Query['Columns'] .= ', `'.str_replace('.', '`.`', $Column).'`';
				}
				else {
					$Query['Columns'] .= ', `'.str_replace('.', '`.`', $Column).'` AS `'.$Alias.'`';
				}
			}
		}

		if(isset($Query['Columns'][1])) {
			$Query['Columns'] = substr($Query['Columns'], 3);
		}

		if(is_array($Tables)) {
			$Query['Tables'] = '`'.$Tables[0].'`';

			for($I = 1, $Count = count($Tables); $I < $Count; ++$I) {
				$Query['Tables'] .= ' '.$Tables[$I]['Method'].' `'.$Tables[$I]['Name'].'`';

				$Tables[$I]['Conditions'] = isset($Tables[$I]['Conditions']) ? $this->ProcessConditions($Tables[$I]['Conditions']) : null;

				if($Tables[$I]['Conditions'] !== null) {
					$Query['Tables'] .= ' ON '.$Tables[$I]['Conditions'];
				}
			}
		}
		else {
			$Query['Tables'] = '`'.$Tables.'`';
		}

		$Query['Conditions'] = $this->ProcessConditions($Conditions);

		return $this->Query(
			'SELECT '.
				$Query['Columns'].' '.
			'FROM '.
				$Query['Tables'].' '.
			'WHERE '.
				$Query['Conditions']
		);
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

	private function ProcessConditions($Conditions) {
		if(0 < count($Conditions)) {
			$String = '';
			$I = 0;

			foreach($Conditions as $Column => $Value) {
				if(0 < $I) {
					$String .= ' AND ';
				}

				if(is_int($Column)) {
					$String .= $Value;
				}
				else if($Value === null) {
					$String .= '`'.str_replace('.', '`.`', $Column).'` = NULL';
				}
				else if(is_int($Value)) {
					$String .= '`'.str_replace('.', '`.`', $Column).'` = '.$Value;
				}
				else {
					$String .= '`'.str_replace('.', '`.`', $Column).'` = "'.$this->EscapeString($Value).'"';
				}

				++$I;
			}

			return $String;
		}
		else {
			return null;
		}
	}
}
?>