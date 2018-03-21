<?php

$url = NULL;

if (! empty($_POST)) {
	$url = $_POST['url'];
} elseif (! empty($_GET)) {
	$url = $_GET['url'];
}

if (! empty($url)) {
	$url = stripslashes($url);
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
  <div id="selection" style="display: none">
    <div id="tab-div" class="tab">
      <button id='normal-tab' class="tablinks" onclick="onClickTab(event)">Normal</button>
      <button id='video-tab' class="tablinks" onclick="onClickTab(event)">Video</button>
      <button id='audio-tab' class="tablinks" onclick="onClickTab(event)">Audio</button>
   </div>
   <div id="normal" class="tabcontent"></div>
   <div id="video" class="tabcontent"></div>
   <div id="video-framerate" class="tabcontent"></div>
   <div id="audio" class="tabcontent"></div>
   <div id="audio-quality" class="tabcontent"></div>
   <div id="convertor-button"></div>
  </div>
<?php
if (empty($url)) {
?>
  <div id="convertor" style="display: block">
    <h2>Convertor</h2>
	<form name="convertorform" method="post" action="" >
      <p><input type="text" name="url" placeholder="https://www.youtube.com/watch?v=" required /></p>
      <p><input type="submit" value="convert" /></p>
    </form>
    <h2><a href="media.html">View converted media clips</a></h2>
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
      sendInforRequest(currentUrl);
    }

    var currentUrl = <?php echo("'$url'"); ?>;
    var timer = setInterval(getData, 1000);
  </script>
<?php
}
?>

</body>
</html>

