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
	
	static function getTypes() {
		return array('text', 'quote', 'image');
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
<p><b>Kategorie:</b> {$this->colorclass}</p>
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
