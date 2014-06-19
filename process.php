<?php

require_once(dirname(__FILE__) . "/config.php");
require_once(dirname(__FILE__) . "/utilities.php");
require_once(dirname(__FILE__) . "/classes/PlayerDiagnostics.php");

if(isset($_GET["file"]) && $_GET["file"] != "") {
	$diag = new PlayerDiagnostics;
	$diag->setFile($_GET["file"]);
	$diag->process();
?>

<div class="page-header">
	<h2>Results</h2>
</div>
	<?php
	/*
	print "Iframe Load: " . $diag->iframeTime/1000 . " secs (" . number_format($diag->iframeTime/$diag->totalTime*100, 2) . "% of total)<br />\n";
	print "Player Load: " . $diag->playerTime/1000 . " secs (" . number_format($diag->playerTime/$diag->totalTime*100, 2) . "% of total)<br />\n";
	print "Playlist Load: " . $diag->playlistTime/1000 . " secs (" . number_format($diag->playlistTime/$diag->totalTime*100, 2) . "% of total)<br />\n";
	print "Ad plugin Load: " . $diag->adPluginTime/1000 . " secs (" . number_format($diag->adPluginTime/$diag->totalTime*100, 2) . "% of total)<br />\n";
	print "Ad call Load: " . $diag->adCallTime/1000 . " secs (" . number_format($diag->adCallTime/$diag->totalTime*100, 2) . "% of total)<br />\n";
	print "Video Load: " . $diag->assetTime/1000 . " secs (" . number_format($diag->assetTime/$diag->totalTime*100, 2) . "% of total)<br />\n"; */

	foreach($diag->durationData as $slow) {
		if($slow["duration"] < THRESHOLD_SLOW) {
			break;
		}
		echo $slow["url"] . " - " . $slow["duration"] . "<br />\n";
	}

	print "View detailed request breakdown <a href=\"" . $_SERVER["HTTP_HOST"] . "http://www.softwareishard.com/har/viewer/?inputUrl=" . $_GET["file"] . "p\">here</a>.";

}

?>

<script type="text/javascript" src="https://www.google.com/jsapi?autoload={'modules':[{'name':'visualization',
       'version':'1','packages':['timeline', 'corechart']}]}"></script>
<script type="text/javascript">

google.setOnLoadCallback(drawChart);
function drawChart() {
  var container = document.getElementById('timeline');
  var chart = new google.visualization.Timeline(container);
  var timelineData = new google.visualization.DataTable();
  timelineData.addColumn({ type: 'string', id: 'Name' });
  timelineData.addColumn({ type: 'number', id: 'Start' });
  timelineData.addColumn({ type: 'number', id: 'End' });

  	<?php

  	$keyEvents = array(
	  	array(
	  		"field" => "iframeStart",
	  		"label" => "iframe Load",
	  	),
			array(
	  		"field" => "playerStart",
	  		"label" => "Player Load",
  		),
			array(
  			"field" => "playlistStart",
  			"label"	=> "Playlist Load",
  		),
			array(
  			"field" => "adPluginStart",
  			"label" => "Ad Plugin Load",
  		),
			array(
  			"field" => "adCallStart",
  			"label" => "Ad Call Load",
  		),
			array(
  			"field" => "assetStart",
  			"label" => "Video Load",
  		)
  	);

  	$timelineData = "";
  	$donutData = "";

  	$timelineData .= "timelineData.addRows([";
  	$donutData .= "google.visualization.arrayToDataTable([";
  	$donutData .= "['Component', 'Seconds'],";

  	$startTime = 0;
  	$endTime = 0;

  	for($i = 0; $i < count($keyEvents); $i++) {
  		$endTime 		+= $diag->events[$keyEvents[$i]["field"]]["interval"];

  		$timelineData .= "[ '" . $keyEvents[$i]["label"] . "',	$startTime, $endTime ],\n";
  		$donutData .= "[ '" . $keyEvents[$i]["label"] . "',	" . number_format($diag->events[$keyEvents[$i]["field"]]["interval"] / 1000, 2, '.', '') . " ],\n";

  		$startTime 	+= $diag->events[$keyEvents[$i]["field"]]["interval"];
  	}

  	$timelineData .= "]);";
		$donutData .= "]);";
	?>

  var options = {
  };

  <?php echo $timelineData; ?>

  chart.draw(timelineData, options);

	var options = {
    pieHole: 0.4,
  };

  var donutData = <?php echo $donutData; ?>

  var chart = new google.visualization.PieChart(document.getElementById('donutchart'));
  chart.draw(donutData, options);
}
</script>

<h3>Component loading time (in seconds)</h3>
<div id="timeline" style="width: 900px; height: 300px;"></div>

<h3>Breakdown by component (in seconds)</h3>
<div id="donutchart" style="width: 900px; height: 400px;"></div>

