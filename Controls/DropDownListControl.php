<?
class DropDownListControl extends \Framework\Newnorth\Control {
	/* Magic methods */

	public function __construct($Parent, $Directory, $Namespace, $Name, $Alias, $Parameters) {
		$this->_Namespace = '\\';

		$this->_Name = 'DropDownListControl';

		parent::__construct($Parent, $Directory, $Namespace, $Name, $Alias, $Parameters);

		if(isset($this->_Parameters['Options'])) {
			$Options = [];

			foreach($this->_Parameters['Options'] as $Value => $Text) {
				$Options[(string)$Value] = (string)$Text;
			}

			$this->_Parameters['Options'] = $Options;
		}
		else {
			$this->_Parameters['Options'] = [];
		}
	}

	/* Life cycle methods */

	public function PostExecute() {
		parent::PostExecute();

		if(method_exists($this->_Parent, 'SetControlOptions_'.$this->_Alias)) {
			$this->_Parent->{'SetControlOptions_'.$this->_Alias}($this);
		}

		if(method_exists($this->_Parent, 'SetControlValue_'.$this->_Alias)) {
			$this->_Parent->{'SetControlValue_'.$this->_Alias}($this);
		}
	}

	/* Validator methods */

	public function GetValueValidator($Parameters) {
		return isset($this->_Parameters['Options'][$_GET[$this->_Parameters['Name']]]);
	}

	public function PostValueValidator($Parameters) {
		return isset($this->_Parameters['Options'][$_POST[$this->_Parameters['Name']]]);
	}
}
?>