<?php

require_once(dirname(__FILE__) . "/config.php");
require_once(dirname(__FILE__) . "/utilities.php");

$harFiles = array();

$files = scandir(dirname(__FILE__) . "/" . UPLOADS_BASE);
foreach($files as $file) {
	if($file != "." && $file != "..") {
		$ext = explode(".", $file);
		if($ext[count($ext) - 1] == "har") {
			$parts = explode("-", $file);
			$date = DateTime::createFromFormat("Ymd-His", $parts[0] . "-" . $parts[1]);	
			$har = new stdClass;
			$har->date = $date;
			$har->file = $file;
			$har->friendly = $date->format("H:i") . " - " . $parts[2];
			$harFiles[] = $har;
		}
	}
}

usort($harFiles, "dateTimeSort");
$lastDay = "";
?>
<div class="page-header">
	<h2>List</h2>
</div>
<?php
foreach($harFiles as $entry) {
	if(empty($lastDay) || ($entry->date->format("Ymd") != $lastDay->format("Ymd"))) {
		if($lastDay != "") {
			echo "</ul>";
		}
		?>
	<div class="page-header">
		<h3><?php echo $entry->date->format("j F, Y"); ?></h3>
	</div>
		<ul>
		<?php
	}
	echo "<li><a href=\"" . getBaseUrl() . "?file=" . $entry->file . "\">" . $entry->friendly . "</a>\n";
	$lastDay = $entry->date;
}
?>
</ul>