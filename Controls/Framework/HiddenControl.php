<?
namespace Framework;

class HiddenControl extends \Framework\Newnorth\Control {
	/* Life cycle methods */

	public function PostExecute() {
		parent::PostExecute();

		if(method_exists($this->_Parent, 'SetControlValue_'.$this->_Alias)) {
			$this->_Parameters['Value'] = $this->_Parent->{'SetControlValue_'.$this->_Alias}($this);
		}
	}
}
?>