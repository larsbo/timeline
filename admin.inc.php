<?php
session_start();

require_once 'class/admin.class.php';

if (!Admin::loggedIn()) die('not logged in!');


$id = DB::escape($_GET['id']);
$action = DB::escape($_GET['action']);

switch ($action) {
	case 'show':
	echo Admin::showEvent($id);
	break;

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

	case 'refresh':
	echo Admin::showEvents();
	break;

	default:
	echo false;
	break;
}
?>