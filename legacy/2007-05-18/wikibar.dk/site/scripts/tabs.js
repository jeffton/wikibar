function tabSelect(id, index, total) {
  var selected;
  for (var i=1; i<=total; i++) {
    selected = (i == index);
    byId(id + 'pane' + i).style.display = (selected ? 'block' : 'none');
    byId(id + i).className = (selected ? 'active' : 'inactive');      
  }
}