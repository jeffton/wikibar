
function addTag(startTag, endTag, newlinestart, newlinemid, newlineend, content, more) {
  var field = byId('text');

  if (newlinemid) {
    startTag += '\n';
    endTag = '\n' + endTag;
  }

  if (document.selection) { // fjollekode til IE
    field.focus();

    if (newlineend) { //¤ burde kun være hvis tagget ikke er efterfulgt af \n i forvejen
      endTag += '\n';
    }

    //¤ newlinestart ignoreres
    range = document.selection.createRange();

    if (content) {
      range.collapse();
    }

    var full = ((range.text.length > 0) || (content && !more));

    range.text = startTag + (content ? content : range.text) + endTag;
    if (!full) {
      range.moveStart("character", -endTag.length);
    }
    range.collapse(!full);
    range.select();
    range.scrollIntoView();

  } else if (field.selectionStart || field.selectionStart == 0) { // standardmetoden til de andre
    if (content) {
      field.selectionEnd = field.selectionStart;
    }

    var start = field.selectionStart;
    var end = field.selectionEnd;
    var scroll = field.scrollTop;

    if (newlinestart && (start > 0)) {
      if (field.value.substring(start-1, start) != '\n') {
        startTag = '\n' + startTag;
      }
    }

    if (newlineend) {
      if (field.value.substring(end, end+1) != '\n') {
        endTag += '\n';
      }
    }

    field.value = field.value.substring(0, start) + startTag +
    (content ? content : field.value.substring(start, end)) +
    endTag + field.value.substring(end, field.value.length);

    var pos;
    if (content) {
      pos = end + startTag.length + content.length + (more ? 0 : endTag.length);
    } else if (start != end) {
      pos = end + startTag.length + endTag.length;
    } else {
      pos = start + startTag.length;
    }
  }
  field.focus();
  field.selectionStart = pos;
  field.selectionEnd = pos;
  field.scrollTop = scroll;
}

function addLinkTag() {
  var url = byId('linkurl').value;
  var title = document.getElementById('linktitle').value;
  addTag('[link=' + url + ']', '[/link]', false, false, false, title);
}

