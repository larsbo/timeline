<?php

require_once('db.class.php');

class Timeline {
	private $events;
	private $matrix;
	private $counter;
	private $start_year;
	private $end_year;
	private $events_output;
	private $details_output;

	function __Construct($start, $end) {
		$this->start_year = $start;
		$this->end_year = $end;
		$this->getEvents();
		$this->alignEvents();
		$this->createEventsOutput();
		$this->createDetailsOutput();
	}


	static function checkAndUpdateTable() {
		$tablediff = DB::checkForTable('events', 
			array('details', 'end_year', 'event_id', 'start_year', 'title'));
			//this has to be sorted ...
		
		//the table is in some wrong state .... need to update or create
		
		if ($tablediff === null) {
			//table is missing?
			Log::debug("we have to create the table");
			$sql = <<<EOD
CREATE TABLE IF NOT EXISTS `events` (
  `event_id` INT( 11 ) NOT NULL AUTO_INCREMENT,
  `title` varchar(30) NOT NULL,
  `details` text NOT NULL,
  `start_year` int(4) NOT NULL,
  `end_year` int(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `start_year` (`start_year`),
  KEY `end_year` (`end_year`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
EOD;

			Log::debug("got: '".implode(",", DB::queryAssoc($sql))."'");
			return true;
		}
		else if (sizeof($tablediff) > 0) {
			//all fields in $tablediff are missing...
			Log::debug("the table is missing these fields: ".implode(",", $tablediff));
			//TODO alter table ....
			$sql = <<<EOD
ALTER TABLE `events` ADD `test` TEXT NOT NULL, `testTwo` TEXT NOT NULL;
EOD;
			return true;
		}
		else
			return true;
	}

	static function insertTestData() {
		//TODO
	}

	function getEvents() {
		$this->events = DB::queryAssoc('SELECT * FROM events');
		// sort events by start_year
		usort($this->events, array($this, 'custom_sort'));
		// count events
		$this->counter = sizeof($this->events);
	}


	function alignEvents() {
		$this->matrix = array();
		for ($i=0; $i<$this->counter; $i++) {
			$year = $this->events[$i]['start_year'];
			$line = 0;
			// search for the first free row
			while ($this->matrix[$year][$line]) {
				$this->events[$i]['line']++;
				$line++;
			}
			// found free row -> mark columns (=years) of this row in matrix
			for ($j = $year; $j <= $this->events[$i]['end_year']; $j++) {
				$this->matrix[$j][$line] = true;
			}
		}
	}


	function createEventsOutput() {
		$c = Config::getInstance();

		$this->events_output = <<<EOD
\t<table id=\"timeline\" class=\"bordered\">
\t\t<thead>
\t\t\t<tr>
EOD;
		for ($year = $this->start_year; $year < $this->end_year; $year++) {
			$this->events_output .= "\t\t\t\t<th width=\"".$c->tl_column_width."\">".$year."</th>\n";
		}
		$this->events_output .= <<<EOD
\t\t\t</tr>
\t\t</thead>
\t\t<tbody>
\t\t\t<tr id=\"content\">
EOD;
		for ($year = $this->start_year; $year < $this->end_year; $year++) {
			$this->events_output .= "\t\t\t\t<td>";
			foreach ($this->events as $event) {
				if ($event['start_year'] == $year) {
					$event['length'] = max(1, $event['end_year'] - $event['start_year'] + 1) * $c->tl_column_width - $c->tl_event_padding_x;
					$event['line'] = $event['line'] * $c->tl_event_padding_y;
					$this->events_output .= "\t\t\t\t\t<span class=\"event\" style=\"width:".$event['length']."px;top:".$event['line']."px\" data-event=\"".$event['event_id']."\" data-title=\"".$event['title']."\" data-width=\"".$event['length']."\">".$event['title']."</span>";
				}
			}
			$this->events_output .= "\t\t\t\t</td>\n";
		}
		$this->events_output .= <<<EOD
\t\t\t</tr>
\t\t</tbody>
\t</table>
EOD;
	}


	function createDetailsOutput() {
		foreach ($this->events as $event) {
			$this->details_output .= "\t\t<div id=\"event-".$event['event_id']."\" class=\"event-detail\">".$event['details']."</div>\n";
		}
	}


	function output($data) {
		if ($data == 'events') echo $this->events_output;
		if ($data == 'details') echo $this->details_output;
	}


	function custom_sort($a, $b) {
		return $a['start_year'] > $b['start_year'];
	}
}
?>
