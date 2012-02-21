<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Timeline</title>
  <link rel="stylesheet" type="text/css" href="css/style.css" />
  <link rel="stylesheet" type="text/css" href="css/fancybox.css" />
  <link rel="stylesheet" type="text/css" href="css/noty.css" />
  <link rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/themes/flick/jquery-ui.css" />	
<!--[if gte IE 9]>
  <style type="text/css">
    .event, .button {
       filter: none;
    }
  </style>
<![endif]-->
  <script type="text/javascript" src="js/iscroll.js"></script>
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
  <script type="text/javascript" src="js/jquery.easing.js"></script>
  <script type="text/javascript" src="js/jquery.fancybox-1.3.4.pack.js"></script>
  <script type="text/javascript" src="js/jquery.hovercard.js"></script>
  <script type="text/javascript" src="js/jquery.mini-map.js"></script>
  <script type="text/javascript" src="js/jquery.noty.js"></script>
  <script type="text/javascript" src="js/run.js"></script>
<?php
include 'class/timeline.class.php';
$c = Config::getInstance();
$timeline = new Timeline();
$width = max(1,$timeline->getEndYear() - $timeline->getStartYear() + 1) * $c->tl_column_width;
echo $timeline->getColorClasses()->toStyleDefinition();
?>
</head>
<body>
<div id="page">
  <div id="map-container">
    <div id="mini-map">
      <div id="current-view">
        <div class="line"></div>
      </div>
    </div>
  </div>
  <div id="wrapper">
<?php echo "\t<div id=\"scroller\" style=\"width: ".$width."px\">\n" ?>
<?php echo $timeline->getOrderedEventsOutput(); ?>
    </div>
  </div>
  <div id="options">
    <div id="colorclasses">
      <p>Ereignisse anzeigen:</p>
<?php echo $timeline->getColorClasses()->toLegendList(); ?>
    </div>
    <div id="options-container">
      <p>Optionen:</p>
      <ul>
        <li class="button selected" data-type="long" title="lange Ereignisse anzeigen">lang</li>
        <li class="button" data-type="short" title="kurze Ereignisse anzeigen">kurz</li>
        <li class="button" data-type="hidden" title="Ereignisse ausblenden">gefiltert</li>
      </ul>
    </div>
  </div>
</div>
<?php if ($_GET['debug']) Log::output(); // show debug messages only with ?debug=true ?>
</body>
</html>
