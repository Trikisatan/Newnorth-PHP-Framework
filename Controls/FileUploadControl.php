<?
class FileUploadControl extends \Framework\Newnorth\Control {
	/* Validator methods */

	public function FileUploadedValidator($Parameters) {
		return 0 < $_FILES[$this->_Parameters['Name']]['size'];
	}
}
?>