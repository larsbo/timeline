<?php
session_start();

require_once 'class/admin.class.php';

if (!Admin::loggedIn()) die('not logged in!');


$action = DB::escape($_POST['action']);
$log = array();

switch ($action) {
	case 'show':
	$id = DB::escape($_POST['id']);
	$log['result'] = Admin::showEvent($id);
	break;

	case 'insert':
	$log['result'] = Admin::getInsertEventForm();
	break;

	case 'save':
	$log['result'] = Admin::saveEvent($_POST);
	break;

	case 'edit':
	$id = DB::escape($_POST['id']);
	$log['result'] = Admin::editEvent($id);
	break;

	case 'update':
	$id = DB::escape($_POST['id']);
	$log['result'] = Admin::updateEvent($id, $_POST);
	break;

	case 'deleteconfirmation':
	$id = DB::escape($_POST['id']);
	$log['result'] = Admin::deleteEventConfirmation($id);
	break;

	case 'refresh':
	$log['result'] = Admin::getEvents();
	break;

	case 'databaseRefresh':
	$insertdata = trim($_POST['insert']);
	$log['result'] = Admin::checkAndUpdateTable($insertdata?true:false);
	break;

	case 'dropAndInsertTestData':
	$log['result'] = Admin::dropTables() && Admin::checkAndUpdateTable(true);
	break;

	default:
	$log['result'] = false;
	break;
	
}
$log['debug'] = Log::getDebugMsg();
echo json_encode($log);
?>
