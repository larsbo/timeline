<?php
require_once('db.class.php');

class Admin {
	private $user;

	static function login($name, $pass) {
		$result = DB::queryAssocAtom("SELECT name FROM `users` WHERE `name` = '".$name."' AND `pass` = '".md5($pass)."' LIMIT 1");

		if ($result) {
			Admin::setSession('admin', $result['name']);
			return true;
		} else {
			return false;
		}
	}

	static function logout() {
		Admin::unsetSession('admin');
	}

	static function loggedIn() {
		return isset($_SESSION['admin']);
	}

	private static function setSession($name, $value) {
		$_SESSION[$name] = $value;
	}

	private static function unsetSession($name) {
		unset($_SESSION[$name]);
	}


	static function showEvents() {
		$result = DB::queryAssoc("SELECT * FROM `events` ORDER BY `start_year`");

		$output = "<ul>";
		foreach ($result as $event) {
			$output .= "<li class=\"eventContainer\" data-id=\"".$event['event_id']."\">
										<span class=\"event\">".$event['title']."</span>
										<span title=\"bearbeiten\" class=\"button edit\"></span>
										<span title=\"l&ouml;schen\" class=\"button delete\"></span>
									</li>";
		}
		$output .= "</ul>";
		return $output;
	}

	static function insertEvent() {
		return "<form data-action=\"save\">
							<input type=\"text\" name=\"title\" size=\"60\" />
							<label for=\"start\">Start-Datum:</label>
							<input type=\"text\" name=\"start\" id=\"start\" size=\"10\" />
							<label for=\"end\">End-Datum:</label>
							<input type=\"text\" name=\"end\" id=\"end\" size=\"10\" />
							<label for=\"colorclass\">Kategorie:</label>
							<input type=\"text\" name=\"colorclass\" id=\"colorclass\" size=\"10\" />
							<textarea name=\"details\" rows=\"10\" cols=\"45\"></textarea>
							<input type=\"submit\" value=\"Speichern\" />
						</form>";
	}

	static function saveEvent() {
		$title = mysql_real_escape_string($_GET['title']);
		$start = mysql_real_escape_string($_GET['start']);
		$end = mysql_real_escape_string($_GET['end']);
		$details = mysql_real_escape_string($_GET['details']);
		$colorclass = mysql_real_escape_string($_GET['colorclass']);
		$result = DB::execute("INSERT INTO `events` (title, start, end, details, colorclass), 
														VALUES ('".$title."', '".$start."', '".$end."', '".$details."', '".$colorclass."')");
		return "Ereignis erfolgreich eingetragen!";
	}

	static function editEvent($id) {
		$event = DB::queryAssocAtom("SELECT * FROM `events` WHERE `event_id` = '".$id."'");
		return "<form data-action=\"update\">
							<input type=\"hidden\" name=\"id\" value=\"".$event['event_id']."\" />
							<input type=\"text\" name=\"title\" value=\"".$event['title']."\" size=\"60\" />
							<label for=\"start\">Start-Datum:</label>
							<input type=\"text\" name=\"start\" id=\"start\" value=\"".$event['start_year']."\" size=\"10\" />
							<label for=\"end\">End-Datum:</label>
							<input type=\"text\" name=\"end\" id=\"end\" value=\"".$event['end_year']."\" size=\"10\" />
							<label for=\"colorclass\">Kategorie:</label>
							<input type=\"text\" name=\"colorclass\" id=\"colorclass\" value=\"".$event['colorclass']."\" size=\"10\" />
							<textarea name=\"details\" rows=\"10\" cols=\"45\">".$event['details']."</textarea>
							<input type=\"submit\" value=\"Speichern\" />
						</form>";
	}

	static function updateEvent($id) {
		$title = mysql_real_escape_string($_GET['title']);
		$start = mysql_real_escape_string($_GET['start']);
		$end = mysql_real_escape_string($_GET['end']);
		$details = mysql_real_escape_string($_GET['details']);
		$colorclass = mysql_real_escape_string($_GET['colorclass']);
		$result = DB::execute("UPDATE `events`
															SET `title` = '".$title."', 
																	`start` = '".$start."',
																	`end` = '".$end."',
																	`details` = '".$details."', 
																	`colorclass` = '".$colorclass."' 
														WHERE `event_id` = '".$id."'");
		return "Ereignis ".$id." erfolgreich bearbeitet!";
	}

	static function deleteEvent($id) {
		return "<p>Soll das Ereignis ".$id." wirklich gel&ouml;scht werden?</p>
						<p>
							<form data-id=\"".$id."\">
								<input name=\"no\" type=\"button\" value=\"abbrechen\" />
								<input name=\"yes\" type=\"button\" value=\"l&ouml;schen\" />
							</form>
						</p>";
	}

	static function deleteEventConfirmation($id) {
		$result = DB::execute("DELETE FROM `events` WHERE `event_id` = '".$id."'");
		return "Ereignis ".$id." erfolgreich gel&ouml;scht!";
	}

}
?>