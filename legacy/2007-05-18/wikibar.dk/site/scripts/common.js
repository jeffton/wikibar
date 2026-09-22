function byId(id) {
  return document.getElementById(id);
}

function byName(name) {
  return document.getElementsByName(name);
}

function trim(str) {
  return str.replace(/^\s+|\s+$/g,"");
}