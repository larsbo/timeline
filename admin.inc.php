<?php
session_start();

require_once 'class/admin.class.php';

if (!Admin::loggedIn()) die('not logged in!');


$action = DB::escape($_GET['action']);

switch ($action) {
	case 'show':
	$id = DB::escape($_GET['id']);
	echo Admin::showEvent($id);
	break;

	case 'insert':
	echo Admin::insertEvent();
	break;

	case 'save':
	echo Admin::saveEvent();
	break;

	case 'edit':
	$id = DB::escape($_GET['id']);
	echo Admin::editEvent($id);
	break;

	case 'update':
	$id = DB::escape($_GET['id']);
	echo Admin::updateEvent($id);
	break;

	case 'deleteconfirmation':
	$id = DB::escape($_GET['id']);
	echo Admin::deleteEventConfirmation($id);
	break;

	case 'refresh':
	echo Admin::getEvents();
	break;

	case 'databaseRefresh':
	$insertdata = trim($_GET['insert']);
	echo Admin::checkAndUpdateTable($insertdata?true:false);
	break;

	default:
	echo false;
	break;
}
?>
