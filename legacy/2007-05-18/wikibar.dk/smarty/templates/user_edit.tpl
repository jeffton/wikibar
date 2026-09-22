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
    {edititem title="Navn" helptopic="user_name"}
      <input type="text" size="24" name="name" value="{ $row.name }"/>
    {/edititem}
    
    {edititem title="Password" helptopic="user_password_edit"}
      Hvis du vil skifte password:
      <table>
        <tr>
          <td>Indtast</td>
          <td>
            <input type="password" size="20" name="password" value="{ $row.password }"/>
          </td>
        </tr>
        <tr>
          <td>Gentag</td>
          <td>
            <input type="password" size="20" name="passwordrepeat" value="{ $row.passwordrepeat }"/>
          </td>
        </tr>
      </table>
    {/edititem}
  {*  
    {edititem title="E-mail" helptopic="user_mail"}
      <label>Mail-adresse <input type="text" name="mail" size="30" value="{$row.mail}" /></label><br />
      <label><input type="checkbox" class="checkbox" name="mail_newsletter"> Modtag ugentlig koncertoversigt</label><br />
      <label><input type="checkbox" class="checkbox" name="mail_message"> Modtag beskeder fra andre brugere</label>
    {/edititem}
  *}  
    {urlchooser row=$row}
  
    {edititem style="large" title="Lidt om dig"}
      {editor text=$row.text}
    {/edititem}
    {clear}
  </div>
  {editbar type="user" id=$row.id action="edit"}
</form>
{foot}