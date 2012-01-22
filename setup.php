<?php
	include 'class/timeline.class.php';

	if (Timeline::checkAndUpdateTable() == 'created')
		Timeline::insertTestData();
	
	$debugmessages = Log::getInstance()->getDebugMsg();
	foreach($debugmessages as $msg) {
		echo "<div class=\"msg debug\">".$msg."</div>\n";
	}
?>
