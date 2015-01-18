<?php
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

	public $_Validators;

	public $_Renderer = '\Framework\Newnorth\HtmlRenderer';

	/* Magic methods */

	public function __construct($Directory, $Name) {
		if(Page::$Instance !== null) {
			ConfigError(
				'An instance of the page has already been initialized.'
			);
		}

		Page::$Instance = $this;

		$this->_Directory = $Directory;
		$this->_Name = $Name;
		$this->_Translations = new Translations($Directory.$Name.'/');
		$this->_Controls = new Controls($this, $Directory.$Name.'/');
		$this->_Actions = new Actions($this, $Directory.$Name.'/');
		$this->_Validators = new Validators();
	}

	public function __toString() {
		return $this->_Directory.$this->_Name;
	}

	/* Events */

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
}
?>