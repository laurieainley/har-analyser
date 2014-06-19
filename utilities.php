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