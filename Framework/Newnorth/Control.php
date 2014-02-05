<?php
namespace Framework\Newnorth;

abstract class Control extends Validators {
	/* Variables */
	private $Parent;
	private $Directory;
	private $Name;
	private $Translations;
	private $Controls;
	private $Actions;

	/* Magic methods */
	public function __construct($Parent, $Directory, $Name) {
		$this->Parent = $Parent;
		$this->Directory = $Directory;
		$this->Name = $Name;
		$this->Translations = new Translations($Directory.$Name.'/');
		$this->Controls = new Controls($Directory.$Name.'/');
		$this->Actions = new Actions($this, $this->Directory.$this->Name.'/');
	}
	public function __toString() {
		return $this->Directory.$this->Name;
	}

	/* Events */
	public function PreInitialize() {
		$this->Translations->Load();
		$this->Controls->Load();
		$this->Actions->Load();

		foreach($this->Controls as $Control) {
			$Control->PreInitialize();
		}
	}
	public abstract function Initialize();
	public function PostInitialize() {
		foreach($this->Controls as $Control) {
			$Control->Initialize();
		}

		foreach($this->Controls as $Control) {
			$Control->PostInitialize();
		}
	}
	public function PreLoad() {
		foreach($this->Controls as $Control) {
			$Control->PreLoad();
		}
	}
	public abstract function Load();
	public function PostLoad() {
		foreach($this->Controls as $Control) {
			$Control->Load();
		}

		foreach($this->Controls as $Control) {
			$Control->PostLoad();
		}
	}
	public function PreExecute() {
		$this->Actions->Execute();

		foreach($this->Controls as $Control) {
			$Control->PreExecute();
		}
	}
	public abstract function Execute();
	public function PostExecute() {
		foreach($this->Controls as $Control) {
			$Control->Execute();
		}

		foreach($this->Controls as $Control) {
			$Control->PostExecute();
		}
	}
	public function Render() {
		$Application = Application::GetInstance();

		$Output[0] = ob_get_contents();
		ob_clean();

		include('Application/'.$this->Directory.$this->Name.'/Content.phtml');

		$Output[1] = ob_get_contents();
		ob_clean();

		$this->Translations->Translate($Output[1]);

		echo $Output[0].$Output[1];
	}

	/* Methods */
	public function GetControlDirectory() {
		return $this->Directory;
	}
	public function GetControlName() {
		return $this->Name;
	}
	public function GetControlParent() {
		return $this->_Parent;
	}
	public function GetTranslation($Key, $DefaultValue = null) {
		return isset($this->Translations[$Key]) ? $this->Translations[$Key] : $DefaultValue;
	}
	public function SetTranslation($Key, $Value) {
		$this->Translations[$Key] = $Value;
	}
	public function RenderControl($Alias) {
		$this->Controls[$Alias]->Render();
	}
}
?>