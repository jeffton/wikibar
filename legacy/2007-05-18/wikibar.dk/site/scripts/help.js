function help(topic) {
  byId('helpdisplay').innerHTML = '&nbsp;';
  byId('helpbox').style.top = getScroll() + getWindowHeight()/4 + 'px';
  byId('helpbox').style.display = 'block';
  var params = new Array();
  params['topic'] = topic;
  new Ajax('help.php', params, 'helpdisplay', 'helpprogress');
}

function helpClose() {
  byId('helpbox').style.display = 'none';
  byId('helpdisplay').innerHTML = '&nbsp;';
}

function getScroll() {
  // http://www.howtocreate.co.uk/tutorials/javascript/browserwindow
  if (typeof(window.pageYOffset) == 'number') { // firefox/opera
    return window.pageYOffset;
  } else if (document.documentElement && document.documentElement.scrollTop) { // IE
    return document.documentElement.scrollTop;
  } else {
    return 0;
  }
}

function getWindowHeight() {
  if (window.innerHeight) {
    return window.innerHeight;
  } else if (document.documentElement) {
    return document.documentElement.clientHeight;
  } else {
    return 0;
  }
}
 