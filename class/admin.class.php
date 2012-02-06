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

		$output = "<span class=\"new event\">Neues Ereignis eintragen</span><br /><br />
							 <ul>";
		foreach ($result as $event) {
			$output .= "<li class=\"eventContainer\" data-id=\"".$event['event_id']."\">
										<span title=\"anzeigen\" class=\"event\">".$event['title']."</span>
										<span title=\"bearbeiten\" class=\"button edit\"></span>
										<span title=\"l&ouml;schen\" class=\"button delete\"></span>
									</li>";
		}
		$output .= "</ul>";
		return $output;
	}

	static function showEvent($id) {
		$event = DB::queryAssocAtom("SELECT * FROM `events` WHERE `event_id` = '".$id."'");
		switch ($event['colorclass']) {
			case 'red': $category = "Politik"; break;
			case 'yellow': $category = "Gesellschaft"; break;
			case 'green': $category = "Religion"; break;
			case 'blue': $category = "Wissenschaft"; break;
		}
		return "<p><b>Titel:</b> ".$event['title']."</p>
						<p><b>Start:</b> ".$event['start_year']."</p>
						<p><b>Ende:</b> ".$event['end_year']."</p>
						<p><b>Kategorie:</b> ".$category."</p>
						<p>".$event['details']."</p>";
	}

	static function insertEvent() {
		return "<form data-action=\"save\">
							<label for=\"title\">Titel:</label>
							<input type=\"text\" name=\"title\" id=\"title\" size=\"62\" />
							<label for=\"start\">Start:</label>
							<input type=\"text\" name=\"start\" id=\"start\" size=\"10\" />
							<label for=\"end\">Ende:</label>
							<input type=\"text\" name=\"end\" id=\"end\" size=\"10\" />
							<label for=\"colorclass\">Kategorie:</label>
							<select name=\"colorclass\" id=\"colorclass\">
								<option value=\"red\">Politik</option>
								<option value=\"yellow\">Gesellschaft</option>
								<option value=\"green\">Religion</option>
								<option value=\"blue\">Wissenschaft</option>
							</select>
							<textarea name=\"details\" rows=\"10\" cols=\"50\"></textarea>
							<input type=\"submit\" value=\"Speichern\" />
						</form>";
	}

	static function saveEvent() {
		$title = mysql_real_escape_string($_GET['title']);
		$start = mysql_real_escape_string($_GET['start']);
		$end = mysql_real_escape_string($_GET['end']);
		$details = mysql_real_escape_string($_GET['details']);
		$colorclass = mysql_real_escape_string($_GET['colorclass']);
		$sql = "INSERT INTO `events` (`title`, `start_year`, `end_year`, `details`, `colorclass`) 
VALUES ('".$title."', '".$start."', '".$end."', '".$details."', '".$colorclass."')";
		DB::execute($sql);
		return "Ereignis erfolgreich eingetragen!";
	}

	static function editEvent($id) {
		$event = DB::queryAssocAtom("SELECT * FROM `events` WHERE `event_id` = '".$id."'");
		switch ($event['colorclass']) {
			case 'red': $politik = " selected=\"selected\""; break;
			case 'yellow': $gesellschaft = " selected=\"selected\""; break;
			case 'green': $religion = " selected=\"selected\""; break;
			case 'blue': $wissenschaft = " selected=\"selected\""; break;
		}
		return "<form data-action=\"update\">
							<input type=\"hidden\" name=\"id\" value=\"".$event['event_id']."\" />
							<label for=\"title\">Titel:</label>
							<input type=\"text\" name=\"title\" id=\"title\" value=\"".$event['title']."\" size=\"62\" />
							<label for=\"start\">Start:</label>
							<input type=\"text\" name=\"start\" id=\"start\" value=\"".$event['start_year']."\" size=\"10\" />
							<label for=\"end\">Ende:</label>
							<input type=\"text\" name=\"end\" id=\"end\" value=\"".$event['end_year']."\" size=\"10\" />
							<label for=\"colorclass\">Kategorie:</label>
							<select name=\"colorclass\" id=\"colorclass\">
								<option value=\"red\"".$politik.">Politik</option>
								<option value=\"yellow\"".$gesellschaft.">Gesellschaft</option>
								<option value=\"green\"".$religion.">Religion</option>
								<option value=\"blue\"".$wissenschaft.">Wissenschaft</option>
							</select>
							<textarea name=\"details\" rows=\"10\" cols=\"50\">".$event['details']."</textarea>
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
																	`start_year` = '".$start."',
																	`end_year` = '".$end."',
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