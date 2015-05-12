<?
namespace Framework\Controls;

class DropDownListControl extends \Framework\Newnorth\Control {
	/* Magic methods */

	public function __construct($Parent, $Directory, $Namespace, $Name, $Alias, $Parameters) {
		$this->_Directory = FRAMEWORK_DIRECTORY.'Controls/';

		$this->_Namespace = '\\Framework\\Controls\\';

		$this->_Name = 'DropDownListControl';

		parent::__construct($Parent, $Directory, $Namespace, $Name, $Alias, $Parameters);

		$this->_Parameters['Value'] = isset($this->_Parameters['Value']) ? (string)$this->_Parameters['Value'] : null;

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

		if(method_exists($this->_Parent, 'Set'.$this->_Alias.'ControlOptions')) {
			$this->_Parent->{'Set'.$this->_Alias.'ControlOptions'}($this);
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