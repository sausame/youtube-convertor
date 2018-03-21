/**
 * Information
 */

function updateAudioQualities() {

  var content = '<div>'
     + '<table class="tb">'
     + '<thead>'
     + '<tr>'
     + '<th width="20%">QUALITY</td>'
     + '<th>BITRATES</td>'
     + '</tr>'
     + '</thead>';
     + '<tbody>';

  var qualities = [ 48, 56, 64, 128, 256, 320 ];

  for (var i = 0; i < qualities.length; i ++) {

    var id = 'audio-quality-' + i;

    content += '<tr>';
    content += '<th><input id="' + id + '" type="radio" name="audio-quality" value="' + qualities[i] + '"/></th>';
    content += '<td><label for="' + id + '">' + qualities[i]+'kb</label></td>\n';
    content += '</tr>';
  }

  content += '</tbody></table></div>';

  document.getElementById('audio-quality').innerHTML = content;
}

function getFormat(name, index, format) {

  var id = name + '-' + index;

  var fields = [ 'format', 'codec', 'ext', 'filesize' ];

  var content = '<tr>';

  content += '<th><input id="' + id + '" type="radio" name="' + name + '" value="' + format['format_id'] + '"/></th>';

  for (var i = 0; i < fields.length; i ++) {
      content += '<td><label for="' + id + '">' + format[fields[i]]+'</label></td>\n';
  }

  content += '</tr>';

  return content;
}

function updateGroup(name, group) {

  var content = '<table class="tb">'
     + '<thead>'
     + '<tr>'
     + '<th width="20%">' + name.toUpperCase() + '</td>'
     + '<th>FORMAT</td>'
     + '<th>CODEC</td>'
     + '<th>EXT</td>'
     + '<th>SIZE</td>'
     + '</tr>'
     + '</thead>';
     + '<tbody>';

  for (var index = 0; index < group.length; index ++) {
      content += getFormat(name, index, group[index]);
  }

  content += '</tbody></table>';

  document.getElementById(name).innerHTML = content;
}

function onInforSucceed(content) {

  document.getElementById('notification').innerHTML = '';

  var infor = JSON.parse(content);

  currentUrl = infor['webpage_url'];

  // Information
  var content = '<h3><a id="link" href="' + currentUrl + '">' + infor['fulltitle'] + '</a></h3>'
    + '<p><div id="thumbnail"><img src="' + infor['thumbnail'] + '" /></div></p>'
    + '<h3>Duration: ' + infor['duration'] + ' seconds</h3>'
    + '</div>';

  document.getElementById('information').innerHTML = content;

  // Update groups
  var types = ['normal', 'video', 'audio'];

  for (var i = 0; i < types.length; i ++) {
    updateGroup(types[i], infor[types[i]]);
  }

  updateAudioQualities();

  document.getElementById('selection').style.display = 'block';

  showTab('normal-tab');
}

function onInforError(url) {

  var content = '<h3>Retrieving information is failed for ' + url + '.</h3>';

  document.getElementById('notification').innerHTML = content;
}

function sendInforRequest(url) {

  clearInterval(timer);

  var xhr = new XMLHttpRequest();

  xhr.onload = function() {

    console.log(xhr.status);

    var content = '';

    if (200 === xhr.status) {
      onInforSucceed(xhr.responseText);
    } else {
      onInforError(url);
    }

  };

  xhr.open('POST', 'infor.php', true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.send('url=' + url);
}

function showTab(tabId) {

  var i, tabcontent, tablinks;

   // Get all elements with class="tabcontent" and hide them
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
      tabcontent[i].style.display = "none";
  }

  // Get all elements with class="tablinks" and remove the class "active"
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  // Show the current tab
  var map = {
    'normal-tab': ['normal'],
    'video-tab': ['video', 'audio', 'audio-quality'],
    'audio-tab': ['audio', 'audio-quality']
  };

  var ids = map[tabId];

  for (i = 0; i < ids.length; i ++) {
    document.getElementById(ids[i]).style.display = "block";
  }

  // Add an "active" class to the button that opened the tab
  document.getElementById(tabId).className += " active";

  // Show the current tab
  var types = {
    'normal-tab': 'normal',
    'video-tab': 'video+audio',
    'audio-tab': 'audio'
  };

  var type = types[tabId];

  var content = '<p><input type="submit" value="Convert ' + type.toUpperCase() + '" onclick="convert(currentUrl, \'' + type + '\'); return false;" /></p>';

  document.getElementById('convertor-button').innerHTML = content;
}

function onClickTab(anEvent) {
  showTab(anEvent.currentTarget.id);
}

/**
 * Convertion
 */

function getExtension(url) {

  var exts = ['mp4', 'webm', 'ogg', 'mp3', 'wav'];
  var types = ['mp4', 'webm', 'ogg', 'mpeg', 'wav'];

  for (var i = 0; i < exts.length; i ++) {
    if (url.endsWith(exts[i])) {
      return types[i];
    }
  }

  return null;
}

function onConvertionSucceed(url, content) {

  var obj = JSON.parse(content);
  var error = obj['error'];
  var data = obj['data'];

  if (0 != error['code']) {
    onConvertionError(url, data['log']);
    return;
  }

  var mediaUrl = 'files/' + data['url'];
  var mediatype = getExtension(mediaUrl);

  if (null != mediatype) {

    var type = data['type'];
    var content = '<' + type + ' controls>'
      + '<source src="' + mediaUrl + '" type="' + type + '/' + mediatype + '">'
      + 'Your browser does not support the ' + type + ' tag.'
      + '</' + type + '>';

    document.getElementById('thumbnail').innerHTML = content;
  } else {
    document.getElementById('link').href = mediaUrl;
  }

  var content = '<h3>' + url + ' is converted.</h3><pre>' + data['log'] + '</pre>';
  document.getElementById('notification').innerHTML = content;
  document.getElementById('selection').style.display = 'none';
}

function onConvertionError(url, log='') {
  var content = '<h3>Convertion is failed for ' + url + '.</h3><pre>' + log + '</pre>';

  document.getElementById('notification').innerHTML = content;
  document.getElementById('selection').style.display = 'block';
}

function sendRequest(url, type, param) {

  clearInterval(timer);

  var xhr = new XMLHttpRequest();

  xhr.onload = function() {

    console.log(xhr.status);
    console.log(xhr.responseText);

    var content = '';

    if (200 === xhr.status) {
      onConvertionSucceed(url, xhr.responseText);
    } else {
      onConvertionError(url);
    }
  };

  document.getElementById('notification').innerHTML = '<h3>Converting ' + url + ' ...</h3>';
  document.getElementById('selection').style.display = 'none';

  var payload = 'url=' + encodeURIComponent(url) + '&type=' + encodeURIComponent(type);

  for (var key in param) {
    payload += '&' + key + '=' + param[key];
  }

  console.log(param);

  xhr.open('POST', 'convertor.php', true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.send(payload);
}

function convert(url, type) {

  var map = {
    'normal': ['normal'],
    'video+audio': ['video', 'audio', 'audio-quality'],
    'audio': ['audio', 'audio-quality']
  };

  var count = 0;
  var param = {};
  var ids = map[type];

  for (i = 0; i < ids.length; i ++) {

    var element = document.getElementById(ids[i]);
    var radios = element.getElementsByTagName('input');

    for (var j = 0; j < radios.length; j ++) {

      var radio = radios[j];

      if (radio.type === 'radio' && radio.checked) {

        param[ids[i]] = radio.value;
        count ++;
      }
    }
  }

  if (count == ids.length) {
    timer = setInterval(function () { sendRequest(url, type, param); }, 1000);
  } else {
    console.log('No input');
  }
}

