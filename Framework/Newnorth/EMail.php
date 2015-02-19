<?
namespace Framework\Newnorth;

class EMail {
	/* Variables */

	private $From = null;

	private $ReplyTo = null;

	private $Subject = null;

	private $Text = null;

	private $Html = null;

	/* Methods */

	public function SetFrom($From) {
		$this->From = $From;
	}

	public function SetReplyTo($ReplyTo) {
		$this->ReplyTo = $ReplyTo;
	}

	public function SetSubject($Subject) {
		$this->Subject = $Subject;
	}

	public function SetText($Text) {
		$this->Text = $Text;
	}

	public function SetTextTemplate($Template, $Variables = []) {
		$FilePath = $Template.'.text.'.Application::GetLocale().'.phtml';

		$this->Text = file_get_contents($FilePath);

		foreach($Variables as $Key => $Value) {
			$this->Text = str_replace('%'.$Key.'%', $Value, $this->Text);
		}
	}

	public function SetHtml($Html) {
		$this->Html = $Html;
	}

	public function SetHtmlTemplate($Template, $Variables = []) {
		$FilePath = $Template.'.html.'.Application::GetLocale().'.phtml';

		$this->Html = file_get_contents($FilePath);

		foreach($Variables as $Key => $Value) {
			$this->Html = str_replace('%'.$Key.'%', $Value, $this->Html);
		}
	}

	public function Send($To) {
		$Rel = 'PHP-rel-'.md5(rand());
		$Alt = 'PHP-alt-'.md5(rand());
		$Newline = "\r\n";

		$Headers =
			'Content-Type: multipart/related; boundary="'.$Rel.'"'.$Newline.
			'Date: '.date('r');

		if($this->From !== null) {
			$Headers .= "\r\n".'From: '.$this->From;
		}

		if($this->ReplyTo !== null) {
			$Headers .= "\r\n".'Reply-To: '.$this->ReplyTo;
		}

		$Message = '';

		if($this->Text !== null && $this->Html !== null) {
			$Message .=
				'--'.$Rel.$Newline.
				'Content-Type: multipart/alternative; boundary="'.$Alt.'"'.$Newline.
				'--'.$Alt.$Newline.
				'Content-Type: text/plain; charset=UTF-8'.$Newline.
				'Content-Transfer-Encoding: quoted-printable'.$Newline.$Newline.
				quoted_printable_encode($this->Text).$Newline.$Newline.
				'--'.$Alt.$Newline.
				'Content-Type: text/html; charset=UTF-8'.$Newline.
				'Content-Transfer-Encoding: quoted-printable'.$Newline.$Newline.
				quoted_printable_encode($this->Html).$Newline.$Newline.
				'--'.$Alt.'--'.$Newline;
		}
		else if($this->Text !== null) {
			$Message .=
				'--'.$Rel.$Newline.
				'Content-Type: text/plain; charset=UTF-8'.$Newline.
				'Content-Transfer-Encoding: quoted-printable'.$Newline.$Newline.
				quoted_printable_encode($this->Text).$Newline.$Newline;
		}
		else if($this->Html !== null) {
			$Message .=
				'--'.$Rel.$Newline.
				'Content-Type: text/html; charset=UTF-8'.$Newline.
				'Content-Transfer-Encoding: quoted-printable'.$Newline.$Newline.
				quoted_printable_encode($this->Html).$Newline.$Newline;
		}

		if(isset($Message[0])) {
			$Message .= '--'.$Rel.'--';
		}

		return mail($To, $this->Subject, $Message, $Headers);
	}
}
?>