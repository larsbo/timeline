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
	private $colordescription;
	private $type;
	private $image;
	
	private $width = -1;	//cache for event representations width
	private $offset = -1;	//cache for event representations offset

	function __Construct($event_id, $title, $details, $startdate, $enddate, $colorclass, $colordescription, $type, $image) {
		$this->event_id = $event_id;
		$this->title = $title;
		$this->details = $details;
		$this->startdate = $startdate;
		$this->enddate = $enddate;
		$this->colorclass = $colorclass;
		$this->colordescription = $colordescription;
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
		if (!empty($this->image) && substr($this->image,0,4)=='http')
			return $this->image;
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
			$minwidth = $c->tl_column_width - $c->tl_event_padding_x; //minwidth is one year...

			if ($this->getStartYear() == $this->getEndYear()) {
				$this->width = $minwidth;	//we have minimal width
				return $minwidth;
			}
			
			/*$datetime1 = new DateTime($this->startdate);
			$datetime2 = new DateTime($this->enddate);
			$interval = $datetime1->diff($datetime2);
			$daysdiff = max(1,intval($interval->format('%a')));*/ //it seems i dont have php 5.3 :(
			
			$diff = abs(strtotime($this->enddate) - strtotime($this->startdate));
			$daysdiff = floor($diff/ (60*60*24));
			Log::debug("#days: ".$daysdiff." for event ".$this->title." [".$this->event_id."]");
			
			$pixelsPerYear = $c->tl_column_width;
			$pixelsPerDay = $pixelsPerYear / 365.0; //we have 365 days
			
			$pixelDays = round($pixelsPerDay * $daysdiff);
			$this->width = max($minwidth, $pixelDays);
		}
		return $this->width;
	}

	function getPixelOffset() {
		if ($this->offset == -1) {
			$c = Config::getInstance();
			if ($this->getStartYear() == $this->getEndYear() || substr($this->startdate, 5, 2) == '00')
				return 0;	//if we have minimal width or no proper date => offset = 0

			//how many months offset?
			$months = intval(substr($this->startdate, 5, 2));
			//how many days in this month?
			$days = intval(substr($this->startdate, 8, 2));
		
			$pixelsPerYear = $c->tl_column_width;
			$pixelsPerMonth = $pixelsPerYear / 12.0; //we have 12 months
			$pixels = round($months * $pixelsPerMonth + $days * $pixelsPerMonth / 30.0);
			$this->offset = $pixels;
		}
		return $this->offset;
	}
	
	static function getTypes() {
		return array('text', 'quote', 'image');
	}
	
	/** representations **/
	function toTimelineRepresentation($line) {
		$c = Config::getInstance();
	
		$length = $this->getPixelWidth();
		$line = $line * $c->tl_event_padding_y;
		$colorclass = $this->colorclass != "" ? " custom colorclass_".$this->colorclass : "";
		$offset = $this->getPixelOffset();
		$html = <<<EOD
\t\t\t\t<div class="event-preview" style="zIndex: 0">
\t\t\t\t\t<span 
\t\t\t\t\t	class="event{$colorclass}" 
\t\t\t\t\t	style="width:{$length}px;top:{$line}px;left:{$offset}px;z-index:2;" 
\t\t\t\t\t	data-event="{$this->event_id}" 
\t\t\t\t\t	data-title="{$this->title}" 
\t\t\t\t\t	data-offset="{$offset}" 
\t\t\t\t\t	data-width="{$length}"
\t\t\t\t\t>{$this->title}
\t\t\t\t\t\t<span class="pin"></span>
\t\t\t\t\t</span>
EOD;
		switch ($this->type) {
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
		$html .= "\t\t\t\t</div>";
		return $html;
	}

	private function getTextRepresentation() {
		$image = $this->getImage() ? "<span class=\"img\">".$this->getImage()."</span>" : "";
		return <<<EOD
\t\t\t\t\t<div class="event-details" style="zIndex: 1">{$image}{$this->details}</div>
EOD;
	}

	private function getQuoteRepresentation() {
		return <<<EOD
\t\t\t\t\t<div class="event-details" style="zIndex: 1">
\t\t\t\t\t\t<blockquote>{$this->details}</blockquote>
\t\t\t\t\t</div>
EOD;
	}

	private function getImageRepresentation() {
		$image = $this->getImage() ? "<img src=\"".$this->getImage()."\" alt=\"".$this->details."\" />" : "";
		return <<<EOD
\t\t\t\t\t<div class="event-details" style="zIndex: 1">
\t\t\t\t\t\t<div class="big-img">{$image}</div>
\t\t\t\t\t\t<div class="img-text">{$this->details}</div>
\t\t\t\t\t</div>
EOD;
	}

	function toAdminRepresentation() {
		return <<<EOD
<p><b>Titel:</b> {$this->title}</p>
<p><b>Start:</b> {$this->startdate}</p>
<p><b>Ende:</b> {$this->enddate}</p>
<p><b>Kategorie:</b> {$this->colordescription}</p>
<p><b>Typ:</b> {$this->type}</p>
<p><b>Bild:</b> {$this->image}</p>
<p>{$this->details}</p>
EOD;
	}


	static function getForm($e = null) {
		$colorclassesSelectField = ColorClasses::getColorClasses(false)->toSelectField($e===null?null:$e->colorclass);
		$typeSelectField = Util::ArrayToSelect(Event::getTypes(), 'type', null, $e===null?null:$e->type);
		$title = $e===null?"":$e->title;
		$startdate = $e===null?"":$e->startdate;
		$enddate = $e===null?"":$e->enddate;
		$image = $e===null?"":$e->image;
		$details = $e===null?"":$e->details;
		$action = $e===null?"save":"update";
		$eventIdInput = $e===null?"":"<input type=\"hidden\" name=\"id\" value=\"".$e->event_id."\" />";
		$html = <<<EOD
<form data-action="$action">$eventIdInput
<table>
	<tr>
		<td width="50"><label for="title">Titel:</label></td>
		<td><input type="text" name="title" id="title" maxlength="30" value="{$title}" /></td>
	</tr>
	<tr>
		<td><label for="start">Start:</label></td>
		<td>
			<input type="text" name="start" class="dateentry" id="start" maxlength="10" value="{$startdate}" />
			<label for="end">Ende:</label>
			<input type="text" name="end" class="dateentry" id="end" maxlength="10" value="{$enddate}" />
		</td>
	</tr>
	<tr>
		<td><label for="colorclass">Kategorie:</label></td>
		<td>{$colorclassesSelectField}</td>
	</tr>
	<tr>
		<td><label for="type">Typ:</label></td>
		<td>{$typeSelectField}</td>
	</tr>
	<tr>
		<td><label for="image">Bild:</label></td>
		<td><input type="text" name="image" id="image" maxlength="100" value="{$image}" /></td>
	</tr>
	<tr>
		<td colspan="2"><textarea name="details" rows="10" cols="50">{$details}</textarea></td>
	</tr>
	<tr>
		<td colspan="2"><input class="submit" type="submit" value="Speichern" /></td>
	</tr>
</table>
</form>
EOD;
		return $html;
	}

	/** FACTORY **/
	static function getEventFromId($id) {
		$sql = <<<EOD
SELECT 
  e.event_id, e.title, e.details, e.colorclass, DATE(e.startdate) AS startdate, DATE(e.enddate) AS enddate, e.type, e.image, c.description AS colordescription 
FROM `events` AS e 
LEFT JOIN `colorclasses` AS c ON c.color_id = e.colorclass
WHERE `event_id` = '$id' 
LIMIT 1;
EOD;
		$r = DB::queryAssocAtom($sql);
		return new Event($r['event_id'], $r['title'], $r['details'], 
				$r['startdate'], $r['enddate'], $r['colorclass'], $r['colordescription'], $r['type'], $r['image']);
	}

}

class Events {
	static function getEvents() {
		$sql = <<<EOD
SELECT 
  e.event_id, e.title, e.details, e.colorclass, DATE(e.startdate) AS startdate, DATE(e.enddate) AS enddate, e.type, e.image, c.description AS colordescription 
FROM `events` AS e
LEFT JOIN `colorclasses` AS c ON c.color_id = e.colorclass
EOD;
		$sql .= " ORDER BY e.startdate ASC";
		$events = Array();
		foreach (DB::queryAssoc($sql) as $r)
			$events[] = new Event($r['event_id'], $r['title'], $r['details'], 
					$r['startdate'], $r['enddate'], $r['colorclass'], $r['colordescription'], $r['type'], $r['image']);
		return $events;
	}
}

?>
