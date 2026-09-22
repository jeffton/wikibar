{head title="Redig&eacute;r band" scripts="edit, help, ajax" at="band"}
<form action="save.php" method="post" id="editform">
  {editbar type="band" id=$row.id action="edit"}
  {if $row.id}
    <h1>Redig&eacute;r band</h1>
  {else}
    <h1>Opret band</h1>
  {/if}
  <div class="content">
    {notice class="info"}
      Alle oplysninger undtagen bandets navn er valgfri. Indtast hvad du ved...
    {/notice}
    {notice class="error"}
      { $error }
    {/notice}
  
    <input type="hidden" name="type" value="band" />
    <input type="hidden" name="id" value="{ $row.id }" />
    
    {item title="Navn"}
      <input size="30" type="text" name="name" value="{ $row.name }" />
    {/item}
    
    {item title="Landekode" helptopic="band_country"}
      <input size="10" type="text" name="country" value="{ $row.country }" />
    {/item}

    {urlchooser row=$row}
  
    {item title="Beskrivelse" style="large"}
      {editor text=$row.text}
    {/item}
    
  </div>
  {editbar type="band" id=$row.id action="edit"}
</form>
{foot}