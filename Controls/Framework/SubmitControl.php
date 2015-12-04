<?
namespace Framework;

class SubmitControl extends \Framework\Newnorth\Control {
	/* Magic methods */

	public function __construct($Parent, $Directory, $Namespace, $Name, $Alias, $Parameters) {
		if($this->_Directory === null) {
			$this->_Directory = Config('Files/Controls').'Framework/';
		}

		if($this->_Namespace === null) {
			$this->_Namespace = '\\Framework\\';
		}

		if($this->_Name === null) {
			$this->_Name = 'SubmitControl';
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

		if(method_exists($this->_Parent, 'SetControl»Text»'.$this->_Alias)) {
			$this->_Parameters['Text'] = $this->_Parent->{'SetControl»Text»'.$this->_Alias}($this);
		}

		parent::Initialize();
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