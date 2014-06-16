<?php

const UPLOADS_BASE = "uploads";

if(!is_dir(UPLOADS_BASE)) {
	mkdir(UPLOADS_BASE);
	chmod(UPLOADS_BASE, 0777);
}

$file = $_FILES["har_file"];
$safeFilename = preg_replace("/[^a-z0-9\.]/", "", strtolower($file["name"])) . ".har";
$currentDate = new DateTime;
$newFilename = $currentDate->format("Ymd-Hi") . $safeFilename;
echo $newFilename;
//move_uploaded_file($file, UPLOADS_BASE);