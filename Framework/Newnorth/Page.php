<?
namespace Framework\Newnorth;

abstract class Page {
	/* Static variables */

	public static $Instance = null;

	/* Variables */

	public $_Directory;

	public $_Name;

	public $_Translations;

	public $_Controls;

	public $_Actions;

	public $_Renderer = '\Framework\Newnorth\HtmlRenderer';

	/* Magic methods */

	public function __construct($Directory, $Name) {
		if(Page::$Instance !== null) {
			throw new ConfigException('Page has already been initialized.');
		}

		Page::$Instance = $this;

		$this->_Directory = Application::$Files['Pages'].$Directory;
		$this->_Name = $Name;
		$this->_Translations = new Translations($this, $Directory.$Name.'/');
		$this->_Controls = new Controls($this, Application::$Files['Pages'].$Directory.$Name.'/');
		$this->_Actions = new Actions($this, Application::$Files['Pages'].$Directory.$Name.'/');
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

		if($GLOBALS['Layout'] !== null) {
			return $GLOBALS['Layout']->GetValidatorMethod($ActionName, $MethodName, $MethodObject);
		}

		return $GLOBALS['Application']->GetValidatorMethod($ActionName, $MethodName, $MethodObject);
	}

	public function GetValidatorRenderMethod($MethodName, &$MethodObject) {
		if(method_exists($this, $MethodName)) {
			$MethodObject = $this;
			return true;
		}

		if($GLOBALS['Layout'] !== null) {
			return $GLOBALS['Layout']->GetValidatorRenderMethod($MethodName, $MethodObject);
		}

		return $GLOBALS['Application']->GetValidatorRenderMethod($MethodName, $MethodObject);
	}
}
?>