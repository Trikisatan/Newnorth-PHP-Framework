<?php
namespace Framework\Newnorth;

abstract class Page {
	/* Static variables */
	public static $Instance = null;

	/* Variables */
	public $Directory;
	public $Name;
	public $Translations;
	public $Controls;
	public $Actions;
	public $Validators;
	public $Renderer = '\Framework\Newnorth\HtmlRenderer';

	/* Magic methods */
	public function __construct($Directory, $Name) {
		if(Page::$Instance !== null) {
			ConfigError(
				'An instance of the layout has already been initialized.'
			);
		}

		Page::$Instance = $this;
		$this->Directory = $Directory;
		$this->Name = $Name;
		$this->Translations = new Translations($Directory.$Name.'/');
		$this->Controls = new Controls($this, $Directory.$Name.'/');
		$this->Actions = new Actions($this, $Directory.$Name.'/');
		$this->Validators = new Validators();
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
	public function Render($PlaceHolder = null) {
		call_user_func($this->Renderer.'::Render', $this, $PlaceHolder);
	}

	/* Static methods */
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