<?php

ini_set("error_reporting", E_ALL & ~E_NOTICE );

class Config {
	private static $oInstance = null;

	public $db_host = "localhost";
	public $db_name = "";
	public $db_user = "";
	public $db_passwd = "";
	public $db_port = 3306;

	public $mail_sender = "noreply@cnlpete.de";
	public $mail_sender_name = "CnlPete";
	public $mail_replyto = "support@cnlpete.de";
	public $mail_replyto_name = "CnlPete.de Support";

	public $page_ref = "http://timeline.cnlpete.de/";

	public $tl_column_width = 100;
	public $tl_event_padding_x = 26;
	public $tl_event_padding_y = 40;

	private function Config() {
		$host = $_SERVER['HTTP_HOST'];

		switch ($host) {
		case 'sowas-such-ich.de':
			$this->db_host = 'localhost';
			$this->db_user = 'web25';
			$this->db_passwd = 'borchert42';
			$this->db_name = 'usr_web25_1';
			break;

		default:
			$this->db_host = 'localhost';
			$this->db_user = 'timeline_user';
			$this->db_passwd = 'timeline_pass';
			$this->db_name = 'timeline';
		}
	}

	static function getInstance() {
		if( !Config::$oInstance ) {
			Config::$oInstance = new Config();
		}
		if( !Config::$oInstance ) {
			Log::critical('Could not create Config singleton.');
		}

		return Config::$oInstance;
	}

}

?>
