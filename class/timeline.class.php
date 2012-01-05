<?php
class Timeline {
	private $events;
	private $matrix;
	private $counter;
	private $start;
	private $end;
	private $events_output;
	private $details_output;

	function __Construct($start, $end) {
		$this->getEvents();
		$this->alignEvents();

		// show events
		$this->events_output  = "    <table id=\"timeline\" class=\"bordered\">
      <thead>
        <tr>\n";
		for ($year = $start; $year < $end; $year++) {
			$this->events_output .= "          <th width=\"".TL_COLUMN_WIDTH."\">".$year."</th>\n";
		}
		$this->events_output .= "        </tr>
      </thead>
      <tbody>
        <tr id=\"content\">\n";
		for ($year = $start; $year < $end; $year++) {
			$this->events_output .= "          <td>";
			foreach ($this->events as $event) {
				if ($event['start_year'] == $year) {
					$event['length'] = max(1, $event['end_year'] - $event['start_year'] + 1) * TL_COLUMN_WIDTH - TL_EVENT_PADDING_X;
					$event['line'] = $event['line'] * TL_EVENT_PADDING_Y;
					$this->events_output .= "
            <span class=\"event\" style=\"width:".$event['length']."px;top:".$event['line']."px\" data-event=\"".$event['event_id']."\" data-title=\"".$event['title']."\" data-width=\"".$event['length']."\">".$event['title']."</span>\n          ";
				}
			}
			$this->events_output .= "</td>\n";
		}
		$this->events_output .= "        </tbody>
      </tr>
    </table>\n";

		// details
		foreach ($this->events as $event) {
			$this->details_output  .= "<div id=\"event-".$event['event_id']."\" class=\"event-details\">".$event['details']."</div>\n";
		}
	}

	function getEvents() {
		$this->events = array();

		$result = mysql_query('select * from events');
		while ($event = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$this->events[] = $event;
		}

		usort($this->events, array($this, 'custom_sort'));

		$this->counter = sizeof($this->events);
	}

	function alignEvents() {
		$this->matrix = array();
		for ($i=0; $i<$this->counter; $i++) {
			$year = $this->events[$i]['start_year'];
			$line = 0;
			// suche freie zeile
			while ($this->matrix[$year][$line]) {
				$this->events[$i]['line']++;
				$line++;
			}
			// freie zeile gefunden -> diese zeile in matrix ueber laenge des events belegen
			for ($j = $year; $j <= $this->events[$i]['end_year']; $j++) {
				// laenge des events in matrix eintrage
				$this->matrix[$j][$line] = true;
			}
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
