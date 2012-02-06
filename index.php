<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Timeline</title>
  <link rel="stylesheet" type="text/css" href="css/style.css" />
<!--[if gte IE 9]>
  <style type="text/css">
    .event, .button {
       filter: none;
    }
  </style>
<![endif]-->
  <script type="text/javascript" src="js/iscroll.js"></script>
  <script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
  <script type="text/javascript" src="js/jquery.hovercard.js"></script>
  <script type="text/javascript" src="js/jquery.message.js"></script>
  <script type="text/javascript" src="js/jquery.mini-map.js"></script>
  <script type="text/javascript" src="js/run.js"></script>
<?php
include 'class/timeline.class.php';
$c = Config::getInstance();
$timeline = new Timeline($c->startdate, $c->enddate);
$width = max(1,$c->enddate - $c->startdate + 1) * $c->tl_column_width;
$timeline->output('css');
?>
</head>
<body>
<div id="page">
  <div id="options-container">
    <p>Ereignisse anzeigen:</p>
    <a href="#" class="button selected" data-type="long" title="lange Ereignisse anzeigen">lang</a>
    <a href="#" class="button" data-type="short" title="kurze Ereignisse anzeigen">kurz</a>
    <a href="#" class="button" data-type="hidden" title="Ereignisse ausblenden">ausblenden</a>
  </div>
  <div id="map-container">
    <div id="mini-map">
      <div id="current-view">
        <div class="line"></div>
      </div>
    </div>
  </div>
  <div id="wrapper">
<?php echo "\t<div id=\"scroller\" style=\"width: ".$width."px\">\n" ?>
<?php $timeline->output('events'); ?>
    </div>
  </div>
  <div class="eventbox">
<?php $timeline->output('details'); ?>
  </div>
<?php Log::output(); ?>
</div>
</body>
</html>
