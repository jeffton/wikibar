
function Ajax(url, parameters, target, progress) {
  var that = this;
  this.target = byId(target);
  this.progress = byId(progress);
  this.url = url;

  if (this.progress.style.visibility == 'hidden') {
    this.progress.style.visibility = '';
    this.hideBy = 'v';
  } else if (this.progress.style.display == 'none') {
    this.progress.style.display = 'inline';
    this.hideBy = 'd';
  }

  var paramstring = '';
  var delim = '';
  for (key in parameters) {
    paramstring += delim + key + '=' + this.urlencode(parameters[key]);
    delim = '&';
  }
  parameters = paramstring;
  this.request = this.getXMLHttpObject();
  this.request.open("POST", url, true);
  this.request.onreadystatechange = function() {
    if (that.request.readyState == 4 || that.request.readyState == "complete") {
      if (that.hideBy == 'v') {
        that.progress.style.visibility = 'hidden';
      } else if (that.hideBy == 'd') {
        that.progress.style.display = 'none';
      }
      that.target.innerHTML = that.request.responseText;
    }
  }
  this.request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  this.request.setRequestHeader("Content-length", parameters.length);
  this.request.send(parameters);
}


// --------------------------------------------------------------------------------------


Ajax.prototype.getXMLHttpObject = function() {
  var request = null;
  try {
    request = new ActiveXObject("Msxml2.XMLHTTP");
  } catch (e) {
    try {
      request = new ActiveXObject("Microsoft.XMLHTTP");
    } catch (e) {
    }
  }
  if (request == null) {
    request = new XMLHttpRequest();
  }
  return request;
}

Ajax.prototype.urlencode = function(str) { // & og + skal have special treatment, ser det ud til...
  var str = encodeURI(str).replace(/\+/g, "%2B").replace(/\&/g, "%26");
  return str;
}