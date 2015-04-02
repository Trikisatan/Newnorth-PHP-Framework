<?
namespace Framework\Controls;

class FileUploadControl extends InputControl {
	/* Life cycle methods */

	public function ParseParameters() {
		if(isset($this->_Parameters['Validators'])) {
			$this->ParseParameters_Validators($this->_Parameters['Validators']);
		}
	}
}
?>