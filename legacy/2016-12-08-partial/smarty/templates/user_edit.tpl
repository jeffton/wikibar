{head at="user" scripts="ajax, edit, help" title="Redig&eacute;r bruger"}
<form action="save.php" method="post" id="editform">
  {editbar type="user" id=$row.id action="edit"}
  <h1>Redig&eacute;r bruger</h1>
  <div class="content">
    {notice class="info"}
      Redig&eacute;r din side her.
    {/notice}
  
    {notice class="error"}
      { $error }
    {/notice}
    <input type="hidden" name="type" value="user" />
    <input type="hidden" name="id" value="{ $row.id }" />
    {item title="Navn" helptopic="user_name"}
      <input type="text" size="24" name="name" value="{ $row.name }"/>
    {/item}
    
    {passwordchooser edit=true row=$row}
    
    {item title="E-mail" helptopic="user_mail"}
      <label>Mail-adresse <input type="text" name="mail" size="30" value="{$row.mail}" /></label><br />
      {if $row.mail}
        {if $row.mailConfirmed}
          <span class="green">Bekræftet</span>
        {else}
          <span class="red">Ikke bekræftet</span>
        {/if}
      {/if}
    {/item}
  
    {urlchooser row=$row}
  
    {item style="large" title="Lidt om dig"}
      {editor text=$row.text}
    {/item}
    {clear}
  </div>
  {editbar type="user" id=$row.id action="edit"}
</form>
{foot}
