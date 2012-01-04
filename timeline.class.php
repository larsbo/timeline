<?php
class Timeline {
	private $events;
	private $details;
	private $start;
	private $end;

	function __Construct($start, $end) {
		$events = $this->getEvents();

		// events
		$this->events  = "    <table id=\"timeline\" class=\"bordered\">
      <thead>
        <tr>\n";
		for ($year = $start; $year < $end; $year++) {
			$this->events .= "          <th>".$year."</th>\n";
		}
		$this->events .= "        </tr>
      </thead>
      <tbody>
        <tr id=\"content\">\n";
		for ($year = $start; $year < $end; $year++) {
			$this->events .= "          <td>";
			foreach ($events as $event) {
				if ($event->start == $year) {
					$this->events .= "
            <span class=\"event\" data-event=\"".$event->event_id."\" data-title=\"".$event->title."\">".$event->title."</span>\n          ";
				}
			}
			$this->events .= "</td>\n";
		}
		$this->events .= "        </tbody>
      </tr>
    </table>\n";

		// details
		foreach ($events as $event) {
			$this->details  .= "<div id=\"event-".$event->event_id."\" class=\"event-details\">".$event->details."</div>\n";
		}

	}

	function getEvents() {
		$events = array();

		$query = mysql_query('select * from events');
		while ($event = mysql_fetch_object($query)) {
			$events[] = $event;
		}
		return $events;
	}

	function output($data) {
		echo $this->$data;
	}
}
?>
