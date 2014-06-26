<?php

require_once(dirname(__FILE__) . "/config.php");
require_once(dirname(__FILE__) . "/utilities.php");

 if(!is_dir(dirname(__FILE__) . "/" . UPLOADS_BASE)) {
	mkdir(dirname(__FILE__) . "/" . UPLOADS_BASE);
	chmod(dirname(__FILE__) . "/" . UPLOADS_BASE, 0744);
}

$file = $_FILES["har_file"];
// success/error status
$status = false;

// check if the uploaded file is valid JSON
if(!isFileJson($file["tmp_name"])) {
	$errorMsg = "The uploaded file was not a valid HAR file. Please capture another log file, following the instructions <a href=\"" . getBaseURL() . "\">here</a>, and upload it again.";
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
	$filename = explode(".", $file["name"]);
	// rename file to include date and time and remove unusual characters
	$safeFilename = preg_replace("/[^a-z0-9\.]/", "", strtolower($filename[0])) . ".har";
	$currentDate = new DateTime;
	$newFilename = $currentDate->format("Ymd-His") . "-" . $safeFilename;
	
	// move file into uploads folder
	move_uploaded_file($file["tmp_name"], dirname(__FILE__) . "/" . UPLOADS_BASE . "/" . $newFilename);

	$contents = "onInputData(" . file_get_contents(dirname(__FILE__) . "/" . UPLOADS_BASE . "/" . $newFilename) . ")";

	file_put_contents(dirname(__FILE__) . "/" . UPLOADS_BASE . "/" . $newFilename . "p", $contents);

	$response["filename"] = $newFilename;
}

echo json_encode($response);