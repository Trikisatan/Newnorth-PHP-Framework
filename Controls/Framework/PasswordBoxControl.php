<?
namespace Framework;

class PasswordBoxControl extends \Framework\Newnorth\Control {
	/* Magic methods */

	public function __construct($Parent, $Directory, $Namespace, $Name, $Alias, $Parameters) {
		if($this->_Directory === null) {
			$this->_Directory = Config('Files/Controls').'Framework/';
		}

		if($this->_Namespace === null) {
			$this->_Namespace = '\\Framework\\';
		}

		if($this->_Name === null) {
			$this->_Name = 'PasswordBoxControl';
		}

		parent::__construct($Parent, $Directory, $Namespace, $Name, $Alias, $Parameters);
	}

	/* Life cycle methods */

	public function Initialize() {
		if(method_exists($this->_Parent, 'SetControl»Id»'.$this->_Alias)) {
			$this->_Parameters['Id'] = $this->_Parent->{'SetControl»Id»'.$this->_Alias}($this);
		}

		if(method_exists($this->_Parent, 'SetControl»Form»'.$this->_Alias)) {
			$this->_Parameters['Form'] = $this->_Parent->{'SetControl»Form»'.$this->_Alias}($this);
		}

		if(method_exists($this->_Parent, 'SetControl»Name»'.$this->_Alias)) {
			$this->_Parameters['Name'] = $this->_Parent->{'SetControl»Name»'.$this->_Alias}($this);
		}

		if(method_exists($this->_Parent, 'SetControl»Value»'.$this->_Alias)) {
			$this->_Parameters['Value'] = $this->_Parent->{'SetControl»Value»'.$this->_Alias}($this);
		}

		parent::Initialize();
	}

	/* Validator methods */

	public function Validators»GetMaxLength($Parameters) {
		return isset($_GET[$this->_Parameters['Name']][$Parameters['Max']]);
	}

	public function Validators»GetMinLength($Parameters) {
		return isset($_GET[$this->_Parameters['Name']][$Parameters['Min']]);
	}

	public function Validators»GetNotEmpty($Parameters) {
		if(isset($Parameters['AllowUnset']) && $Parameters['AllowUnset']) {
			if(!isset($_GET[$this->_Parameters['Name']])) {
				return true;
			}
		}

		return isset($_GET[$this->_Parameters['Name']][0]);
	}

	public function Validators»PostMaxLength($Parameters) {
		return isset($_POST[$this->_Parameters['Name']][$Parameters['Max']]);
	}

	public function Validators»PostMinLength($Parameters) {
		return isset($_POST[$this->_Parameters['Name']][$Parameters['Min']]);
	}

	public function Validators»PostNotEmpty($Parameters) {
		if(isset($Parameters['AllowUnset']) && $Parameters['AllowUnset']) {
			if(!isset($_POST[$this->_Parameters['Name']])) {
				return true;
			}
		}

		return isset($_POST[$this->_Parameters['Name']][0]);
	}

	/* Instance methods */

	public function CreateElement($Name, $Attributes, $OptionalAttributes, $Parameters, $Html) {
		$Element = new \Framework\HTML\Element($Name);

		foreach($Attributes as $Key => $Value) {
			$Element->CreateAttribute(
				$Key,
				$Value
			);
		}

		foreach($OptionalAttributes as $Alias => $Key) {
			if(isset($Parameters[$Alias])) {
				$Element->CreateAttribute(
					$Key,
					$Parameters[$Alias]
				);
			}
		}

		if($Html !== null) {
			$Element->AppendHtml($Html);
		}

		return $Element;
	}
}
?>