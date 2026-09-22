{head title="Redig&eacute;r band" scripts="edit" at="band"}

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
    {edititem title="Navn"}
      <input size="30" type="text" name="name" value="{ $row.name }" />
    {/edititem}

    {urlchooser row=$row}
  
    {edititem title="Beskrivelse" style="large"}
      {editor text=$row.text}
    {/edititem}
  </div>
  {editbar type="band" id=$row.id action="edit"}
</form>

{foot}