<!DOCTYPE html>
<?php 

session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$allowed_extensions = array('jpeg', 'jpg', 'png', 'gif', 'pdf');
$dest_directory = "attachments";
$title = "Upload service";

?>
<html>
	<head>
		<title><?= $title ?></title>
		<meta charset='UTF-8' />
		<meta http-equiv="Pragma" content="no-cache"/>
		<meta http-equiv="Expires" content="0"/>
		<style>
			div.message {
				text-align: center;

				padding: 0.5em;
				border-radius: 0.5em;
			}

			div.message p {
				margin: 0.5em;
			}

			div.success {
				background-color: #D6EFC2;
			}

			div.failure {
				background-color: #FBE3E4;
			}

			form {
				max-width: 30em;
				margin-left: auto;
				margin-right: auto;

				padding: 1em;
				border-radius: 0.5em;
				background-color: PapayaWhip;
			}
		</style>
	</head>
	<body>
		<h1><a href="<?= $_SERVER['SCRIPT_NAME'] ?>"><?= $title ?></a></h1>
<?php

function bytes_to_human($bytes) {
	$sz = 'BKMGTP';
	$factor = floor((strlen($bytes) - 1) / 3);
	return sprintf("%.0f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}

function human_to_bytes($sSize) {
    if (is_numeric($sSize)) { return $sSize; }

    $sSuffix = substr($sSize, -1);
    $bytes = substr($sSize, 0, -1);

    switch(strtoupper($sSuffix)) {
    case 'P': $bytes *= 1024;
    case 'T': $bytes *= 1024;
    case 'G': $bytes *= 1024;
    case 'M': $bytes *= 1024;
    case 'K': $bytes *= 1024;
        break;
    }

    return $bytes;
}

function maximum_server_file_upload_size() {
	$post_max_size = human_to_bytes(ini_get('post_max_size'));
	$upload_max_filesize = human_to_bytes(ini_get('upload_max_filesize'));
	return min($post_max_size, $upload_max_filesize);
}

function message($msg_class, $message) {
?>
	<div class='message <?= $msg_class ?>'>
		<p><?= $message ?></p>
	</div>
<?php
}

$file_info = $_FILES['file'];

$error = false;

if (!$error && !file_exists($dest_directory)) {
	$error = true;
	$error_message = "Directory <em>$dest_directory</em> does not exist on server.";

}

if (!$error && !is_writable($dest_directory)) {
	$error = true;
	$error_message = "Directory <em>$dest_directory</em> is not accessible";
}

if (!$error && $file_info['error'] != UPLOAD_ERR_OK) {
	$error = true;
	$error_message = "File upload failed";
}

if ($error) {
	echo message("failure", $error_message);
}

if (!$error && !empty($file_info)) {
	$file_name = $file_info['name'];
	$file_size = $file_info['size'];
	$file_temp = $file_info['tmp_name'];
	$file_type = $file_info['type'];

	// http://stackoverflow.com/questions/2021624/string-sanitizer-for-filename/2021729#2021729
	$file_name = preg_replace("([^0-9A-Za-z._-])", '_', $file_name);
	$file_name = preg_replace("([\.]{2,})", '', $file_name);

	$file_ext = strtolower(end(explode('.', $file_name)));

	if (in_array($file_ext, $allowed_extensions) === false) {
		$error = true;

		$error_message = "Extension <em>$file_ext</em> not allowed. ";
		$error_message .= "Allowed extensions are: ";
		$error_message .= implode(', ', array_map(function($e) { return "<em>$e</em>"; }, $allowed_extensions));
	}

	if (!$error) {
		$dest_file_name = "$dest_directory/$file_name";
		$error = !move_uploaded_file($file_temp, $dest_file_name);
	}

	if (!$error) {
		$msg_class = "success";
		$message = "File uploaded to ";
		$message .= "<a href='$dest_file_name'><code>$dest_file_name</code></a>";
	} else {
		$msg_class = "failure";
		$message = "An error has occurred while uploading <em>$file_name</em>:<br/>";
		$message .= $error_message;
	}

	echo message($msg_class, $message);
}
?>

<?php
	$uploaded_files = array_diff(scandir($dest_directory), array('..', '.'));
?>
		<div id='uploaded-files'>
			<p>Files uploaded in <a href='<?= $dest_directory ?>'><code><?= $dest_directory ?>/</code></a>
			(<?= count($uploaded_files) ?> in total)</p>

			<ul>
<?php
	foreach ($uploaded_files as $file_name) {
		$path = "$dest_directory/$file_name";
?>
				<li><a href='<?= $path ?>'><?= $file_name ?></a>
				<span>(<?= bytes_to_human(filesize($path)) ?> —
				<?= date("Y-m-d H:i:s", filemtime($path)) ?>)</span></li>
<?php
	}
?>
			</ul>
		</div>

		<form action="" method="POST" enctype="multipart/form-data">
			<p>Maximum upload size
			<?= bytes_to_human(maximum_server_file_upload_size()) ?></p>

			<fieldset>
				<legend>Upload another file</legend>
				<input type="file" name="file" />
				<input type="submit" />
			</fieldset>
		</form>
	</body>
	<!-- This is free software released into the public domain (CC0 license). -->
</html>