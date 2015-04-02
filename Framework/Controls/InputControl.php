<?
namespace Framework\Controls;

abstract class InputControl extends \Framework\Newnorth\Control {
	/* Life cycle methods */

	protected function ParseParameters_Validators($Validators) {
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

	public function PostLoad() {
		$this->SetValue();

		parent::PostLoad();
	}

	public function SetValue() {

	}
}
?>