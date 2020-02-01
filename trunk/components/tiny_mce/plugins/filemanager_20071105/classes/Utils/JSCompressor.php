<?php
/**
 * $Id: JSCompressor.php 10 2007-05-27 10:55:12Z spocke $
 *
 * @package ManagerEngine
 * @author Moxiecode
 * @copyright Copyright © 2007, Moxiecode Systems AB, All rights reserved.
 */

class Moxiecode_JSCompressor {
	var $_items;
	var $_settings;
	var $_lastUpdate;

	function Moxiecode_JSCompressor($settings = array()) {
		$this->_items = array();

		$default = array(
			'expires_offset' => 3600 * 24 * 10,
			'disk_cache' => true,
			'cache_dir' => '_cache',
			'gzip_compress' => true,
			'remove_whitespace' => true,
			'charset' => 'UTF-8'
		);

		$this->_settings = array_merge($default, $settings);
		$this->_lastUpdate = 0;
	}

	function addContent($content) {
		$this->_items[] = array('content', $content);
	}

	function addFile($path) {
		$this->_items[] = array('file', $path);

		$mtime = @filemtime($path);

		if ($mtime > $this->_lastUpdate)
			$this->_lastUpdate = $mtime;
	}

	function compress() {
		$key = "";

		foreach ($this->_items as $item)
			$key .= $item[1];

		// Setup some variables
		$cacheFile = $this->_settings['cache_dir'] . "/" . md5($key);
		$supportsGzip = false;
		$content = "";
		$encodings = array();

		// Check if it supports gzip
		if (isset($_SERVER['HTTP_ACCEPT_ENCODING']))
			$encodings = explode(',', strtolower(preg_replace("/\s+/", "", $_SERVER['HTTP_ACCEPT_ENCODING'])));

		if ($this->_settings['gzip_compress'] && (in_array('gzip', $encodings) || in_array('x-gzip', $encodings) || isset($_SERVER['---------------'])) && function_exists('gzencode') && !ini_get('zlib.output_compression')) {
			$enc = in_array('x-gzip', $encodings) ? "x-gzip" : "gzip";
			$supportsGzip = true;
			$cacheFile .= ".gz";
		} else
			$cacheFile .= ".js";

		// Set headers
		header("Content-type: text/javascript;charset=" . $this->_settings['charset']);
		header("Vary: Accept-Encoding");  // Handle proxies
		header("Expires: " . gmdate("D, d M Y H:i:s", time() + $this->_settings['expires_offset']) . " GMT");

		// Use cached file
		if ($this->_settings['disk_cache'] && file_exists($cacheFile) && @filemtime($cacheFile) == $this->_lastUpdate) {
			if ($supportsGzip)
				header("Content-Encoding: " . $enc);

			echo $this->_getFileContents($cacheFile);
			return;
		}

		// Load content
		foreach ($this->_items as $item) {
			if ($item[0] == 'file')
				$content .= $this->_getFileContents($item[1]);
			else
				$content .= $item[1];
		}

		// Remove comments and whitespace
		if ($this->_settings['remove_whitespace']) {
			$this->_strings = array();
			$this->_count = 0;

			// Replace strings and regexps
			$content = preg_replace_callback('/(\'[^\'\\n\\r]*\')|("[^"\\n\\r]*")|(\\s+(\\/[^\\/\\n\\r\\*][^\\/\\n\\r]*\\/g?i?))|([^\\w\\x24\\/\'"*)\\?:]\\/[^\\/\\n\\r\\*][^\\/\\n\\r]*\\/g?i?)/', array(&$this, '_strToItems'), $content);

			// Remove comments
			$content = preg_replace('/(\\/\\/[^\\n\\r]*[\\n\\r])|(\\/\\*[^*]*\\*+([^\\/][^*]*\\*+)*\\/)/', '', $content);

			// Remove whitespace
			$content = preg_replace('/\s*([=&|!+\\-\\/?:;,\\^\\(\\)\\{\\}<>%]+)\s*/', '$1', $content);
			$content = preg_replace('/[\r\n]+|(;)\s+/', '$1', $content);
			$content = preg_replace('/\s+/', ' ', $content);

			// Restore strings and regexps
			$content = preg_replace_callback('/¤@([^¤]+)¤/', array(&$this, '_itemsToStr'), $content);
		}

		// GZip content
		if ($supportsGzip) {
			header("Content-Encoding: " . $enc);
			$content = gzencode($content, 9, FORCE_GZIP);
		}

		// Write cache file
		if ($this->_settings['disk_cache']) {
			$this->_putFileContents($cacheFile, $content);

			if (@file_exists($cacheFile))
				@touch($cacheFile, $this->_lastUpdate);
		}

		// Output content to client
		echo $content;
	}

	// * * * Private methods

	function _putFileContents($path, $content) {
		if (function_exists("file_put_contents"))
			return @file_put_contents($path, $content);

		$fp = @fopen($path, "wb");
		if ($fp) {
			fwrite($fp, $content);
			fclose($fp);
		}
	}

	function _getFileContents($path) {
		$path = realpath($path);

		if (!$path || !@is_file($path))
			return "";

		if (function_exists("file_get_contents"))
			return @file_get_contents($path);

		$content = "";
		$fp = @fopen($path, "r");
		if (!$fp)
			return "";

		while (!feof($fp))
			$content .= fread($fp, 1024);

		fclose($fp);

		return $content;
	}

	function _strToItems($matches) {
		$this->_strings[] = $matches[0];

		return '¤@' . ($this->_count++) . '¤';
	}

	function _itemsToStr($matches) {
		return $this->_strings[intval($matches[1])];
	}
}
?>