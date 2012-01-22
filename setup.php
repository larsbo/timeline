<?php
	include 'class/timeline.class.php';

	Timeline::checkAndUpdateTable();
	
	$debugmessages = Log::getInstance()->getDebugMsg();
	foreach($debugmessages as $msg) {
		echo "<div class=\"msg debug\">".$msg."</div>\n";
	}
?>
