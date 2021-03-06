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
