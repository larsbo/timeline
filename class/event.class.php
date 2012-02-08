<?php

require_once 'config.class.php';
require_once 'db.class.php';

class Event {

	private $event_id;
	private $title;
	private $details;
	private $startdate;
	private $enddate;
	private $colorclass;
	
	private $width = -1;	//cache for event representations width

	function __Construct($event_id, $title, $details, $startdate, $enddate, $colorclass) {
		$this->event_id = $event_id;
		$this->title = $title;
		$this->details = $details;
		$this->startdate = $startdate;
		$this->enddate = $enddate;
		$this->colorclass = $colorclass;
	}
	
	/** getter **/
	function getId() {
		return $this->event_id;
	}
	function getColorclass() {
		return $this->colorclass;
	}
	function getTitle() {
		return $this->title;
	}
	
	function getStartYear() {
		return intval(substr($this->startdate, 0, 4));
	}
	function getEndYear() {	//FIXME, should returns tartdate, if invalid enddate is given...
		if (substr($this->enddate, 0, 4))
			return intval(substr($this->enddate, 0, 4));
		else
			return intval(substr($this->startdate, 0, 4));
	}
	
	function getPixelWidth() {
		if ($this->width == -1) {
			$c = Config::getInstance();
			$pixelWidthPerYear = $c->tl_column_width;
			$this->width = max(1, $this->getEndYear() - $this->getStartYear() + 1) * $pixelWidthPerYear - $c->tl_event_padding_x;
		}
		return $this->width;
	}
	
	/** representations **/
	function toTimelineRepresentation($line) {
		$c = Config::getInstance();
	
		$length = $this->getPixelWidth();
		$line = $line * $c->tl_event_padding_y;
		$colorclass = $this->colorclass != "" ? " custom colorclass_".$this->colorclass : "";
		$html .= <<<EOD
\t\t\t\t<div class="event-preview" style="zIndex: 0">
\t\t\t\t\t<span 
\t\t\t\t\t	class="event{$colorclass}" 
\t\t\t\t\t	style="width:{$length}px;top:{$line}px;z-index:2;" 
\t\t\t\t\t	data-event="{$this->event_id}" 
\t\t\t\t\t	data-title="{$this->title}" 
\t\t\t\t\t	data-width="{$length}"
\t\t\t\t\t>{$this->title}
\t\t\t\t\t\t<span class="pin"></span>
\t\t\t\t\t</span>
\t\t\t\t\t<div class="event-details" style="zIndex: 1">{$this->details}</div>
\t\t\t\t</div>
EOD;
		return $html;
	}

	/** FACTORY **/
	static function getEventFromId($id) {
		$sql = <<<EOD
SELECT 
  e.event_id, e.title, e.details, e.colorclass, DATE(e.startdate) AS startdate, DATE(e.enddate) AS enddate 
FROM `events` AS e 
WHERE `event_id` = '$id' 
LIMIT 1;
EOD;
		$r = DB::queryAssocAtom($sql);
		return new Event($r['event_id'], $r['title'], $r['details'], 
				$r['startdate'], $r['enddate'], $r['colorclass']);
	}

}

class Events {
	static function getEvents($start = 0, $end = 0) {
		$sql = <<<EOD
SELECT 
  e.event_id, e.title, e.details, e.colorclass, DATE(e.startdate) AS startdate, DATE(e.enddate) AS enddate 
FROM `events` AS e
EOD;
//		if ($start != 0 && $end != 0)
//			$sql .= " WHERE e.startdate >= DATE($start) AND e.enddate <= DATE($end)";
//		else if ($start != 0)
//			$sql .= " WHERE e.startdate >= DATE($start)";
//		else if ($end != 0)
//			$sql .= " WHERE e.enddate <= DATE($end)";// have to figure out, how this works ...
		$sql .= " ORDER BY e.startdate ASC";
		$events = Array();
		foreach (DB::queryAssoc($sql) as $r)
			$events[] = new Event($r['event_id'], $r['title'], $r['details'], 
					$r['startdate'], $r['enddate'], $r['colorclass']);
		return $events;
	}
}

?>
