<?
namespace Framework\Controls;

class ValueIntervalBoxControl extends \Framework\Newnorth\Control {
	/* Life cycle methods */

	public function Initialize() {

	}

	public function Load() {

	}

	public function Execute() {

	}

	/* Instance methods */

	public function ParseParameters() {
		$this->_Parameters['UseJavaScript'] = isset($this->_Parameters['UseJavaScript']) ? $this->_Parameters['UseJavaScript'] == true : false;

		$this->_Parameters['LowerIsDisabled'] = isset($this->_Parameters['LowerIsDisabled']) ? $this->_Parameters['LowerIsDisabled'] == true : false;

		$this->_Parameters['LowerIsReadOnly'] = isset($this->_Parameters['LowerIsReadOnly']) ? $this->_Parameters['LowerIsReadOnly'] == true : false;

		$this->_Parameters['UpperIsDisabled'] = isset($this->_Parameters['UpperIsDisabled']) ? $this->_Parameters['UpperIsDisabled'] == true : false;

		$this->_Parameters['UpperIsReadOnly'] = isset($this->_Parameters['UpperIsReadOnly']) ? $this->_Parameters['UpperIsReadOnly'] == true : false;

		if(isset($this->_Parameters['Validators'])) {
			$this->ParseParameters_Validators($this->_Parameters['Validators']);
		}
	}

	private function ParseParameters_Validators($Validators) {
		$this->_Parameters['Validators'] = [];

		foreach($Validators as $Method => $Parameters) {
			$Method = 'Render'.$Method.'Validator';

			if(!$this->GetValidatorRenderMethod($Method, $Owner)) {
				throw new \Framework\Newnorth\ConfigException(
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