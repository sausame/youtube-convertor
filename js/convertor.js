/**
 * Information
 */

function getAudioQualities() {

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
    content += '<th><input id="' + id + '" type="radio" name="quality" value="' + qualities[i] + '"/></th>';
    content += '<td><label for="' + id + '">' + qualities[i]+'kb</label></td>\n';
    content += '</tr>';
  }

  content += '</tbody></table></div>';

  return content;
}

function getFormat(id, name, index, format) {

  id += '-' + name + '-' + index;

  var fields = [ 'format', 'codec', 'ext', 'filesize' ];

  var content = '<tr>';

  content += '<th><input id="' + id + '" type="radio" name="' + name + '" value="' + format['format_id'] + '"/></th>';

  for (var i = 0; i < fields.length; i ++) {
      content += '<td><label for="' + id + '">' + format[fields[i]]+'</label></td>\n';
  }

  content += '</tr>';

  return content;
}

function getGroup(id, name, group) {

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
      content += getFormat(id, name, index, group[index]);
  }

  content += '</tbody></table>';

  return content;
}

function onInforSucceed(content) {

  document.getElementById('notification').innerHTML = '';

  var infor = JSON.parse(content);

  var url = infor['webpage_url'];

  // Information
  var content = '<h3><a href="' + url + '">' + infor['fulltitle'] + '</a></h3>'
    + '<p><img src="' + infor['thumbnail'] + '" /></p>'
    + '<h3>Duration: ' + infor['duration'] + ' seconds</h3>'
    + '</div>';

  document.getElementById('information').innerHTML = content;

  // Selection
  content = '';

  var names = ['normal', 'video+audio', 'audio'];
  var categories = [['normal'], ['video', 'audio'], ['audio']];
  var audioqualities = [false, false, true];
  var numbers = [1, 2, 2];

  for (var index = 0; index < names.length; index ++) {

    var id = 'id-' + index;
    var types = categories[index];

    content += '<div id="' + id + '">'
      + '<h2>' + names[index].toUpperCase() + '</h2>';

    for (var i = 0; i < types.length; i ++) {
      content += getGroup(id, types[i], infor[types[i]]);
    }

    if (audioqualities[index]) {
      content += getAudioQualities();
    }

    content += '<input type="submit" value="Convert ' + names[index] + '" onclick="convert(\'' + url + '\', \'' + names[index] + '\', \'' + id + '\', ' + numbers[index] + '); return false;" />'
      + '</div>';
  }

  document.getElementById('selection').innerHTML = content;
  document.getElementById('selection').style.display = 'block';
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

    document.getElementById('convertor').style.display = 'block';
  };

  xhr.open('POST', 'infor.php', true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.send('url=' + url);
}

/**
 * Convertion
 */

function onConvertionSucceed(url) {
  var content = '<h3>' + url + ' is converted.</h3>';

  document.getElementById('notification').innerHTML = content;
  document.getElementById('convertor').style.display = 'block';
  document.getElementById('selection').style.display = 'none';
}

function onConvertionError(url) {
  var content = '<h3>Convertion is failed for ' + url + '.</h3>';

  document.getElementById('notification').innerHTML = content;
  document.getElementById('convertor').style.display = 'block';
  document.getElementById('selection').style.display = 'block';
}

function sendRequest(url, type, names, values) {

  clearInterval(timer);

  var xhr = new XMLHttpRequest();

  xhr.onload = function() {

    console.log(xhr.status);
    console.log(xhr.responseText);

    var content = '';

    if (200 === xhr.status) {
      onConvertionSucceed(url);
    } else {
      onConvertionError(url); 
    }
  };

  document.getElementById('notification').innerHTML = '<h3>Converting ' + url + ' ...</h3>';
  document.getElementById('selection').style.display = 'none';
  document.getElementById('convertor').style.display = 'none';

  var payload = 'url=' + encodeURIComponent(url) + '&type=' + encodeURIComponent(type);

  for (var i = 0; i < names.length; i ++) {
    payload += '&' + names[i] + '=' + values[i];
  }

  xhr.open('POST', 'convertor.php', true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.send(payload);
}

function convert(url, type, id, number) {

  var element = document.getElementById(id);
  var radios = element.getElementsByTagName('input');

  var names = [];
  var values = [];
  var count = 0;

  for (var i = 0; i < radios.length; i++) {

    var radio = radios[i];

    if (radio.type === 'radio' && radio.checked) {

      names.push(radio.name);
      values.push(radio.value);

      count ++;
    }
  }

  if (count == number) {
    timer = setInterval(function () { sendRequest(url, type, names, values); }, 1000);
  } else {
    console.log('No input');
  }
}

