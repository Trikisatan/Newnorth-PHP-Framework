<?
namespace Framework\Newnorth;

class DataType {
	/* Instance variables */

	public $_DataManager;

	public $_Data;

	/* Magic methods */

	public function __construct(\Framework\Newnorth\ADataManager $DataManager, Array $Data) {
		$this->_DataManager = $DataManager;

		$this->_Data = $Data;

		foreach($Data as $Key => $Value) {
			if(isset($DataManager->DataMembers[$Key])) {
				$this->$Key = $DataManager->DataMembers[$Key]->Parse($Value);
			}
		}

		foreach($DataManager->DataReferences as $DataReference) {
			$DataReference->Initialize($this);
		}

		foreach($DataManager->DataLists as $DataList) {
			$DataList->Initialize($this);
		}
	}

	public function __call($Function, $Parameters) {
		if(preg_match('/^Load([A-Z][0-9A-Za-z]+)$/', $Function, $Matches) === 1) {
			if($this->Load($Matches[1], $Result)) {
				return $Result;
			}
		}
		else if(preg_match('/^Create([A-Z][0-9A-Za-z]+)$/', $Function, $Matches) === 1) {
			return $this->Create($Matches[1], $Parameters);
		}
		else if(preg_match('/^Add([A-Z][0-9A-Za-z]+)$/', $Function, $Matches) === 1) {
			return $this->Add($Matches[1], $Parameters);
		}
		else if(preg_match('/^Delete([A-Z][0-9A-Za-z]+)By([A-Z][0-9A-Za-z]+)$/', $Function, $Matches) === 1) {
			if($this->FindAllBy($Matches[1], $Matches[2], $Parameters, $Items)) {
				$Count = 0;

				foreach($Items as $Item) {
					array_unshift($Parameters, $Item);

					if($this->Delete($Matches[1], $Parameters, $Result) && $Result) {
						++$Count;
					}

					array_shift($Parameters);
				}

				return $Count;
			}
		}
		else if(preg_match('/^Delete([A-Z][0-9A-Za-z]+)$/', $Function, $Matches) === 1) {
			if($this->Delete($Matches[1], $Parameters, $Result)) {
				return $Result;
			}
		}
		else if(preg_match('/^Remove([A-Z][0-9A-Za-z]+)$/', $Function, $Matches) === 1) {
			if($this->Remove($Matches[1], $Parameters, $Result)) {
				return $Result;
			}
		}
		else if(preg_match('/^Find([A-Z][0-9A-Za-z]+)By([A-Z][0-9A-Za-z]+)$/', $Function, $Matches) === 1) {
			return $this->FindBy($Matches[1], $Matches[2], $Parameters);
		}
		else if(preg_match('/^FindAll([A-Z][0-9A-Za-z]+)By([A-Z][0-9A-Za-z]+)$/', $Function, $Matches) === 1) {
			if($this->FindAllBy($Matches[1], $Matches[2], $Parameters, $Result)) {
				return $Result;
			}
		}
		else if(preg_match('/^IndexOf([A-Z][0-9A-Za-z]+)By([A-Z][0-9A-Za-z]+)$/', $Function, $Matches) === 1) {
			return $this->IndexOfBy($Matches[1], $Matches[2], $Parameters);
		}
		else if(preg_match('/^Has([A-Z][0-9A-Za-z]+)By([A-Z][0-9A-Za-z]+)$/', $Function, $Matches) === 1) {
			return $this->HasBy($Matches[1], $Matches[2], $Parameters);
		}
		else if(preg_match('/^Count([A-Z][0-9A-Za-z]+)By([A-Z][0-9A-Za-z]+)$/', $Function, $Matches) === 1) {
			return $this->CountBy($Matches[1], $Matches[2], $Parameters);
		}
		else if(preg_match('/^Count([A-Z][0-9A-Za-z]+)$/', $Function, $Matches) === 1) {
			return $this->Count($Matches[1], $Parameters);
		}
		else if(preg_match('/^([A-Z][a-z]+)([0-9A-Za-z]+)$/', $Function, $Matches) === 1) {
			$Function = $Matches[1];

			$Member = $Matches[2];

			if(isset($this->_DataManager->DataMembers[$Member])) {
				$Member = $this->_DataManager->DataMembers[$Member];

				if(method_exists($Member, $Function)) {
					return call_user_func_array(
						[$Member, $Function],
						[$this, $Parameters]
					);
				}
				else {
					throw new RuntimeException(
						'Object method doesn\'t exist.',
						['Function' => $Function.$Member->Alias, 'Parameters' => $Parameters]
					);
				}
			}
			else {
				throw new RuntimeException(
					'Object method doesn\'t exist.',
					['Function' => $Function.$Member, 'Parameters' => $Parameters]
				);
			}
		}

		throw new RuntimeException(
			'Object method doesn\'t exist.',
			['Function' => $Function, 'Parameters' => $Parameters]
		);
	}

	/* Instance methods */

	private function Load($Alias, &$Result) {
		if(isset($this->_DataManager->DataReferences[$Alias])) {
			$Result = $this->_DataManager->DataReferences[$Alias]->Load($this);

			return true;
		}
		else if(isset($this->_DataManager->DataLists[$Alias])) {
			$Result = $this->_DataManager->DataLists[$Alias]->Load($this);

			return true;
		}
		else {
			return false;
		}
	}

	private function Create($DataList, $Parameters) {
		return $this->_DataManager->DataLists[$DataList]->Create(
			$this,
			$Parameters[0],
			$Parameters[1]
		);
	}

	private function Add($DataList, $Parameters) {
		return $this->_DataManager->DataLists[$DataList]->Add($this, $Parameters[0], $Parameters[1]);
	}

	public function OnDelete($Source) {
		foreach($this->_DataManager->DataReferences as $DataReference) {
			if($DataReference->OnDelete !== null) {
				$this->{'OnDelete_DataReference_'.$DataReference->OnDelete}($DataReference, $Source);
			}
		}

		foreach($this->_DataManager->DataLists as $DataList) {
			if($DataList->OnDelete !== null) {
				$this->{'OnDelete_DataList_'.$DataList->OnDelete}($DataList, $Source);
			}
		}
	}

	private function OnDelete_DataReference_Delete($DataReference, $Source) {
		if($DataReference->Load($this)) {
			$DataReference->Delete($this, $Source);
		}
	}

	private function OnDelete_DataReference_Remove($DataReference, $Source) {
		if($DataReference->Load($this)) {
			$DataReference->Remove($this, $Source);
		}
	}

	private function OnDelete_DataList_Delete($DataList, $Source) {
		if($DataList->Load($this, $Items)) {
			foreach($Items as $Item) {
				$DataList->Delete($this, $Item, $Source);
			}
		}
	}

	private function OnDelete_DataList_Remove($DataList, $Source) {
		if($DataList->Load($this, $Items)) {
			foreach($Items as $Item) {
				$DataList->Remove($this, $Item, $Source);
			}
		}
	}

	private function Delete($Alias, $Parameters, &$Result) {
		if(isset($this->_DataManager->DataReferences[$Alias])) {
			$Result = $this->_DataManager->DataReferences[$Alias]->Delete(
				$this,
				$Parameters[0]
			);

			return true;
		}
		else if(isset($this->_DataManager->DataLists[$Alias])) {
			$Result = $this->_DataManager->DataLists[$Alias]->Delete(
				$this,
				$Parameters[0],
				$Parameters[1]
			);

			return true;
		}
		else {
			return false;
		}
	}

	private function Remove($Alias, $Parameters, &$Result) {
		if(isset($this->_DataManager->DataReferences[$Alias])) {
			$Result = $this->_DataManager->DataReferences[$Alias]->Remove($this, $Parameters[0]);

			return true;
		}
		else if(isset($this->_DataManager->DataLists[$Alias])) {
			$Result = $this->_DataManager->DataLists[$Alias]->Remove($this, $Parameters[0], $Parameters[1]);

			return true;
		}
		else {
			return false;
		}
	}

	private function FindBy($DataList, $DataMembers, $Values) {
		$DataList = $this->_DataManager->DataLists[$DataList];

		$DataMembers = explode('And', $DataMembers);

		for($I = 0; $I < count($DataMembers); ++$I) {
			$DataMembers[$I] = $DataList->ForeignDataManager->DataMembers[$DataMembers[$I]];

			$Values[$I] = $DataMembers[$I]->Parse($Values[$I]);
		}

		return $DataList->FindBy($this, $DataMembers, $Values);
	}

	private function FindAllBy($DataList, $DataMembers, &$Parameters, &$Result) {
		$DataList = $this->_DataManager->DataLists[$DataList];

		$DataMembers = explode('And', $DataMembers);

		$Values = [];

		for($I = 0; $I < count($DataMembers); ++$I) {
			$DataMembers[$I] = $DataList->ForeignDataManager->DataMembers[$DataMembers[$I]];

			$Values[] = $DataMembers[$I]->Parse($Parameters[0]);

			array_splice($Parameters, 0, 1);
		}

		$Result = $DataList->FindAllBy($this, $DataMembers, $Values);

		return true;
	}

	private function IndexOfBy($DataList, $DataMembers, $Values) {
		$DataList = $this->_DataManager->DataLists[$DataList];

		$DataMembers = explode('And', $DataMembers);

		for($I = 0; $I < count($DataMembers); ++$I) {
			$DataMembers[$I] = $DataList->ForeignDataManager->DataMembers[$DataMembers[$I]];

			$Values[$I] = $DataMembers[$I]->Parse($Values[$I]);
		}

		return $DataList->IndexOfBy($this, $DataMembers, $Values);
	}

	private function HasBy($DataList, $DataMembers, $Values) {
		$DataList = $this->_DataManager->DataLists[$DataList];

		$DataMembers = explode('And', $DataMembers);

		for($I = 0; $I < count($DataMembers); ++$I) {
			$DataMembers[$I] = $DataList->ForeignDataManager->DataMembers[$DataMembers[$I]];

			$Values[$I] = $DataMembers[$I]->Parse($Values[$I]);
		}

		return $DataList->HasBy($this, $DataMembers, $Values);
	}

	private function Count($DataList, $Values) {
		$DataList = $this->_DataManager->DataLists[$DataList];

		return $DataList->Count($this, $Values);
	}

	private function CountBy($DataList, $DataMembers, $Values) {
		$DataList = $this->_DataManager->DataLists[$DataList];

		$DataMembers = explode('And', $DataMembers);

		for($I = 0; $I < count($DataMembers); ++$I) {
			$DataMembers[$I] = $DataList->ForeignDataManager->DataMembers[$DataMembers[$I]];

			$Values[$I] = $DataMembers[$I]->Parse($Values[$I]);
		}

		return $DataList->CountBy($this, $DataMembers, $Values);
	}
}
?>