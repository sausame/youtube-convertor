/**
 * Media files
 */

function getExt(filename) {
  var pos = filename.lastIndexOf('.');

  if (pos < 0) {
    return null;
  }

  return filename.substring(pos + 1);
}

function getMediaType(ext) {

  if (null == ext) {
    return null;
  }

  var exts = ['mp4', 'webm', 'ogg', 'mp3', 'wav'];
  var mediatypes = ['mp4', 'webm', 'ogg', 'mpeg', 'wav'];

  for (var i = 0; i < exts.length; i ++) {
    if (ext.endsWith(exts[i])) {
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

  var medias = obj['files'];

  for (var i = 0; i < medias.length; i ++) {

    var media = medias[i];

    var id = media['id'];
    var originalUrl = media['url'];
    var title = media['fulltitle'];
    var filename = media['filename'];

    var ext = getExt(filename)
    var mediatype = getMediaType(ext);
    var type = getType(mediatype);

    var url = 'files/' + id + '/' + filename;
    var newFilename = title + '.' + ext;

    var file = [url, type, mediatype, title];

    files.push(file);

    content += '<tr>';
    content += '<th>' + (i+1) + '</th>';
    content += '<td><a href="#" onclick="playFile(' + i + '); return false;">' + title + '</a></td>';
    content += '<td><a href="' + url + '" download="' + newFilename + '">Download</a></td>';
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

  console.log('Play NO.' + index);

  var file = files[index];

  var url = file[0];
  var type = file[1];
  var mediatype = file[2];
  var title = file[3];

  var content = '<h2>' + title + '</h2>'
    + '<' + type + ' controls>'
    + '<source src="' + url + '" type="' + type + '/' + mediatype + '">' + 'Your browser does not support the ' + type + ' tag.'
    + '</' + type + '>';

  document.getElementById('player').innerHTML = content;
}

var files = [];
var timer = setInterval(getData, 1000);

