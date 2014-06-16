<?php

require_once(dirname(__FILE__) . "/config.php");
require_once(dirname(__FILE__) . "/utilities.php");

 if(!is_dir(UPLOADS_BASE)) {
	mkdir(UPLOADS_BASE);
	chmod(UPLOADS_BASE, 0777);
}

$file = $_FILES["har_file"];
// success/error status
$status = false;

// check if the uploaded file is valid JSON
if(!isFileJson($file["tmp_name"])) {
	$errorMsg = "Uploaded file is not a valid JSON file";
} else {
	$status = true;
}

// setup response
$response = array(
	"status" => $status
);

if(!$status) {
	// return an error for invalid JSON
	$response["error"] = $errorMsg;
	$response["test"] = "test";
} else {
	// rename file to include date and time and remove unusual characters
	$safeFilename = preg_replace("/[^a-z0-9\.]/", "", strtolower($file["name"])) . ".har";
	$currentDate = new DateTime;
	$newFilename = $currentDate->format("Ymd-His") . "-" . $safeFilename;
	move_uploaded_file($file["tmp_name"], UPLOADS_BASE . "/" . $newFilename);
	$response["filename"] = $newFilename;
}

echo json_encode($response);