<?php

require_once('db.class.php');

class Timeline {
	private $events;
	private $matrix;
	private $start_year;
	private $end_year;
	private $events_output;
	private $css_output;

	function __Construct($start, $end) {
		$this->start_year = $start;
		$this->end_year = $end;
		$this->events = $this->getEvents();
		$this->alignEvents();

		$this->css_output = $this->getColorclasses();
		$this->createEventsOutput();
	}

	function getEvents() {
		$sql = <<<EOD
SELECT e.event_id, e.title, e.details, e.start_year, e.end_year, e.colorclass
FROM events AS e;
EOD;
		$events = DB::queryAssoc($sql);
		// sort events by start_year
		usort($events, array($this, 'custom_sort'));
		return $events;
	}

	function getColorClasses() {
		$sql = <<<EOD
SELECT DISTINCT c.color_id, c.css_code AS css FROM `colorclasses` AS c
RIGHT JOIN events AS e ON e.colorclass = c.color_id;
EOD;

		$htmlcode = '<style type="text/css">';
		foreach (DB::queryAssoc($sql) as $colorclass) {
			if(!empty($colorclass['color_id']) && !empty($colorclass['css'])) {
				$htmlcode .= ".".$colorclass['color_id']." { \n";
				$htmlcode .= $colorclass['css']." }\n";
			}
		}
		$htmlcode .= '</style>';
		return $htmlcode;
	}

	function alignEvents() {
		$counter = sizeof($this->events);
		$this->matrix = array();
		for ($i=0; $i<$counter; $i++) {
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
\t<table id="timeline" class="bordered">
\t\t<thead>
\t\t\t<tr>\n
EOD;
		for ($year = $this->start_year; $year < $this->end_year; $year++) {
			$this->events_output .= "\t\t\t\t<th class=\"date\" style=\"width: ".$c->tl_column_width."px\">".$year."</th>\n";
		}
		$this->events_output .= <<<EOD
\t\t\t</tr>
\t\t</thead>
\t\t<tbody>
\t\t\t<tr id="content">\n
EOD;
		for ($year = $this->start_year; $year < $this->end_year; $year++) {
			$this->events_output .= "\t\t\t\t<td>\n";
			foreach ($this->events as $event) {
				if ($event['start_year'] == $year) {
					$event['length'] = max(1, $event['end_year'] - $event['start_year'] + 1) * $c->tl_column_width - $c->tl_event_padding_x;
					$event['line'] = $event['line'] * $c->tl_event_padding_y;
					$this->events_output .= <<<EOD
\t\t\t\t\t<div class="event-preview" style="zIndex: 0">
						<span class="event $event[colorclass]" 
									style="width:$event[length]px;top:$event[line]px;z-index:2;" 
									data-event="$event[event_id]" 
									data-title="$event[title]" 
									data-width="$event[length]">
							$event[title]
							<span class="pin"></span>
						</span>
						<div class="event-details" style="zIndex: 1">$event[details]</div>
					</div>\n
EOD;
				}
			}
			$this->events_output .= "\t\t\t\t</td>\n";
		}
		$this->events_output .= <<<EOD
\t\t\t</tr>
\t\t</tbody>
\t</table>\n
EOD;
	}

	function output($data) {
		if ($data == 'events') echo $this->events_output;
		if ($data == 'css') echo $this->css_output;
	}

	function custom_sort($a, $b) {
		return $a['start_year'] > $b['start_year'];
	}
}
?>