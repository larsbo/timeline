<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Timeline</title>
  <link rel="stylesheet" href="css/style.css" type="text/css" media="screen" />
  <script type="text/javascript" src="js/iscroll.js"></script>
  <script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
  <script type="text/javascript" src="js/jquery.hovercard.js"></script>
  <script type="text/javascript" src="js/jquery.mini-map.js"></script>
  <script type="text/javascript" src="js/run.js"></script>
</head>
<body>
<?php
include 'inc/config.php';
include 'inc/application_top.php';
include 'timeline.class.php';

$timeline = new Timeline(1930, 1980);
?>
<div id="page">
  <div id="options-container">
    <form>
      <p>Ereignisse anzeigen:</p>
      <ul>
        <li class="selected">
          <a href="#" class="button" title="1">lang</a>
          <input name="event-style" value="long" type="radio" checked />
        </li>
        <li>
          <a href="#" class="button" title="2">kurz</a>
          <input name="event-style" value="short" type="radio" />
        </li>
        <li>
          <a href="#" class="button" title="3">ausblenden</a>
          <input name="event-style" value="hidden" type="radio" />
        </li>
      </ul>
    </form>
  </div>
  <div id="map-container">
    <div id="mini-map">
      <div id="current-view">
        <div class="line"></div>
      </div>
    </div>
  </div>
  <div id="wrapper">
    <div id="scroller">
<?php $timeline->output('events'); ?>
    </div>
  </div>
<?php $timeline->output('details'); ?>
</div>
</body>
</html>