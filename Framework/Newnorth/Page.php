<?
namespace Framework\Newnorth;

class Page {
	/* Static methods */

	public static function Instantiate($Path, &$Page) {
		Page::ParsePath(
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
					'Unable to load page.',
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

		$Page = new $ClassName($Directory, $Namespace, $Name);
	}

	private static function ParsePath($Path, &$FilePath, &$Directory, &$ClassName, &$Namespace, &$Name) {
		$FilePath = Config('Files/Pages', '').$Path.'Page.php';

		$Directory = strrpos($FilePath, '/');

		$Directory = ($Directory === false) ? '' : substr($FilePath, 0, strrpos($FilePath, '/') + 1);

		$ClassName = '\\'.str_replace('/', '\\', $Path).'Page';

		$Namespace = strrpos($ClassName, '\\');

		$Namespace = ($Namespace === false) ? '\\' : substr($ClassName, 0, $Namespace + 1);

		$Name = strrpos($Path, '/');

		$Name = (($Name === false) ? $Path : substr($Path, $Name + 1)).'Page';
	}

	/* Instance variables */

	public $_Id;

	public $_Directory;

	public $_Namespace;

	public $_Name;

	public $_Actions;

	public $_Controls;

	public $_ErrorMessages = [];

	public $_Renderer = '\Framework\Newnorth\HtmlRenderer';

	/* Magic methods */

	public function __construct($Directory, $Namespace, $Name) {
		$this->_Id = str_replace('\\', '/', $Namespace).$Name;

		$this->_Directory = isset($this->_Directory[0]) ? $this->_Directory : $Directory;

		$this->_Namespace = isset($this->_Namespace[0]) ? $this->_Namespace : $Namespace;

		$this->_Name = isset($this->_Name[0]) ? $this->_Name : $Name;

		$this->_Actions = new Actions($this, $Directory.$Name.'/');

		$this->_Controls = new Controls($this);

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
		$this->_Actions->Initialize();

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

	/* Action methods */

	public function _AddAction($Alias, \Framework\Newnorth\Action $Action) {
		$this->_Actions->Items[$Alias] = $Action;
	}

	public function _RemoveAction($Action) {
		$this->_Actions->Items[$Alias]->Destroy();

		unset($this->_Actions->Items[$Alias]);
	}

	public function GetAction($Alias) {
		return $this->_Actions->Items[$Alias];
	}

	public function HasAction($Alias) {
		return isset($this->_Actions->Items[$Alias]);
	}

	public function IsActionExecuted($Alias) {
		return $this->_Actions->Items[$Alias]->IsExecuted;
	}

	/* Control methods */

	public function AddControl($Alias, \Framework\Newnorth\Control $Control) {
		$this->_Controls->Items[$Alias] = $Control;
	}

	public function CloneControl($Alias, $NewAlias, array $Parameters = []) {
		return $this->_Controls->Add(
			$NewAlias,
			array_merge(
				$this->_Controls->Items[$Alias]->_Parameters,
				$Parameters
			)
		);
	}

	public function RemoveControl($Alias) {
		$this->_Controls->Items[$Alias]->Destroy();

		unset($this->_Controls->Items[$Alias]);
	}

	public function GetControl($Alias) {
		return $this->_Controls->Items[$Alias];
	}

	public function HasControl($Alias) {
		return isset($this->_Controls->Items[$Alias]);
	}

	public function RenderControl($Alias, $Placeholder = null, $Return = false, $Parameters = []) {
		return $this->_Controls->Items[$Alias]->Render($Placeholder, $Return, $Parameters);
	}

	/* Instance methods */

	public function GetTranslation($Key, $DefaultValue = null) {
		return isset($this->_Translations[$Key]) ? $this->_Translations[$Key] : $DefaultValue;
	}

	public function SetTranslation($Key, $Value) {
		$this->_Translations[$Key] = $Value;
	}

	/* Pre formatter methods */

	public function PreFormatters»Trim($Parameters) {
		eval($Parameters['Variable'].' = trim('.$Parameters['Variable'].');');
	}

	/* Validator methods */

	public function Validators»IsBetweenExclusive($Parameters) {
		return eval('return $Parameters[\'Min\'] < '.$Parameters['Variable'].' && '.$Parameters['Variable'].' < $Parameters[\'Max\'];');
	}

	public function Validators»IsBetweenInclusive($Parameters) {
		return eval('return $Parameters[\'Min\'] <= '.$Parameters['Variable'].' && '.$Parameters['Variable'].' <= $Parameters[\'Max\'];');
	}

	public function Validators»IsEMailAddress($Parameters) {
		return eval('return 0 < preg_match(\'/^([a-zA-Z0-9_.-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]+)?$/\', '.$Parameters['Variable'].');');
	}

	public function Validators»IsFloat($Parameters) {
		$Value = eval('return '.$Parameters['Variable'].';');

		if($Parameters['AllowEmpty']) {
			if(isset($Value[0])) {
				return is_numeric($Value);
			}
			else {
				return true;
			}
		}
		else {
			return is_numeric($Value);
		}
	}

	public function Validators»IsInt($Parameters) {
		$Value = eval('return '.$Parameters['Variable'].';');

		if($Parameters['AllowEmpty']) {
			if(isset($Value[0])) {
				return ctype_digit($Value);
			}
			else {
				return true;
			}
		}
		else {
			return ctype_digit($Value);
		}
	}

	public function Validators»MaxLength($Parameters) {
		return eval('return isset('.$Parameters['Variable'].'[$Parameters[\'Max\']]);');
	}

	public function Validators»MinLength($Parameters) {
		return eval('return isset('.$Parameters['Variable'].'[$Parameters[\'Min\']]);');
	}

	public function Validators»NotEmpty($Parameters) {
		return eval('return isset('.$Parameters['Variable'].'[0]);');
	}
}
?>