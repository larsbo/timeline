<?php

class Log {
	private $debug_msg = Array();
	private static $oInstance = null;

	static function getInstance() {
		if( !Log::$oInstance ) {
			Log::$oInstance = new Log();
		}
		if( !Log::$oInstance ) {
			Log::critical('Could not create Log singleton.');
		}			
		
		return Log::$oInstance;
		
	}
	
	function Log() {
	}

	public static function error($sMessage) {
		Log::showMessage("[E] ".$sMessage, 'error');
	}

	public static function warn($sMessage) {
		Log::showMessage("[W] ".$sMessage, 'warning');
	}

	public static function critical($sMessage, $bDie = true) {
		Log::showMessage("[C] ".$sMessage, 'critical');
		die('');
	}

	public static function debug($sMessage) {
		Log::getInstance()->debug_msg[] = $sMessage;
	}

	public function getDebugMsg() {
		return $this->debug_msg;
	}

	private static function showMessage($msg, $t = "error") {
		echo '<div class="msg '.$t.' immediate">'
			.'<h3>Fehler!</h3><p>'.$msg.'</p></div>\n';
	}
}
?>
