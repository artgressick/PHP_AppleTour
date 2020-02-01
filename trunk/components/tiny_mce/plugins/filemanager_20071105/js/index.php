<?php
	// Use installer
	if (file_exists("../install"))
		die("alert('You need to run the installer or rename/remove the \"install\" directory.');");

	error_reporting(E_ALL ^ E_NOTICE);

	require_once("../includes/general.php");
	require_once('../classes/Utils/JSCompressor.php');

	$compress = true;

	// Some PHP installations seems to
	// output the dir rather than the current file here
	// it gets to /js/ instead of /js/index.php
	$baseURL = $_SERVER["PHP_SELF"];

	// Is file, get dir
	if (getFileExt($_SERVER["PHP_SELF"]) == "php")
		$baseURL = dirname($_SERVER["PHP_SELF"]);

	// Remove trailing slash if it has any
	if ($baseURL && $baseURL[strlen($baseURL) - 1] == '/')
		$baseURL = substr($baseURL, 0, strlen($baseURL)-1);

	if ($compress) {
		$compressor =& new Moxiecode_JSCompressor(array(
			'expires_offset' => 3600 * 24 * 10,
			'disk_cache' => true,
			'cache_dir' => '_cache',
			'gzip_compress' => false,
			'remove_whitespace' => true,
			'charset' => 'UTF-8'
		));

		// Compress these
		$compressor->addFile('mox.js');
		$compressor->addFile('gz_loader.js');
		$compressor->addContent("mox.baseURL = '" .$baseURL . "';");
		$compressor->addContent("mox.defaultDoc = 'index.php';");
		$compressor->compress();

		die;
	} else {
		header("Content-type: text/javascript");
		require_once('mox.js');
		echo "\nmox.baseURL = '" .$baseURL . "';\n";
		echo "\nmox.defaultDoc = 'index.php';\n";
	}
?>