<?php

class PlayerDiagnostics {

	public $file;
	public $absoluteFile;
	public $JSONdata;
	public $data;
	public $slimData;
	public $error;
	public $requestStart;
	public $events;
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

	private function convertFromISO($iso) {
		return DateTime::createFromFormat("Y-m-d\TH:i:s.u\Z", $iso);
	}

	private function extractKeyData($entries) {
		foreach($entries as $node) {
			$time = $this->convertFromISO($node->startedDateTime);
			$diff = dateTimeToMilliseconds($time) - dateTimeToMilliseconds($this->requestStart);
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

		return $nodes;

	}

	private function identifyKeyEvents($data) {
		$this->events = array();

		// loop once, extract all
		foreach($this->slimData as $item) {

			if(!isset($this->events["iframeStart"]) && preg_match("~.*//vds\.rightster\.com/v/.{14}\?target=iframe.*~", urldecode($item["url"]))) {
				$this->events["iframeStart"] = $item;
			}
			if(!isset($this->events["playerStart"]) && preg_match("~.*//player.rightster.com/.*/Player.swf.*~", urldecode($item["url"]))) {
				$this->events["playerStart"] = $item;
			}
			if(!isset($this->events["playlistStart"]) && preg_match("~.*//vds.*\.rightster\.com/v/.{14}\?.*fn=get_video_info.*~", urldecode($item["url"]))) {
				$this->events["playlistStart"] = $item;
			}
			if(!isset($this->events["adPluginStart"]) && preg_match("~http://vox-static\.liverail\.com/swf/.*/admanager.swf~", urldecode($item["url"]))) {
				$this->events["adPluginStart"] = $item;
			}
			if(!isset($this->events["adCallStart"]) && preg_match("~^.*//ad4\.liverail\.com/?$~", urldecode($item["url"])) && $item["method"] == "POST") {
				$this->events["adCallStart"] = $item;
			}
			if(!isset($this->events["assetStart"]) && preg_match("~.*//videos\.rightster\.com/.*/videos/.*~", urldecode($item["url"]))) {
				$this->events["assetStart"] = $item;
			}
			if(!isset($this->events["playStart"]) && preg_match("~.*//vds.*\.rightster\.com/v/.{14}\?.*fn=count_play.*~", urldecode($item["url"]))) {
				$this->events["playStart"] = $item;
			}			

		}
	}

	private function calculateKeyDurations() {
		$this->iframeTime = $this->events["playerStart"]["startOffset"] - $this->events["iframeStart"]["startOffset"];
		$this->playerTime = $this->events["playlistStart"]["startOffset"] - $this->events["playerStart"]["startOffset"];
		$this->playlistTime = $this->events["adPluginStart"]["startOffset"] - $this->events["playlistStart"]["startOffset"];
		$this->adPluginTime = $this->events["adCallStart"]["startOffset"] - $this->events["adPluginStart"]["startOffset"];
		$this->adCallTime = $this->events["assetStart"]["startOffset"] - $this->events["adCallStart"]["startOffset"];
		$this->assetTime = $this->events["playStart"]["startOffset"] - $this->events["assetStart"]["startOffset"];

		$this->totalTime = $this->events["playStart"]["startOffset"] - $this->events["iframeStart"]["startOffset"];
	}

	public function process() {

		if(!isJson($this->JSONdata)) {
			$this->error = "Invalid JSON file uploaded";
			return false;
		}

		$this->data = json_decode($this->JSONdata);

		// create date/time object including microseconds e.g. 2014-05-21T17:35:50.688Z
		$this->requestStart = $this->convertFromISO($this->data->log->pages[0]->startedDateTime);

		// simplify log file into array of only required data
		$this->slimData = $this->extractKeyData($this->data->log->entries);

		$this->identifyKeyEvents($this->slimData);

		$this->calculateKeyDurations($this->events);

	}

}

