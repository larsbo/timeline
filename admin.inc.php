<?php
session_start();

require_once 'class/admin.class.php';

if (!Admin::loggedIn()) die('not logged in!');

$id = mysql_real_escape_string($_GET['id']);
$action = mysql_real_escape_string($_GET['action']);

switch ($action) {
	case 'show':
	echo Admin::showEvent($id);
	break;

	case 'edit':
	echo Admin::editEvent($id);
	break;

	case 'delete':
	echo Admin::deleteEvent($id);
	break;

	case 'save':
	echo Admin::saveEvent($id);
	break;

	case 'update':
	echo Admin::updateEvent($id);
	break;

	default:
	echo false;
	break;
}
?>