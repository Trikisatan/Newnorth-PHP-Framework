<?
namespace Framework\Newnorth;

class DataReference {
	/* Instance variables */

	public $LocalKey;

	public $ForeignDataManager;

	public $ForeignKey;

	public $ForeignPrimaryKey;

	public $Alias;

	public $OnDelete = null;

	/* Magic methods */

	public function __construct($Parameters) {
		$this->LocalKey = $Parameters['LocalKey'];

		$this->ForeignDataManager = $Parameters['ForeignDataManager'];

		$this->ForeignPrimaryKey = $Parameters['ForeignDataManager']->PrimaryKey;

		$this->ForeignKey = $Parameters['ForeignKey'];

		$this->Alias = $Parameters['Alias'];

		if(isset($Parameters['OnDelete'])) {
			$this->OnDelete = $Parameters['OnDelete'];
		}
	}

	/* Instance methods */

	public function Initialize(\Framework\Newnorth\DataType $DataType) {
		$DataType->{$this->Alias} = null;

		$DataType->{'Is'.$this->Alias.'Loaded'} = false;
	}

	public function Load(\Framework\Newnorth\DataType $DataType) {
		if(!$DataType->{'Is'.$this->Alias.'Loaded'}) {
			$DataType->{$this->Alias} = $this->ForeignDataManager->{'FindBy'.$this->ForeignKey->Name}($DataType->{$this->LocalKey->Name});

			$DataType->{'Is'.$this->Alias.'Loaded'} = true;
		}

		return $DataType->{$this->Alias} !== null;
	}

	public function Delete(\Framework\Newnorth\DataType $DataType) {
		$this->Load($DataType);

		$this->ForeignDataManager->{'DeleteBy'.$this->ForeignPrimaryKey->Name}($DataType->{$this->Alias}->{$this->ForeignPrimaryKey->Name});

		$DataType->{$this->Alias} = null;
	}

	public function Remove(\Framework\Newnorth\DataType $DataType) {
		$this->Load($DataType);

		$DataType->{$this->Alias}->{'Set'.$this->ForeignKey->Name}(null);

		$DataType->{$this->Alias} = null;
	}
}
?>