<?
namespace Framework\Newnorth;

abstract class Control {
	/* Static methods */

	public static function Instantiate($Parent, $Path, $Alias, $Parameters) {
		Control::ParsePath(
			$Parent,
			$Path,
			$FilePath,
			$Directory,
			$ClassName,
			$Namespace,
			$Name
		);

		if(!class_exists($ClassName, false)) {
			if(file_exists($FilePath)) {
				include($FilePath);
			}

			if(!class_exists($ClassName, false)) {
				throw new RuntimeException(
					'Unable to load control.',
					[
						'Path' => $Path,
						'File path' => $FilePath,
						'Directory' => $Directory,
						'Class name' => $ClassName,
						'Namespace' => $Namespace,
						'Name' => $Name,
					]
				);
			}
		}

		return new $ClassName($Parent, $Directory, $Namespace, $Name, $Alias, $Parameters);
	}

	private static function ParsePath($Parent, $Path, &$FilePath, &$Directory, &$ClassName, &$Namespace, &$Name) {
		$FilePath = $GLOBALS['Config']->Files['Controls'].$Path.'Control.php';

		$Directory = substr($FilePath, 0, strrpos($FilePath, '/') + 1);

		$ClassName = '\\'.str_replace('/', '\\', $Path).'Control';

		$Namespace = strrpos($ClassName, '\\');

		$Namespace = ($Namespace === false) ? '\\' : substr($ClassName, 0, $Namespace + 1);

		$Name = strrpos($Path, '/');

		$Name = (($Name === false) ? $Path : substr($Path, $Name + 1)).'Control';
	}

	/* Instance variables */

	public $_Id;

	public $_Parent;

	public $_Directory;

	public $_Namespace;

	public $_Name;

	public $_Alias;

	public $_Parameters;

	public $_Actions;

	public $_Controls;

	public $_ErrorMessages = [];

	public $_Renderer = '\Framework\Newnorth\HtmlRenderer';

	/* Magic methods */

	public function __construct($Parent, $Directory, $Namespace, $Name, $Alias, $Parameters) {
		$this->_Id = $Parent->_Id.'/'.$Alias;

		$this->_Parent = $Parent;

		$this->_Directory = isset($this->_Directory[0]) ? $this->_Directory : $Directory;

		$this->_Namespace = isset($this->_Namespace[0]) ? $this->_Namespace : $Namespace;

		$this->_Name = isset($this->_Name[0]) ? $this->_Name : $Name;

		$this->_Alias = $Alias;

		$this->_Parameters = $Parameters;

		$this->_Actions = new Actions($this, $Directory.$Name.'/');

		$this->_Controls = new Controls($this, $Directory.$Name.'/', $Namespace.$Name.'\\');

		$GLOBALS['Application']->RegisterObject($this);
	}

	public function __toString() {
		return $this->_Id;
	}

	/* Life cycle methods */

	public function PreInitialize() {
		$this->_Controls->PreInitialize();
	}

	public function Initialize() {
		$this->_Controls->Initialize();
	}

	public function PostInitialize() {
		$this->_Controls->PostInitialize();
	}

	public function PreLoad() {
		$this->_Controls->PreLoad();
	}

	public function Load() {
		$this->_Controls->Load();
	}

	public function PostLoad() {
		$this->_Controls->PostLoad();
	}

	public function PreExecute() {
		$this->_Controls->PreExecute();
	}

	public function Execute() {
		$this->_Actions->Execute();

		$this->_Controls->Execute();
	}

	public function PostExecute() {
		$this->_Controls->PostExecute();
	}

	public function Render($PlaceHolder = null, $Return = false, $Parameters = []) {
		return call_user_func($this->_Renderer.'::Render', $this, $PlaceHolder, $Return, $Parameters);
	}

	/* Instance methods */

	public function Destroy() {
		$this->_Actions->Destroy();

		$this->_Actions = null;

		$this->_Controls->Destroy();

		$this->_Controls = null;

		$GLOBALS['Application']->UnregisterObject($this);
	}

	public function GetTranslation($Key, $DefaultValue = null) {
		return isset($this->_Translations[$Key]) ? $this->_Translations[$Key] : $DefaultValue;
	}

	public function SetTranslation($Key, $Value) {
		$this->_Translations[$Key] = $Value;
	}

	public function _RemoveAction($Action) {
		for($I = 0; $I < count($this->_Actions->Items); ++$I) {
			if($this->_Actions->Items[$I] === $Alias) {
				$this->_Actions->Items[$I]->Destroy();

				array_splice($this->_Actions->Items, $I);
			}
		}
	}

	public function AddControl($Alias, \Framework\Newnorth\Control $Control) {
		$this->_Controls->Items[$Alias] = $Control;
	}

	public function RemoveControl($Alias) {
		$this->_Controls->Items[$Alias]->Destroy();

		unset($this->_Controls->Items[$Alias]);
	}

	public function GetControl($Alias) {
		return $this->_Controls->Items[$Alias];
	}

	public function RenderControl($Alias, $Placeholder = null, $Return = false, $Parameters = []) {
		return $this->_Controls->Items[$Alias]->Render($Placeholder, $Return, $Parameters);
	}

	/* Validator methods */

	public function IsBetweenExclusiveValidator($Parameters) {
		return eval('return $Parameters[\'Min\'] < '.$Parameters['Variable'].' && '.$Parameters['Variable'].' < $Parameters[\'Max\'];');
	}

	public function IsBetweenInclusiveValidator($Parameters) {
		return eval('return $Parameters[\'Min\'] <= '.$Parameters['Variable'].' && '.$Parameters['Variable'].' <= $Parameters[\'Max\'];');
	}

	public function IsEMailAddressValidator($Parameters) {
		return eval('return 0 < preg_match(\'/^([a-zA-Z0-9_.-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]+)?$/\', '.$Parameters['Variable'].');');
	}

	public function IsFloatValidator($Parameters) {
		return eval('return is_numeric('.$Parameters['Variable'].');');
	}

	public function IsIntValidator($Parameters) {
		return eval('return ctype_digit('.$Parameters['Variable'].');');
	}

	public function MaxLengthValidator($Parameters) {
		return eval('return isset('.$Parameters['Variable'].'[$Parameters[\'Max\']]);');
	}

	public function MinLengthValidator($Parameters) {
		return eval('return isset('.$Parameters['Variable'].'[$Parameters[\'Min\']]);');
	}

	public function NotEmptyValidator($Parameters) {
		return eval('return isset('.$Parameters['Variable'].'[0]);');
	}
}
?>