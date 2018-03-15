<?php

$url = NULL;

if (! empty($_POST)) {
	$url = $_POST['url'];
} elseif (! empty($_GET)) {
	$url = $_GET['url'];
}
?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; CHARSET=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=0.5, maximum-scale=2.0, user-scalable=yes" />
  <link rel="stylesheet" href="css/style.css" />
  <title>Youtube Convertor</title>
</head>
<body>
  <div id="notification">
<?php
if (! empty($url)) {
	echo("<h3>Retrieving information for '$url' ...</h3>");
}
?>
  </div>
  <div id="information"></div>
  <div id="selection"></div>
<?php
if (empty($url)) {
?>
  <div id="convertor" style="display: block">
    <h2>Convertor</h2>
	<form name="convertorform" method="post" action="" >
      <p><input type="text" name="url" placeholder="https://www.youtube.com/watch?v=" required /></p>
      <p><input type="submit" value="convert" /></p>
    </form>
  </div>
<?php
}
?>

<?php
if (! empty($url)) {
?>
  <script src="js/convertor.js"></script>
  <script>

    function getData() {
      sendInforRequest(<?php echo("'$url'"); ?>);
    }

    var timer = setInterval(getData, 1000);
  </script>
<?php
}
?>

</body>
</html>

