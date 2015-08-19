<?
class DropDownListControl extends \Framework\Newnorth\Control {
	/* Magic methods */

	public function __construct($Parent, $Directory, $Namespace, $Name, $Alias, $Parameters) {
		$this->_Directory = $GLOBALS['Config']->Files['Controls'];

		$this->_Namespace = '\\';

		$this->_Name = 'DropDownListControl';

		parent::__construct($Parent, $Directory, $Namespace, $Name, $Alias, $Parameters);
	}

	/* Life cycle methods */

	public function PostExecute() {
		parent::PostExecute();

		if(method_exists($this->_Parent, 'SetControlOptions_'.$this->_Alias)) {
			$this->_Parameters['Options'] = $this->_Parent->{'SetControlOptions_'.$this->_Alias}($this);
		}

		if(method_exists($this->_Parent, 'SetControlValue_'.$this->_Alias)) {
			$this->_Parameters['Value'] = $this->_Parent->{'SetControlValue_'.$this->_Alias}($this);
		}
	}

	/* Validator methods */

	public function GetValueValidator($Parameters) {
		if(isset($this->_Parameters['Options'])) {
			$Value = $_GET[$this->_Parameters['Name']];

			foreach($this->_Parameters['Options'] as $Option) {
				if($Option['Value'] === $Value) {
					return true;
				}
			}

			return false;
		}
		else {
			return false;
		}
	}

	public function PostValueValidator($Parameters) {
		if(isset($this->_Parameters['Options'])) {
			$Value = $_POST[$this->_Parameters['Name']];

			foreach($this->_Parameters['Options'] as $Option) {
				if($Option['Value'] === $Value) {
					return true;
				}
			}

			return false;
		}
		else {
			return false;
		}
	}
}
?>