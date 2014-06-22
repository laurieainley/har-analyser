<?php

class PlayerDiagnostics {

	public $file;
	public $absoluteFile;
	public $JSONdata;
	public $data;
	public $slimData;
	public $durationData;
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
	public $iframeRequests;
	public $totalRequests;
	public $numAuctions;

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
				"startOffset"		 	=> $diff,
				"url" 						=> $node->request->url,
				"duration" 				=> $node->time,
				"requestSize" 		=> $node->request->bodySize,
				"method" 					=> $node->request->method,
				"responseSize" 		=> $node->response->bodySize,
				"timings" 				=> $node->timings,
				"filesize" 				=> $node->response->content->size,
			);
		}

		$this->totalRequests = count($entries);

		usort($nodes, "startSort");

		return $nodes;

	}

	private function identifyKeyEvents($data) {
		$this->events = array();
		$this->numAuctions = 0;

		// loop once, extract all
		foreach($this->slimData as $item) {

			if(!isset($this->events["pageStart"]) && isset($this->pageUrl) && $this->pageUrl == $item["url"]) {
				$this->events["pageStart"] = $item;
			}
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
			if(!isset($this->events["adPlaybackStart"]) && preg_match("~.*//t4\.liverail\.com/?.*metric=impression.*~", urldecode($item["url"]))) {
				$this->events["adPlaybackStart"] = $item;
			}
			if(!isset($this->events["assetStart"]) && preg_match("~.*//videos\.rightster\.com/.*/videos/.*~", urldecode($item["url"]))) {
				$this->events["assetStart"] = $item;
			}
			if(!isset($this->events["playStart"]) && preg_match("~.*//vds.*\.rightster\.com/v/.{14}\?.*fn=count_play.*~", urldecode($item["url"]))) {
				$this->events["playStart"] = $item;
			}			

			if(preg_match("~^.*//ad4\.liverail\.com/?$~", urldecode($item["url"])) && $item["method"] == "POST") {
				$this->numAuctions++;
			}

		}
	}

	private function calculateKeyDurations() {

		$intervals = array();

		// select only the events 

		foreach($this->events as $ek => $ev) {
			if(!empty($last)) {
				$intervals[] = array("field" => $last, "end" => $ek);
			}
			$last = $ek;
		}

		foreach($intervals as $interval) {
			if(isset($this->events[$interval["field"]])) {
				if($interval["field"] == "playlistStart") {
					$this->events[$interval["field"]]["interval"] = $this->events[$interval["field"]]["duration"];
				} else {
					$this->events[$interval["field"]]["interval"] = $this->events[$interval["end"]]["startOffset"] - $this->events[$interval["field"]]["startOffset"];
				}
			}
		}

		$this->totalTime = $this->events[$intervals[count($intervals)-1]["field"]]["startOffset"] - $this->events[$intervals[0]["field"]]["startOffset"];
	}

	private function nodesByDuration($durationData) {

		usort($durationData, "durationSort");

		return $durationData;

	}

	public function process() {

		if(!isJson($this->JSONdata)) {
			$this->error = "Invalid JSON file uploaded";
			return false;
		}

		$this->data = json_decode($this->JSONdata);

		// create date/time object including microseconds e.g. 2014-05-21T17:35:50.688Z
		$this->requestStart = $this->convertFromISO($this->data->log->pages[0]->startedDateTime);

		if(isset($this->data->log->pages[0])) {
			$this->pageUrl = $this->data->log->pages[0]->title;
		}

		// simplify log file into array of only required data
		$this->slimData = $this->extractKeyData($this->data->log->entries);

		$this->durationData = $this->nodesByDuration($this->slimData);

		$this->identifyKeyEvents($this->slimData);

		$this->calculateKeyDurations($this->events);

	}

}

