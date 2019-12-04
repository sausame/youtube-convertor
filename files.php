<?php

function scanFolder($folder, $isFile=true, $isDir=false) {

	$files = array();
	foreach (scandir($folder) as $file) {
		if ('.' == $file[0]) continue;
		$path = $folder . '/' . $file;

		if ($isFile && !is_file($path)) {
			continue;
		}

		if ($isDir && !is_dir($path)) {
			continue;
		}

		$files[$file] = filemtime($path);
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

function deleteDir($dirPath) {

	if (! is_dir($dirPath)) {
		throw new InvalidArgumentException("$dirPath must be a directory");
	}

	if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
		$dirPath .= '/';
	}

	$files = glob($dirPath . '*', GLOB_MARK);

	foreach ($files as $file) {
		if (is_dir($file)) {
			deleteDir($file);
		} else {
			unlinkPath($file);
		}
	}

	rmdir($dirPath);
}

?>
