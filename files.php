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

function getLastModifiedPath($folder) {
	$filename = getLastModifiedFile($folder);
	if (NULL == $filename) {
		return NULL;
	}
	return $folder.'/'.$filename;
}

function renamePath($oldname, $newname) {

	syslog(LOG_WARNING, "$oldname --> $newname");
	rename($oldname, $newname);
}

function unlinkPath($filename, $iskept=false) {

	if ($iskept) {
		renamePath($filename, $filename.'.old');
	} else {
		syslog(LOG_WARNING, "Unlink $filename");
		unlink($filename);
	}
}

?>
