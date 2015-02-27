<?
namespace Framework\Newnorth;

abstract class Control {
	/* Variables */

	public $_Parent;

	public $_Directory;

	public $_Name;

	public $_Parameters;

	public $_Translations;

	public $_Controls;

	public $_Actions;

	public $_Renderer = '\Framework\Newnorth\HtmlRenderer';

	/* Magic methods */

	public function __construct($Parent, $Directory, $Name, $Parameters) {
		$this->_Parent = $Parent;
		$this->_Directory = $Directory;
		$this->_Name = $Name;
		$this->_Parameters = $Parameters;
		$this->_Translations = new Translations($this, $Directory.$Name.'/');
		$this->_Controls = new Controls($this, $Directory.$Name.'/');
		$this->_Actions = new Actions($this, $Directory.$Name.'/');

		$this->ParseParameters();
	}

	public function __toString() {
		return $this->_Directory.$this->_Name;
	}

	/* Life cycle methods */

	public function PreInitialize() {
		$this->_Controls->PreInitialize();
	}

	public abstract function Initialize();

	public function PostInitialize() {
		$this->_Controls->Initialize();
		$this->_Controls->PostInitialize();
	}

	public function PreLoad() {
		$this->_Controls->PreLoad();
	}

	public abstract function Load();

	public function PostLoad() {
		$this->_Controls->Load();
		$this->_Controls->PostLoad();
	}

	public function PreExecute() {
		$this->_Actions->Execute();
		$this->_Controls->PreExecute();
	}

	public abstract function Execute();

	public function PostExecute() {
		$this->_Controls->Execute();
		$this->_Controls->PostExecute();
	}

	public function Render($PlaceHolder = null) {
		call_user_func($this->_Renderer.'::Render', $this, $PlaceHolder);
	}

	/* Methods */

	public abstract function ParseParameters();

	public function GetTranslation($Key, $DefaultValue = null) {
		return isset($this->_Translations[$Key]) ? $this->_Translations[$Key] : $DefaultValue;
	}

	public function SetTranslation($Key, $Value) {
		$this->_Translations[$Key] = $Value;
	}

	public function GetControl($Alias) {
		return $this->_Controls[$Alias];
	}

	public function RenderControl($Alias) {
		$this->_Controls[$Alias]->Render();
	}

	public function GetValidatorMethod($ActionName, &$MethodName, &$MethodObject) {
		$PossibleMethodName = $ActionName.'Action_'.$MethodName;

		if(method_exists($this, $PossibleMethodName)) {
			$MethodObject = $this;
			$MethodName = $PossibleMethodName;
			return true;
		}

		if(method_exists($this, $MethodName)) {
			$MethodObject = $this;
			return true;
		}

		return $this->_Parent->GetValidatorMethod($ActionName, $MethodName, $MethodObject);
	}

	public function GetValidatorRenderMethod($MethodName, &$MethodObject) {
		if(method_exists($this, $MethodName)) {
			$MethodObject = $this;
			return true;
		}

		return $this->_Parent->GetValidatorRenderMethod($MethodName, $MethodObject);
	}
}
?>