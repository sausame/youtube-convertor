<?php

require('files.php');

$config = parse_ini_file('config.ini');
$savePath = $config['save-path'];

$files = scanFolder($savePath, false, true);
$num = sizeof($files);

echo('{"num": '.$num.', "files": [');

for ($i = 0; $i < $num; $i ++) {

	if ($i > 0) {
		echo(', ');
	}

	$path = $savePath . '/' . $files[$i] . '/information.json';
	echo(file_get_contents($path));
}

echo(']}');

?>

