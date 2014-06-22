<?php

function isJson($string) {
 json_decode($string);
 return (json_last_error() == JSON_ERROR_NONE);
}

function isFileJson($file) {
	if(filesize($file) > 0 && isJson(file_get_contents($file))) {
		return true;
	}
}

function dateTimeToMilliseconds(\DateTime $dateTime)
{
    $secs = $dateTime->getTimestamp(); // Gets the seconds
    $millisecs = $secs*1000; // Converted to milliseconds
    $millisecs += $dateTime->format("u")/1000; // Microseconds converted to seconds
    return $millisecs;
}

function startSort($a, $b)
{
    if ($a["startOffset"] == $b["startOffset"]) {
        return 0;
    }
    return ($a["startOffset"] < $b["startOffset"]) ? -1 : 1;
}

function durationSort($a, $b)
{
    if ($a["duration"] == $b["duration"]) {
        return 0;
    }
    return ($a["duration"] > $b["duration"]) ? -1 : 1;
}

function dateTimeSort($a, $b)
{
    if ($a->date->format("U") == $b->date->format("U")) {
        return 0;
    }
    return ($a->date->format("U") > $b->date->format("U")) ? -1 : 1;
}

function getBaseURL() {
    $path = $_SERVER["REQUEST_URI"];
    if(substr($path, -1) != "/") {
        $parts = explode("/", $path);
        array_pop($parts);
        $newPath = implode("/", $parts);
        $newPath .= "/";
    }
    $link =  "//" . $_SERVER["HTTP_HOST"] . $newPath;
    return $link;
}

function dp($num, $dp = 2) {
    return number_format($num, $dp);
}

function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 

    // Uncomment one of the following alternatives
     $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow)); 

    return round($bytes, $precision) . ' ' . $units[$pow]; 
} 