<?
namespace Framework\Newnorth;

class Layout {
	/* Static methods */

	public static function Instantiate($Path, &$Layout) {
		Layout::ParsePath(
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
					'Unable to load layout.',
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

		$Layout = new $ClassName($Directory, $Namespace, $Name);
	}

	private static function ParsePath($Path, &$FilePath, &$Directory, &$ClassName, &$Namespace, &$Name) {
		$FilePath = $GLOBALS['Config']->Files['Layouts'].$Path.'Layout.php';

		$Directory = substr($FilePath, 0, strrpos($FilePath, '/') + 1);

		$ClassName = '\\'.str_replace('/', '\\', $Path).'Layout';

		$Namespace = strrpos($ClassName, '\\');

		$Namespace = ($Namespace === false) ? '\\' : substr($ClassName, 0, $Namespace + 1);

		$Name = strrpos($Path, '/');

		$Name = (($Name === false) ? $Path : substr($Path, $Name + 1)).'Layout';
	}

	/* Instance variables */

	public $_Id;

	public $_Directory;

	public $_Namespace;

	public $_Name;

	public $_Controls;

	public $_Actions;

	public $_ErrorMessages = [];

	public $_Renderer = '\Framework\Newnorth\HtmlRenderer';

	/* Magic methods */

	public function __construct($Directory, $Namespace, $Name) {
		$this->_Id = str_replace('\\', '/', $Namespace).$Name;

		$this->_Directory = isset($this->_Directory[0]) ? $this->_Directory : $Directory;

		$this->_Namespace = isset($this->_Namespace[0]) ? $this->_Namespace : $Namespace;

		$this->_Name = isset($this->_Name[0]) ? $this->_Name : $Name;

		$this->_Controls = new Controls($this, $Directory.$Name.'/', $Namespace.$Name.'\\');

		$this->_Actions = new Actions($this, $Directory.$Name.'/');

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

	public function Render() {
		call_user_func($this->_Renderer.'::Render', $this, null);
	}

	/* Instance methods */

	public function GetTranslation($Key, $DefaultValue = null) {
		return isset($this->_Translations[$Key]) ? $this->_Translations[$Key] : $DefaultValue;
	}

	public function SetTranslation($Key, $Value) {
		$this->_Translations[$Key] = $Value;
	}

	public function GetControl($Alias) {
		return $this->_Controls->Items[$Alias];
	}

	public function RenderControl($Alias) {
		$this->_Controls->Items[$Alias]->Render();
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