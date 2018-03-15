<?php

function scanFolder($folder) {

	$files = array();
	foreach (scandir($folder) as $file) {
		if ('.' == $file[0]) continue;
		$files[$file] = filemtime($folder . '/' . $file);
	}

	arsort($files);
	$files = array_keys($files);

	return ($files) ? $files : NULL;
}

function getLastModifiedFile($folder) {
	$files = scanFolder($folder);

	if (! $files or 0 == sizeof($files)) {
		return NULL;
	}

	return $files[0];
}

?>
