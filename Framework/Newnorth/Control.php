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
		$this->Actions = new Actions($this, $Directory.$Name.'/');
	}
	public function __toString() {
		return $this->Directory.$this->Name;
	}

	/* Events */
	public function PreInitialize() {
		$this->Translations->Load();
		$this->Actions->Load();
		$this->Controls->PreInitialize();
	}
	public abstract function Initialize();
	public function PostInitialize() {
		$this->Controls->Initialize();
		$this->Controls->PostInitialize();
	}
	public function PreLoad() {
		$this->Controls->PreLoad();
	}
	public abstract function Load();
	public function PostLoad() {
		$this->Controls->Load();
		$this->Controls->PostLoad();
	}
	public function PreExecute() {
		$this->Actions->Execute();
		$this->Controls->PreExecute();
	}
	public abstract function Execute();
	public function PostExecute() {
		$this->Controls->Execute();
		$this->Controls->PostExecute();
	}
	public function Render() {
		HtmlRenderer::Render(
			$this,
			$this->Directory.$this->Name.'/Content.phtml',
			$this->Translations
		);
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
	public function GetControl($Alias) {
		return $this->Controls[$Alias];
	}
	public function RenderControl($Alias) {
		$this->Controls[$Alias]->Render();
	}
}
?>