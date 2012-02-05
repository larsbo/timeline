<?php
session_start();

require_once 'class/admin.class.php';

// submit form
if ($_POST['username'] && $_POST['password']) {
	$user = mysql_real_escape_string($_POST['username']);
	$pass = mysql_real_escape_string($_POST['password']);
	$login = Admin::login($user, $pass);
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Timeline - Admin</title>
  <link rel="stylesheet" type="text/css" href="css/style.css" />
  <script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
  <script type="text/javascript" src="js/admin.js"></script>
</head>
<body class="adminContent">
<?php
if (Admin::loggedIn()) {
	// logged in

?>
<h2>Eingetragene Ereignisse</h2>
<div class="bordered" id="eventList">
  <span class="new event">Neues Ereignis einf&uuml;gen</span><br /><br />
<?php echo Admin::showEvents(); ?>
</div>
<div class="bordered" id="eventDetails"></div>
<?php
} else {
	// show login form

	if ($_POST['username'] && $_POST['password'] && !$login) {
		echo "Login fehlgeschlagen!";
	}
?>
<form name="login" method="post" action="">
  <label for="username">Benutzername:</label>
  <input type="text" id="username" name="username" />
  <label for="password">Passwort:</label>
  <input type="password" id="password" name="password" />
  <input type="submit" value="Login" />
</form>
<?php
}
?>
</body>
</html>