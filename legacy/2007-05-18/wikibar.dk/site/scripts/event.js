var timer;
var chooserIndex = new Array();
var chooserName = new Array();
var chooserType = new Array();


function isWhitespace(nod) {
  return !(/[^\t\n\r ]/.test(nod.data));
}

function init() {
  showDate('date');
  showDate('enddate');
  chooserInit('band', 'bands', 'multiple');
  chooserInit('venue', 'venue', 'single');
//  chooserInit('user', 'invitees', 'multiple');
//  chooserInit('user', 'organizers', 'multiple');
}

// -----------------------------------------------------------

function chooserInit(type, name, mode) {
  var id = 'chooser_' + name;
  if (mode == "multiple") {
    var ul = byId(id); // firefox har whitespace-nodes.
    var lis = ul.childNodes;
    for (var i=0; i<lis.length; i++) {
      if (isWhitespace(lis[i])) {
        ul.removeChild(lis[i]);
      }
    }
    chooserIndex[id] = ul.childNodes.length + 1;
  }
  chooserName[id] = name;
  chooserType[id] = type;
}

function chooserRemove(id, index) {
  var ul = byId(id);
  var li = byId(id + index);
  ul.removeChild(li);
  if (!ul.hasChildNodes()) {
    chooserIndex[id] = 1;
    chooserAdd(id);
  }
}

function chooserAdd(id) {
  var index = chooserIndex[id];
  chooserIndex[id] += 1;
  var li = document.createElement('li');
  li.setAttribute('id', id + index);
  byId(id).appendChild(li);
  li.innerHTML =
    '<input type="text" id="' + id + 'Field'+ index + '" name="' + id + 'field[]"' +
    '    value="" onkeyup="chooserSearch(\'' + id + '\', ' + index + ')" size="30" /> ' +
    '<a class="button" href="javascript:chooserSearch(\'' + id + '\', ' + index + ', true)">S&oslash;g</a> ' +
    '<a class="button" href="javascript:chooserRemove(\'' + id + '\', ' + index + ')">Fjern valg</a>' +
    '<span id="' + id + 'Progress' + index + '" style="visibility:hidden"><img src="layout/loading.gif" alt="..." /></span>' +
    '<div id="' + id + 'Results' + index + '">&nbsp;</div>';
}

function chooserSearch(id, index, force) {
  clearTimeout(timer);
  if (force) {
    search(id, index);
  } else {
    timer = setTimeout("search('" + id + "', '" + 
        index + "', " + force + ")", 300);
  }
}

function search(id, index) {
  var progress = id + 'Progress' + index;
  var results = id + 'Results' + index;
  var field = byId(id + 'Field' + index);
  var parameters = new Array();
  parameters['search'] = field.value;
  parameters['type'] = chooserType[id];
  parameters['index'] = index;
  parameters['name'] = chooserName[id];
  new Ajax('chooser_results.php', parameters, results, progress);
}


// ---------------------------------------------------------------


function bandAdd() {

}


/*********************************************************
 *   DATO
 *********************************************************/


function replaceAll(source, before, after) {
  while (source.indexOf(before) != -1) {
    source = source.replace(before, after);
  }
  return source;
}


function parseDate(datestring) {
  datestring = trim(datestring);
  if (!datestring) {
    return null;
  }

  var current = new Date();

  datestring = replaceAll(datestring, ".", " ");
  datestring = replaceAll(datestring, ",", " ");
  datestring = replaceAll(datestring, "-", " ");
  datestring = replaceAll(datestring, "/", " ");
  datestring = replaceAll(datestring, "'", " ");
  datestring = replaceAll(datestring, ":", " ");

  datestring = replaceAll(datestring, "  ", " ");


  if (!datestring) {
    return null;
  }

  var dateterms = datestring.split(" ");

  var day = parseInt(dateterms[0], 10);
  if (!day || day > 31) {
    day = current.getDate();
  }

  var months = "januar februar marts april maj juni juli august september oktober november december".split(" ");
  var month = dateterms[1];

  var defaultmonth = current.getMonth();
  if (day < current.getDate()) {
    defaultmonth = (defaultmonth + 1) % 12;
  }

  var found = false;
  if (month) {
    var monthnum = parseInt(month, 'int');
    if (monthnum && (monthnum <= 12)) {
      month = monthnum - 1;
      found = true;
    } else {
      var loopat = defaultmonth;
      for (var i=0; i<months.length; i++) {
        if (months[loopat].substring(0, month.length) == month) {
          month = loopat;
          found = true;
          break;
        }
        loopat = (loopat + 1) % 12;
      }
    }
  }

  if (!found) {
    month = defaultmonth;
  }

  var year = parseInt(dateterms[2], 10);

  if (!year && year != 0) {
    year = current.getFullYear() + (month < current.getMonth() || (month == current.getMonth() && day < current.getDate()));
  } else {
    if (year < 100) {
      var currentyear = current.getFullYear();
      var twodigits = currentyear % 100;
      var century = currentyear - twodigits;
      var future = 60;

      if ((year+100)%100 <= ((twodigits+future) % 100)) {
        year += century + 100 * (twodigits+future >= 100);
      } else {
        year += century - 100 + 100 * (twodigits+future >= 100);
      }
    }
  }

  resultdate = new Date(year, month, day);

  var weekdays = "s&oslash;ndag mandag tirsdag onsdag torsdag fredag l&oslash;rdag".split(" ");
  var weekday = resultdate.getDay();
  resultstring = day + ". " + months[month] + " " + year + " (" + weekdays[weekday] + ")";

  return new Array(year + '-' + (month+1) + '-' + day, resultstring);
}

function showDate(name) {
  fromwhere = name + 'text';
  savewhere = name + 'value';
  towhere = name + 'display';
  var date = parseDate(byId(fromwhere).value);
  if (date) {
    byId(savewhere).value = date[0];
    byId(towhere).innerHTML = date[1];
  } else {
    byId(savewhere).value = "";
    byId(towhere).innerHTML = "";
  }

}