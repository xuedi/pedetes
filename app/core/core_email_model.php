<?php
namespace Pedetes\core;

use \PDO;
use PHPMailer;

class core_email_model extends \Pedetes\model {

	var $body;
	var $subject;
	var $address;
	var $from;
	var $smtp;
	var $debug;

	function __construct($ctn) {
		parent::__construct($ctn);
		$this->smtp = $this->ctn['config']['smtp'];
		$this->debug = false;
		$this->pebug->log( "core_email_model::__construct()" );
	}

	public function send() {
		$mail = new PHPMailer;
		$mail->SMTPDebug = $this->debug;
		$mail->isSMTP();
		$mail->isHTML(false);
		$mail->Host = $this->smtp['host'];
		$mail->SMTPAuth = true;
		$mail->Username = $this->smtp['user'];
		$mail->Password = $this->smtp['pass'];
		$mail->SMTPSecure = 'tls';
		$mail->Port = $this->smtp['port'];
		$mail->setFrom($this->from);
		$mail->addAddress($this->address);
		$mail->Subject = $this->subject;
		$mail->Body    = $this->body;
		$mail->send();
	}

}
