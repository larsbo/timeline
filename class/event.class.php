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
	private $type;
	private $image;
	
	private $width = -1;	//cache for event representations width

	function __Construct($event_id, $title, $details, $startdate, $enddate, $colorclass, $type, $image) {
		$this->event_id = $event_id;
		$this->title = $title;
		$this->details = $details;
		$this->startdate = $startdate;
		$this->enddate = $enddate;
		$this->colorclass = $colorclass;
		$this->type = $type;
		$this->image = $image;
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
	function getType() {
		return $this->type;
	}
	function getImage() {
		if (!empty($this->image) && file_exists("data/".$this->image))
			return "data/".$this->image;
		else
			return false;
	}
	
	function getStartYear() {
		return intval(substr($this->startdate, 0, 4));
	}
	function getEndYear() {
		$startyear = $this->getStartYear();
		$endyear = intval(substr($this->enddate, 0, 4));
		if ($endyear < $startyear) //this can happen, if the enddate is not properly entered 0000-... or if lies before its startingdate
			return $startyear;
		else
			return $endyear;
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
		$html = <<<EOD
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
EOD;
		switch ($this->getType()) {
			case 'text':
			$html .= $this->getTextRepresentation();
			break;
			
			case 'quote':
			$html .= $this->getQuoteRepresentation();
			break;
			
			case 'image':
			$html .= $this->getImageRepresentation();
			break;
			
			default:
			$html .= $this->getTextRepresentation();
			break;
		}
		return $html;
	}

	function getTextRepresentation() {
		$image = $this->getImage() ? "<span class=\"img\">".$this->getImage()."</span>" : "";
		return <<<EOD
\t\t\t\t\t<div class="event-details" style="zIndex: 1">{$image}{$this->details}</div>
\t\t\t\t</div>
EOD;
	}

	function getQuoteRepresentation() {
		return <<<EOD
\t\t\t\t\t<div class="event-details" style="zIndex: 1">
\t\t\t\t\t\t<blockquote>{$this->details}</blockquote>
\t\t\t\t\t</div>
\t\t\t\t</div>
EOD;
	}

	function getImageRepresentation() {
		$image = $this->getImage() ? "<img src=\"".$this->getImage()."\" alt=\"".$this->details."\" />" : "";
		return <<<EOD
\t\t\t\t\t<div class="event-details" style="zIndex: 1">
\t\t\t\t\t\t<div class="big-img">{$image}</div>
\t\t\t\t\t\t<div class="img-text">{$this->details}</div>
\t\t\t\t\t</div>
\t\t\t\t</div>
EOD;
	}


	/** FACTORY **/
	static function getEventFromId($id) {
		$sql = <<<EOD
SELECT 
  e.event_id, e.title, e.details, e.colorclass, DATE(e.startdate) AS startdate, DATE(e.enddate) AS enddate, e.type, e.image 
FROM `events` AS e 
WHERE `event_id` = '$id' 
LIMIT 1;
EOD;
		$r = DB::queryAssocAtom($sql);
		return new Event($r['event_id'], $r['title'], $r['details'], 
				$r['startdate'], $r['enddate'], $r['colorclass'], $r['type'], $r['image']);
	}

}

class Events {
	static function getEvents($start = 0, $end = 0) {
		$sql = <<<EOD
SELECT 
  e.event_id, e.title, e.details, e.colorclass, DATE(e.startdate) AS startdate, DATE(e.enddate) AS enddate, e.type, e.image 
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
					$r['startdate'], $r['enddate'], $r['colorclass'], $r['type'], $r['image']);
		return $events;
	}
}

?>
