<?php

require_once(dirname(__FILE__) . "/config.php");
require_once(dirname(__FILE__) . "/utilities.php");

$diag = new PlayerDiagnostics;
$diag->setFile($_GET["file"]);
$diag->process();

class PlayerDiagnostics {

	public $file;
	public $absoluteFile;
	public $JSONdata;
	public $data;
	public $slimData;
	public $error;
	public $iframeTime;
	public $playerTime;
	public $playlistTime;
	public $adPluginTime;
	public $adCallTime;
	public $assetTime;
	public $totalTime;

	public function setFile($file) {
		$this->file = $file;
		$this->absoluteFile = UPLOADS_BASE . "/" . $this->file;
		$this->JSONdata = file_get_contents($this->absoluteFile);
	}

	public function process() {

		if(!isJson($this->JSONdata)) {
			$this->error = "Invalid JSON file uploaded";
			return false;
		}

		$this->data = json_decode($this->JSONdata);

		// create date/time object including microseconds e.g. 2014-05-21T17:35:50.688Z
		$requestStart = DateTime::createFromFormat("Y-m-d\TH:i:s.u\Z", $this->data->log->pages[0]->startedDateTime);

		foreach($this->data->log->entries as $node) {
			$time = DateTime::createFromFormat("Y-m-d\TH:i:s.u\Z", $node->startedDateTime);
			$diff = dateTimeToMilliseconds($time) - dateTimeToMilliseconds($requestStart);
			$nodes[] = array(
				"startOffset" => $diff,
				"url" => $node->request->url,
				"duration" => $node->time,
				"requestSize" => $node->request->bodySize,
				"method" => $node->request->method,
				"responseSize" => $node->response->bodySize
			);
		}

		usort($nodes, "startSort");

		$this->slimData = $nodes;

		$iframeStart = null;
		$playerStart = null;
		$playlistStart = null;
		$adPluginStart = null;
		$adCallStart = null;
		$assetStart = null;
		$playStart = null;

		// loop once, extract all
		foreach($this->slimData as $item) {

			if($iframeStart === null && preg_match("~.*//vds\.rightster\.com/v/.{14}\?target=iframe.*~", urldecode($item["url"]))) {
				$iframeStart = $item;
			}
			if($playerStart === null && preg_match("~.*//player.rightster.com/.*/Player.swf.*~", urldecode($item["url"]))) {
				$playerStart = $item;
			}
			if($playlistStart === null && preg_match("~.*//vds.*\.rightster\.com/v/.{14}\?.*fn=get_video_info.*~", urldecode($item["url"]))) {
				$playlistStart = $item;
			}
			if($adPluginStart === null && preg_match("~http://vox-static\.liverail\.com/swf/.*/admanager.swf~", urldecode($item["url"]))) {
				$adPluginStart = $item;
			}
			if($adCallStart === null && preg_match("~^.*//ad4\.liverail\.com/?$~", urldecode($item["url"])) && $item["method"] == "POST") {
				$adCallStart = $item;
			}
			if($assetStart === null && preg_match("~.*//videos\.rightster\.com/.*/videos/.*~", urldecode($item["url"]))) {
				$assetStart = $item;
			}
			if($playStart === null && preg_match("~.*//vds.*\.rightster\.com/v/.{14}\?.*fn=count_play.*~", urldecode($item["url"]))) {
				$playStart = $item;
			}			


		}

		/*print_r($iframeStart);
		print_r($playlistStart);
		print_r($adPluginStart);
		print_r($adCallStart);
		print_r($assetStart);
		print_r($playStart);
		
		print_r($this->slimData);*/

		$this->iframeTime = $playerStart["startOffset"] - $iframeStart["startOffset"];
		$this->playerTime = $playlistStart["startOffset"] - $playerStart["startOffset"];
		$this->playlistTime = $adPluginStart["startOffset"] - $playlistStart["startOffset"];
		$this->adPluginTime = $adCallStart["startOffset"] - $adPluginStart["startOffset"];
		$this->adCallTime = $assetStart["startOffset"] - $adCallStart["startOffset"];
		$this->assetTime = $playStart["startOffset"] - $assetStart["startOffset"];

		$this->totalTime = $playStart["startOffset"] - $iframeStart["startOffset"];

		print "Iframe Load: " . $this->iframeTime/1000 . " secs (" . number_format($this->iframeTime/$this->totalTime*100, 2) . "% of total)<br />\n";
		print "Player Load: " . $this->playerTime/1000 . " secs (" . number_format($this->playerTime/$this->totalTime*100, 2) . "% of total)<br />\n";
		print "Playlist Load: " . $this->playlistTime/1000 . " secs (" . number_format($this->playlistTime/$this->totalTime*100, 2) . "% of total)<br />\n";
		print "Ad plugin Load: " . $this->adPluginTime/1000 . " secs (" . number_format($this->adPluginTime/$this->totalTime*100, 2) . "% of total)<br />\n";
		print "Ad call Load: " . $this->adCallTime/1000 . " secs (" . number_format($this->adCallTime/$this->totalTime*100, 2) . "% of total)<br />\n";
		print "Video Load: " . $this->assetTime/1000 . " secs (" . number_format($this->assetTime/$this->totalTime*100, 2) . "% of total)<br />\n";

	}

}

