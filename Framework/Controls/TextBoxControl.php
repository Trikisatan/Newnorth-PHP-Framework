<?
namespace Framework\Controls;

use \Framework\Newnorth\Control;
use \Framework\Newnorth\ConfigException;

class TextBoxControl extends Control {
	/* Life cycle methods */

	public function Initialize() {

	}

	public function Load() {

	}

	public function Execute() {

	}

	/* Methods */

	public function ParseParameters() {
		$this->_Parameters['IsDisabled'] = isset($this->_Parameters['IsDisabled']) ? $this->_Parameters['IsDisabled'] === true : false;

		$this->_Parameters['IsReadOnly'] = isset($this->_Parameters['IsReadOnly']) ? $this->_Parameters['IsReadOnly'] === true : false;

		if(isset($this->_Parameters['Validators'])) {
			$this->ParseParameters_Validators($this->_Parameters['Validators']);
		}
	}

	private function ParseParameters_Validators($Validators) {
		$this->_Parameters['Validators'] = [];

		foreach($Validators as $Method => $Parameters) {
			$Method = 'Render'.$Method.'Validator';

			if(!$this->GetValidatorRenderMethod($Method, $Owner)) {
				throw new ConfigException(
					'Unable to find validator render method.',
					[
						'Object' => $this->__toString(),
						'Method' => $Method,
					]
				);
			}

			$this->_Parameters['Validators'][] = [
				'Owner' => $Owner,
				'Method' => $Method,
				'Parameters' => $Parameters,
				'ErrorMessage' => $Parameters['ErrorMessage'],
			];
		}
	}

	public function AutoFill($Value) {
		$this->_Parameters['Value'] = $Value;
	}
}
?>