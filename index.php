<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="bower_components/bootstrap/assets/ico/favicon.ico">

    <title>Player Load Diagnostics | Rightster</title>

    <!-- Bootstrap core CSS -->
    <link href="bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap theme -->
    <link href="bower_components/bootstrap/dist/css/bootstrap-theme.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/style.css" rel="stylesheet">

    <!-- Dropzone -->
    <link href="bower_components/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet">
    <link href="bower_components/jquery-file-upload/css/jquery.fileupload-ui.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="bower_components/bootstrap/assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body role="document">

    <div class="container theme-showcase" role="main">

      <div class="jumbotron">


        <h1>Rightster Player Load Diagnostics</h1>

        <p>Drag and drop a valid HAR file onto this page, or click the button below to select a file to upload.</p>

        <div class="alert alert-danger hide" id="error-message">
          
        </div>

        <form id="upload-form" action="process.php" method="post" enctype="multipart/form-data">
          <span class="btn btn-success fileinput-button">
                <i class="glyphicon glyphicon-plus"></i>
                <span>Select files...</span>
                <!-- The file input field used as target for the file upload widget -->
                <input id="fileupload" type="file" name="har_file">
            </span> 
        </form>

        <div id="progress" class="progress">
          <div class="progress-bar progress-bar-success" style="width: 0%;"></div>
        </div>

      </div>

      <?php

      if(isset($_GET["file"]) && $_GET["file"] != "") {

        require_once(dirname(__FILE__) . "/process.php");

      } elseif(isset($_GET["action"]) && $_GET["action"] == "list") {

        require_once(dirname(__FILE__) . "/list.php");

      } else {

        require_once(dirname(__FILE__) . "/instructions.php");


      }

      ?>

    </div>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
	<!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
	<script src="bower_components/jquery-file-upload/js/vendor/jquery.ui.widget.js"></script>
	<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
	<script src="bower_components/jquery-file-upload/js/jquery.iframe-transport.js"></script>
	<!-- The basic File Upload plugin -->
	<script src="bower_components/jquery-file-upload/js/jquery.fileupload.js"></script>

<script>

function getPathFromUrl(url) {
	console.log(url);
  return url.split("?")[0];
}

/*jslint unparam: true */
/*global window, $ */
$(function () {

    'use strict';
    // Change this to the location of your server-side upload handler:
    var url = 'upload.php';
    $('#fileupload').fileupload({
        url: url,
        dataType: 'json',
        done: function (e, data) {
        	if(!data.result.status) {
        		$("#error-message").html(data.result.error).removeClass("hide").hide().fadeIn('slow');
        	} else {
        		window.location.href = getPathFromUrl(document.URL) + "?file=" + data.result.filename;
        	}
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .progress-bar').css(
                'width',
                progress + '%'
            );
        }
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');
});
</script>

  </body>
</html>
