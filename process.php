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
	print "Iframe Load: " . $diag->iframeTime/1000 . " secs (" . number_format($diag->iframeTime/$diag->totalTime*100, 2) . "% of total)<br />\n";
	print "Player Load: " . $diag->playerTime/1000 . " secs (" . number_format($diag->playerTime/$diag->totalTime*100, 2) . "% of total)<br />\n";
	print "Playlist Load: " . $diag->playlistTime/1000 . " secs (" . number_format($diag->playlistTime/$diag->totalTime*100, 2) . "% of total)<br />\n";
	print "Ad plugin Load: " . $diag->adPluginTime/1000 . " secs (" . number_format($diag->adPluginTime/$diag->totalTime*100, 2) . "% of total)<br />\n";
	print "Ad call Load: " . $diag->adCallTime/1000 . " secs (" . number_format($diag->adCallTime/$diag->totalTime*100, 2) . "% of total)<br />\n";
	print "Video Load: " . $diag->assetTime/1000 . " secs (" . number_format($diag->assetTime/$diag->totalTime*100, 2) . "% of total)<br />\n";

	print "View detailed request breakdown <a href=\"" . $_SERVER["HTTP_HOST"] . "http://www.softwareishard.com/har/viewer/?inputUrl=" . $_GET["file"] . "p\">here</a>.";

}

?>

<script type="text/javascript" src="https://www.google.com/jsapi?autoload={'modules':[{'name':'visualization',
       'version':'1','packages':['timeline']}]}"></script>
<script type="text/javascript">

google.setOnLoadCallback(drawChart);
function drawChart() {
  var container = document.getElementById('timeline');
  var chart = new google.visualization.Timeline(container);
  var dataTable = new google.visualization.DataTable();
  dataTable.addColumn({ type: 'string', id: 'Name' });
  dataTable.addColumn({ type: 'number', id: 'Start' });
  dataTable.addColumn({ type: 'number', id: 'End' });
  dataTable.addRows([
    [ 'iframe Load',			0, 																	<?= $diag->iframeTime; ?> ],
    [ 'Player Load',    	<?= $diag->iframeTime; ?>,			<?= ($diag->iframeTime + $diag->playerTime); ?> ],
    [ 'Playlist Load',    <?= $diag->playerTime; ?>,			<?= ($diag->playerTime + $diag->playlistTime); ?> ],
    [ 'Ad Plugin Load',   <?= $diag->playlistTime; ?>,			<?= ($diag->playlistTime + $diag->adPluginTime); ?> ],
    [ 'Ad Call Load',    	<?= $diag->adPluginTime; ?>,			<?= ($diag->adPluginTime + $diag->adCallTime); ?> ],
    [ 'Video Load',    		<?= $diag->adCallTime; ?>,			<?= ($diag->adCallTime + $diag->assetTime); ?> ]
  ]);

  var options = {
    timeline: { showRowLabels: false }
  };

  chart.draw(dataTable, options);
}
</script>

<div id="timeline" style="width: 900px; height: 400px;"></div>