<?php
session_start();

require_once 'class/admin.class.php';

if (!Admin::loggedIn()) die('not logged in!');

$id = mysql_real_escape_string($_GET['id']);
$action = mysql_real_escape_string($_GET['action']);

switch ($action) {
	case 'insert':
	echo Admin::insertEvent();
	break;

	case 'save':
	echo Admin::saveEvent();
	break;

	case 'edit':
	echo Admin::editEvent($id);
	break;

	case 'update':
	echo Admin::updateEvent($id);
	break;

	case 'delete':
	echo Admin::deleteEvent($id);
	break;

	case 'deleteconfirmation':
	echo Admin::deleteEventConfirmation($id);
	break;

	default:
	echo false;
	break;
}
?>