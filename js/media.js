/**
 * Media files
 */

function getMediaType(filename) {

  var exts = ['mp4', 'webm', 'ogg', 'mp3', 'wav'];
  var mediatypes = ['mp4', 'webm', 'ogg', 'mpeg', 'wav'];

  for (var i = 0; i < exts.length; i ++) {
    if (filename.endsWith(exts[i])) {
      return mediatypes[i];
    }
  }

  return null;
}

function getType(mediatype) {

  var mediatypes = ['mp4', 'webm', 'ogg', 'mpeg', 'wav'];
  var types = ['video','video', 'video', 'audio', 'audio'];

  for (var i = 0; i < mediatypes.length; i ++) {
    if (mediatype == mediatypes[i]) {
      return types[i];
    }
  }

  return null;
}

function onSucceed(content) {

  var obj = JSON.parse(content);

  var num = obj['num'];

  if (0 == num) {
    onError();
    return;
  }

  var content = '<div>'
     + '<table class="tb">'
     + '<thead>'
     + '<tr>'
     + '<th width="10%"></th>'
     + '<th>Filenames</th>'
     + '<th>Download</th>'
     + '</tr>'
     + '</thead>';
     + '<tbody>';

  var names = obj['files'];

  for (var i = 0; i < names.length; i ++) {

    var name = names[i];
    var mediatype = getMediaType(name);
    var type = getType(mediatype);

    var file = [name, type, mediatype];
    var url = 'files/' + name;

    files.push(file);

    content += '<tr>';
    content += '<th>' + (i+1) + '</th>';
    content += '<td><a href="#" onclick="playFile(' + i + '); return false;">' + name + '</a></td>';
    content += '<td><a href="' + url + '" download>Download</a></td>';
    content += '</tr>';
  }

  content += '</tbody></table></div>';

  document.getElementById('files').innerHTML = content;
}

function onError() {

  var content = '<h2>No content</h2>';

  document.getElementById('files').innerHTML = content;
}

function getData() {

  clearInterval(timer);

  var xhr = new XMLHttpRequest();

  xhr.onload = function() {

    console.log(xhr.status);

    if (200 === xhr.status) {
      onSucceed(xhr.responseText);
    } else {
      onError();
    }
  };

  xhr.open('GET', 'mediafiles.php', true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.send();
}

function playFile(index) {

  console.log(index);

  var file = files[index];

  var url = 'files/' + file[0];
  var type = file[1];
  var mediatype = file[2];

  var content = '<' + type + ' controls>'
    + '<source src="' + url + '" type="' + type + '/' + mediatype + '">'
    + 'Your browser does not support the ' + type + ' tag.'
    + '</' + type + '>';

  document.getElementById('player').innerHTML = content;
}

var files = [];
var timer = setInterval(getData, 1000);

