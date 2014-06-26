<?php

require_once(dirname(__FILE__) . "/config.php");
require_once(dirname(__FILE__) . "/utilities.php");
require_once(dirname(__FILE__) . "/classes/PlayerDiagnostics.php");

if(isset($_GET["file"]) && $_GET["file"] != "") {
	$diag = new PlayerDiagnostics;
	$diag->setFile($_GET["file"]);
	$diag->process();

  	$keyEvents = array(
  		array(
  			"field" 		=> "pageStart",
  			"label" 		=> "Page Load",
  			"lowThreshold" 	=> 4000,
  			"highThreshold" => 8000,
  		),
	  	array(
	  		"field" 		=> "iframeStart",
	  		"label" 		=> "iframe Load",
  			"lowThreshold" 	=> 3000,
  			"highThreshold" => 6000,
	  	),
			array(
	  		"field"			=> "playerStart",
	  		"label" 		=> "Player Load",
  			"lowThreshold" 	=> 1500,
  			"highThreshold" => 3000,
  		),
			array(
  			"field" 		=> "playlistStart",
  			"label"			=> "Playlist Load",
  			"lowThreshold" 	=> 1000,
  			"highThreshold" => 2000,
  		),
			array(
  			"field" 		=> "adPluginStart",
  			"label" 		=> "Ad Plugin Load",
  			"lowThreshold" 	=> 1000,
  			"highThreshold" => 2000,
  		),
			array(
  			"field" 		=> "adCallStart",
  			"label" 		=> "Ad Call Load",
  			"lowThreshold" 	=> 5000,
  			"highThreshold" => 8000,
  		),
			//array(
  		//	"field" 		=> "adPlaybackStart",
  		//	"label" 		=> "Ad Playback",
  		//	"lowThreshold" 	=> 1000,
  		//	"highThreshold" => 2000,
  		//),
			array(
  			"field" 		=> "assetStart",
  			"label" 		=> "Video Initiation",
  			"lowThreshold" 	=> 3000,
  			"highThreshold" => 6000,
  		)
  	);

  	$numEvents = count($keyEvents);

?>

<div class="page-header">
	<h2>Results</h2>
</div>

<p>Link to this page: <a href="http:<?php echo getBaseURL() . "?file=" . $_GET["file"] . "\">http:" . getBaseURL() . "?file=" . $_GET["file"] . "</a></p>\n"; ?>

<h3>Diagnosis</h3>

<?php

$timelineData = "";
$donutData = "";

$timelineData .= "timelineData.addRows([";
$donutData .= "google.visualization.arrayToDataTable([";
$donutData .= "['Component', 'Seconds'],";

$diagnosisStr = "<table class=\"table-bordered table-condensed table-striped diagnosis-table\">\n";
$diagnosisStr .= "<tr>\n<th class=\"col-md-2\">Component</th>\n<th class=\"col-md-2\">Status</th></tr>\n";

$startTime = 0;
$endTime = 0;

$numFields = 0;

for($i = 0; $i < $numEvents; $i++) {

	if(isset($diag->events[$keyEvents[$i]["field"]])) {

		$numFields++;

		$endTime		+= $diag->events[$keyEvents[$i]["field"]]["interval"];

		$timelineData 	.= "[ '" . $keyEvents[$i]["label"] . "',	$startTime, $endTime ],\n";
		$donutData 		.= "[ '" . $keyEvents[$i]["label"] . "',	" . dp($diag->events[$keyEvents[$i]["field"]]["interval"] / 1000, 2, '.', '') . " ],\n";

		$startTime 		+= $diag->events[$keyEvents[$i]["field"]]["interval"];

		// diagnosis - threshold examination

		$diagnosisStr 	.= "<tr>\n<td>" . $keyEvents[$i]["label"] . "</td>\n";

		if($diag->events[$keyEvents[$i]["field"]]["interval"] > $keyEvents[$i]["highThreshold"]) {
			$diagnosisStr .= "<td><span class=\"label label-danger\">Critical</span></td>\n";
		} elseif($diag->events[$keyEvents[$i]["field"]]["interval"] > $keyEvents[$i]["lowThreshold"]) {
			$diagnosisStr .= "<td><span class=\"label label-warning\">Warning</span></td>\n";
		} else {
			$diagnosisStr .= "<td><span class=\"label label-success\">OK</span></td>\n";
		}
	}

	$diagnosisStr .= "</tr>\n";
}

$timelineData .= "]);";
$donutData .= "]);";

$diagnosisStr .= "</table>\n";

echo $diagnosisStr;

?>
<h3>Summary</h3>
<ul>
<?php 
if(isset($diag->pageUrl)) {
	echo "<li>URL: <a href=\"" . $diag->pageUrl . "\">" . $diag->pageUrl . "</a></li>\n";
}
if(isset($diag->events["pageStart"])) {
	if(isset($diag->events["adPlaybackStart"])) {
		echo "<li>Page load to ad playback time: " . dp(($diag->events["adPlaybackStart"]["startOffset"] - $diag->events["pageStart"]["startOffset"]) / 1000) . " secs</li>\n";
	} if(isset($diag->events["assetStart"])) {
		echo "<li>Page load to content playback time: " . dp(($diag->events["playStart"]["startOffset"] - $diag->events["pageStart"]["startOffset"]) / 1000) . " secs</li>\n";
	}
	?>
	<li>Total number of requests: <?php echo $diag->totalRequests; ?></li>
	<?php if(isset($diag->numAuctions) && $diag->numAuctions > 0) {
		echo "<li>Number of ad auctions run: " . $diag->numAuctions . "</li>\n";
	}

}
	echo "<li>View detailed request breakdown <a href=\"http://www.softwareishard.com/har/viewer/?inputUrl=" . getBaseURL() . UPLOADS_BASE . "/" . $_GET["file"] . "p\">here</a>.</li>\n";
?>
</ul>
	<?php
}

?>

<script type="text/javascript" src="https://www.google.com/jsapi?autoload={'modules':[{'name':'visualization',
       'version':'1','packages':['timeline', 'corechart']}]}"></script>
<script type="text/javascript">

google.setOnLoadCallback(drawChart);
function drawChart() {

	// timeline

  var container = document.getElementById('timeline');
  var chart = new google.visualization.Timeline(container);
  var timelineData = new google.visualization.DataTable();
  timelineData.addColumn({ type: 'string', id: 'Name' });
  timelineData.addColumn({ type: 'number', id: 'Start' });
  timelineData.addColumn({ type: 'number', id: 'End' });

  var options = {
  };

  <?php echo $timelineData; ?>

  chart.draw(timelineData, options);

	var options = {
    pieHole: 0.4,
  };

  // pie chart

  var donutData = <?php echo $donutData; ?>

  var chart = new google.visualization.PieChart(document.getElementById('donut-chart'));
  chart.draw(donutData, options);


<?php

	$numRequests = 0;

	$requestStr = "";
	foreach($diag->durationData as $slow) {
		if($slow["duration"] < THRESHOLD_SLOW || $numRequests >= MAX_REQUESTS) {
			break;
		}
		$numRequests++;
		$requestStr .= "['" . $slow["url"] . " (" . formatBytes($slow["filesize"]) . ")', " . dp($slow["timings"]->blocked / 1000) . ", " . dp($slow["timings"]->dns / 1000) . ", " . dp($slow["timings"]->connect / 1000) . ", " . dp($slow["timings"]->send / 1000) . ", " . dp($slow["timings"]->wait / 1000) . ", " . dp($slow["timings"]->receive / 1000) . ", ''],\n";
	}

	if(!empty($requestStr)) {

		?>

	  // stacked bar chart

		var requestData = google.visualization.arrayToDataTable([
	        ['URL', 'Blocked', 'DNS', 'Connection', 'Request', 'Waiting',
	         'Receiving', { role: 'annotation' } ],
		
		<?php echo $requestStr; ?>

    ]);

    var options = {
      width: "100%",
      height: "100%",
      legend: { position: 'top', maxLines: 3 },
      isStacked: true,
      hAxis: {
      	viewWindowMode: "maximized",
      },
    };

  var chart = new google.visualization.BarChart(document.getElementById('requests-chart'));
  chart.draw(requestData, options);

	<?php
	}
	?>

}
</script>

<h3>Component loading time (in seconds)</h3>
<p>Note: All video playback removed from calculations.</p>
<div id="timeline" style="width: 100%; height: <?php echo (($numFields * 45) + 50); ?>px;"></div>

<h3>Breakdown by component (in seconds)</h3>
<div id="donut-chart" style="width: 100%; height: 400px;"></div>

<?php
if($numRequests > 0) {
?>
<h3>Possible suspects (slowest requests)</h3>
<div id="requests-chart" style="width: 100%; height: <?php echo ($numRequests * 45) + 50; ?>px;"></div>
<?php } ?>