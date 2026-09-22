{head title="Log ind" at="user" scripts="tabs, ajax, help" onload="document.forms.loginform.name.focus()"}

{if $back}
  <div class="editbar abovetabs">
    <a class="button" href="{$back}">Tilbage</a>
  </div>
{/if}

{if $passthrough}
  {notice class="info"}
    Du skal logge ind eller oprette en bruger for at forts&aelig;tte.
  {/notice}
{/if}

{tabs labels="Log ind, Ny bruger" selected=$selectedtab group="logintabs"}
{tabpane group="logintabs" index=1 selected=$tabselected[1]}
  {notice class="error"}
    {$error_login}
  {/notice}
  <form action="login.php" method="post" id="loginform">
    <input type="hidden" name="proceed" value="{$proceed}" />
    <input type="hidden" name="passthrough" value="{$passthrough}" />
    <input type="hidden" name="action" value="login" />
    {edititem title="Navn"}
      <input type="text" name="name" value="{$name_login}" />
    {/edititem}
    {edititem title="Password"}
      <input type="password" name="password" value="{$password_login}" />
    {/edititem}
    {edititem title="&nbsp;"}
      <a href="javascript:document.forms.loginform.submit()" class="button">Log ind</a>
    {/edititem}
    <input type="submit" value="Log ind" class="hidden">
  </form>
  {clear}
{/tabpane}
{tabpane group="logintabs" index=2 selected=$tabselected[2]}
  {notice class="info"}
    V&aelig;lg et brugernavn og et password.<br />
    N&aring;r du f&oslash;rst er oprettet, kan du tilpasse din side hvis du
    har lyst.
  {/notice}
  {notice class="error"}
    { $error_create }
  {/notice}
  <form action="login.php" method="post" id="createform">
    <input type="hidden" name="proceed" value="{$proceed}" />
    <input type="hidden" name="passthrough" value="{$passthrough}" />
    <input type="hidden" name="action" value="create" />
    {edititem title="Navn" helptopic="user_name"}
      <input type="text" name="name" value="{$name_create}" />
    {/edititem}
    {edititem title="Password" helptopic="user_password"}
      <table>
        <tr>
          <td>
            Indtast
          </td>
          <td>
            <input type="password" name="password" value="{$password_create}" />
          </td>
        </tr>
        <tr>
          <td>
            Gentag
          </td>
          <td>
            <input type="password" name="passwordrepeat" value="{$passwordrepeat}" /></label>
          </td>
        </tr>
      </table>
    {/edititem}
    {edititem title="&nbsp;"}
      <a href="javascript:document.forms.createform.submit()" class="button">Opret bruger</a>
    {/edititem}
    <input type="submit" class="hidden" value="Opret bruger" />
  </form>
  {clear}
{/tabpane}



{foot}