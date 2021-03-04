/**
 * Information
 */

function updateVideoFramerates(maxFps) {

  var content = '<div>'
     + '<table class="tb">'
     + '<thead>'
     + '<tr>'
     + '<th width="20%">FRAMERATE</td>'
     + '<th>BITRATES</td>'
     + '</tr>'
     + '</thead>';
     + '<tbody>';

  var qualities = [ 5, 10, 15, 24, 25, 30, 48, 60, 90, 120 ];

  for (var i = 0; i < qualities.length && qualities[i] <= maxFps; i ++) {

    var id = 'video-framerate-' + i;

    content += '<tr>';
    content += '<th><input id="' + id + '" type="radio" name="video-framerate" value="' + qualities[i] + '"/></th>';
    content += '<td><label for="' + id + '">' + qualities[i] + ' fps </label></td>\n';
    content += '</tr>';
  }

  content += '</tbody></table></div>';

  document.getElementById('video-framerate').innerHTML = content;
}

function updateAudioQualities(maxAbr) {

  var content = '<div>'
     + '<table class="tb">'
     + '<thead>'
     + '<tr>'
     + '<th width="20%">QUALITY</td>'
     + '<th>BITRATES</td>'
     + '</tr>'
     + '</thead>';
     + '<tbody>';

  var qualities = [ 48, 56, 64, 96, 128, 160, 256, 320 ];

  for (var i = 0; i < qualities.length && qualities[i] <= maxAbr; i ++) {

    var id = 'audio-quality-' + i;

    content += '<tr>';
    content += '<th><input id="' + id + '" type="radio" name="audio-quality" value="' + qualities[i] + '"/></th>';
    content += '<td><label for="' + id + '">' + qualities[i] + ' kb</label></td>\n';
    content += '</tr>';
  }

  content += '</tbody></table></div>';

  document.getElementById('audio-quality').innerHTML = content;
}

function updateSpeed() {

  var content = '<div>'
     + '<table class="tb">'
     + '<thead>'
     + '<tr>'
     + '<th width="20%">Speed</td>'
     + '<th>VALUE</td>'
     + '</tr>'
     + '</thead>';
     + '<tbody>';

  var values = [ 0.5, 0.75, 1, 1.25, 1.5, 1.75, 2 ];

  for (var i = 0; i < values.length; i ++) {

    var id = 'speed-' + i;
    var value = values[i];
    var checked = '';

    if (1 == value) {
      checked = 'checked';
    }

    content += '<tr>';
    content += '<th><input id="' + id + '" type="radio" name="speed" value="' + value + '" ' + checked + '/></th>';
    content += '<td><label for="' + id + '">' + value + '</label></td>\n';
    content += '</tr>';
  }

  content += '</tbody></table></div>';

  document.getElementById('speed').innerHTML = content;
}

function humanFileSize(bytes, si=true) {

  var thresh = si ? 1000 : 1024;

  if (Math.abs(bytes) < thresh) {
      return bytes + ' B';
  }

  var units = si
      ? ['kB','MB','GB','TB','PB','EB','ZB','YB']
      : ['KiB','MiB','GiB','TiB','PiB','EiB','ZiB','YiB'];

  var u = -1;

  do {
      bytes /= thresh;
      ++ u;
  } while (Math.abs(bytes) >= thresh && u < units.length - 1);

  return bytes.toFixed(1) + ' ' + units[u];
}

function getFormat(name, index, format) {

  var id = name + '-' + index;

  var fields = [ 'format', 'codec', 'ext' ];

  var content = '<tr>';

  content += '<th><input id="' + id + '" type="radio" name="' + name + '" value="' + format['format_id'] + '"/></th>';

  for (var i = 0; i < fields.length; i ++) {
      content += '<td><label for="' + id + '">' + format[fields[i]] +'</label></td>\n';
  }

  var filesize = humanFileSize(format['filesize']);
  content += '<td><label for="' + id + '">' + filesize +'</label></td>\n';

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

function onInforSucceed(infor, content) {

  document.getElementById('notification').innerHTML = '';

  var obj = null;

  try {
    obj = JSON.parse(content);
  } catch (err) {
    document.getElementById('notification').innerHTML = "<h3>Failed to retrieve information for '"
      + infor.getUrl() + "', please try it again.</h3>";
    console.log(content);
    return
  }

  infor.setObj(obj);

  // Information
  var content = '<h3><a id="link" href="' + obj['webpage_url'] + '">' + obj['fulltitle'] + '</a></h3>'
    + '<p><div id="thumbnail"><img src="' + obj['thumbnail'] + '" /></div></p>'
    + '<h3>Duration: ' + obj['duration'] + ' seconds</h3>'
    + '</div>';

  document.getElementById('information').innerHTML = content;

  // Update groups
  var types = ['normal', 'video', 'audio'];

  for (var i = 0; i < types.length; i ++) {
    updateGroup(types[i], obj[types[i]]);
  }

  updateVideoFramerates(infor.getValue('fps'));
  updateAudioQualities(infor.getValue('abr'));

  document.getElementById('selection').style.display = 'block';

  updateSpeed();

  showTab('normal-tab');
}

function onInforError(url) {

  var content = '<h3>Retrieving information is failed for ' + url + '.</h3>';

  document.getElementById('notification').innerHTML = content;
}

function sendInforRequest(infor) {

  url = infor.getUrl();

  clearInterval(timer);

  var xhr = new XMLHttpRequest();

  xhr.onload = function() {

    console.log(xhr.status);

    var content = '';

    if (200 === xhr.status) {
      onInforSucceed(infor, xhr.responseText);
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
    'video-tab': ['video', 'video-framerate', 'audio', 'audio-quality'],
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

  var content = '<p><input type="submit" value="Convert ' + type.toUpperCase() + '" onclick="convert(information, \'' + type + '\'); return false;" /></p>';

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

  var mediaUrl = 'files/' + data['id'] + '/' + data['filename'];
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

function sendRequest(infor) {

  url = infor.getUrl();

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

  var payload = infor.toRequestParams();

  xhr.open('POST', 'convert.php', true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.send(payload);
}

function getRadioSelected(elementId) {
  var element = document.getElementById(elementId);
  var radios = element.getElementsByTagName('input');

  for (var j = 0; j < radios.length; j ++) {

    var radio = radios[j];

    if (radio.type === 'radio' && radio.checked) {
      return radio.value;
    }
  }

  return null;
}

function convert(infor, type) {

  var map = {
    'normal': ['normal'],
    'video+audio': ['video', 'video-framerate', 'audio', 'audio-quality'],
    'audio': ['audio', 'audio-quality']
  };

  var count = 0;
  var ids = map[type];

  for (i = 0; i < ids.length; i ++) {

    var elementId = ids[i];
    var value = getRadioSelected(elementId);
    if (null != value) {
        infor.setValue(elementId, value);
        count ++;
	}
  }

  infor.setValue('type', type);

  var value = getRadioSelected('speed');
  infor.setValue('speed', value);

  if (count == ids.length) {
    timer = setInterval(function () { sendRequest(infor); }, 1000);
  } else {
    console.log('No input');
  }
}

class Information {

  constructor(url) {

    this.url = url;
    this.params = {};
  }

  setObj(obj) {

    this.url = obj['webpage_url'];

    this.setValue('id', obj['id']);
    this.setValue('fulltitle', obj['fulltitle']);
    this.setValue('thumbnail', obj['thumbnail']);
    this.setValue('duration', obj['duration']);
    this.setValue('abr', obj['abr']); // Average Bitrate
    this.setValue('fps', obj['fps']);
  }

  setValue(key, value) {
    this.params[key] = value;
  }

  getValue(key) {
    return this.params[key];
  }

  getUrl() {
    return this.url;
  }

  toRequestParams() {

    var payload = 'url=' + encodeURIComponent(this.url);

    for (var key in this.params) {
      payload += '&' + key + '=' + encodeURIComponent(this.params[key]);
    }

    console.log(this.params);

    return payload;
  }
};

