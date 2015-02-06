<?
namespace Framework\Controls;

use \Framework\Newnorth\Control;
use \Framework\Newnorth\ConfigException;

class DropDownListControl extends Control {
	/* Life cycle methods */

	public function Initialize() {

	}

	public function Load() {

	}

	public function Execute() {

	}

	/* Methods */

	public function ParseParameters() {
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

	public function AddOption($Value, $Text) {
		if(isset($this->_Parameters['Options'])) {
			$this->_Parameters['Options'][$Value] = $Text;
		}
		else {
			$this->_Parameters['Options'] = [$Value => $Text];
		}
	}
}
?>