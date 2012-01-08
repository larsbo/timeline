<?php
/* database */
$host = $_SERVER['HTTP_HOST'];

switch ($host) {
	case 'sowas-such-ich.de':
	define('DB_HOST', 'localhost');
	define('DB_USER', 'web25');
	define('DB_PASS', 'borchert42');
	define('DB_NAME', 'usr_web25_1');	
	break;

	default:
	define('DB_HOST', 'localhost');
	define('DB_USER', 'timeline_user');
	define('DB_PASS', 'timeline_pass');
	define('DB_NAME', 'timeline');	
}

/* timeline */
define('TL_COLUMN_WIDTH', 100);
define('TL_EVENT_PADDING_X', 26);
define('TL_EVENT_PADDING_Y', 40);
?>