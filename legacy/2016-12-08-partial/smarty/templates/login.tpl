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

{tabs labels="Log ind, Ny bruger, Glemt password" selected=$selectedtab group="logintabs"}
{tabpane group="logintabs" index=1 selected=$tabselected[1]}
  {notice class="error"}
    {$error_login}
  {/notice}
  <form action="login.php" method="post" id="loginform">
    <input type="hidden" name="proceed" value="{$proceed}" />
    <input type="hidden" name="passthrough" value="{$passthrough}" />
    <input type="hidden" name="action" value="login" />
    {item title="Navn"}
      <input type="text" name="name" value="{$name_login}" />
    {/item}
    {item title="Password"}
      <input type="password" name="password" value="{$password_login}" /><br />
      <label><input type="checkbox" name="remember" class="checkbox" /> Husk mig</label>
      {help topic="login_remember"}
    {/item}
    {item title="&nbsp;"}
      <a href="javascript:document.forms.loginform.submit()" class="button">Log ind</a>
    {/item}
    <input type="submit" value="Log ind" class="hidden">
    {clear}
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
    {item title="Navn" helptopic="user_name"}
      <input type="text" name="name" value="{$name_create}" />
    {/item}

    {passwordchooser password=$password_create passwordrepeat=$passwordrepeat}
    
    {item title="&nbsp;"}
      <a href="javascript:document.forms.createform.submit()" class="button">Opret bruger</a>
    {/item}
    <input type="submit" class="hidden" value="Opret bruger" />
  </form>
  {clear}
{/tabpane}
{tabpane group="logintabs" index=3 selected=$tabselected[3]}
  {notice class="info"}
    {if $info_reset}
      {$info_reset}
    {else}
      Indtast dit brugernavn og din mail-adresse her. Du vil modtage en mail med et link, du kan bruge til at logge ind og &aelig;ndre dit password.
      Har du ogs&aring; glemt dit brugernavn, kan du se om du kan finde dig p&aring; <a href="{url type='user'}">listen over brugere</a>.
    {/if}
  {/notice}
  {notice class="error"}
    {$error_reset}
  {/notice}
  <form action="login.php" method="post" id="resetform">
    <input type="hidden" name="proceed" value="{$proceed}" />
    <input type="hidden" name="passthrough" value="{$passthrough}" />
    <input type="hidden" name="action" value="reset" />
    {item title="Navn"}
      <input type="text" name="name" value="{$name_reset}" />
    {/item}

    {item title="E-mail"}
      <input type="text" name="mail" value="{$mail_reset}" />
    {/item}
    
    {item title="&nbsp;"}
      <a href="javascript:document.forms.resetform.submit()" class="button">Send</a>
    {/item}
    <input type="submit" class="hidden" value="Send" />
    {clear}
  </form>
  {clear}
{/tabpane}
{foot}