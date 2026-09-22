<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>{if $title}{ $title } p&aring; {/if}wikibar.dk</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <base href="{$host}{$folder}" />
    <link rel="stylesheet" type="text/css" href="layout/style.css?v={$scriptversion}" />
    <link rel="icon" type="image/png" href="layout/favicon.png" />
    <link rel="shortcut icon" type="image/x-icon" href="layout/favicon.ico" />
    <link rel="apple-touch-icon" type="image/png" href="/layout/apple-touch-icon.png" />
    {if $scripts}
      <script type="text/javascript">
        var _folder = '{$folder}';
      </script>
    {/if}
    {strip}
      {section name="script" loop=$scripts}
        <script type="text/javascript" src="{ $scripts[script] }
          {if substr($scripts[script], 0, 7) != 'http://'}
            .js?v={$scriptversion}
          {/if}"></script>
      {/section}
    {/strip}
  </head>
  <body{if $onload} onload="{$onload}"{/if}{if $onunload} onunload="{$onunload}"{/if}>
    <div class="menu">
      <div class="menucontent">
        <a href="{url type='calendar'}"><img src="layout/menu_calendar{if $at=="calendar"}_selected{/if}.png" width="122" height="51" alt="kalender" /></a>
        <a href="{url type='venue'}"><img src="layout/menu_venue{if $at=="venue"}_selected{/if}.png" width="161" height="51" alt="spillesteder" /></a>
        <a href="{url type='band'}"><img src="layout/menu_band{if $at=="band"}_selected{/if}.png" width="85" height="51" alt="bands" /></a>
        <a href="{url type='user'}"><img src="layout/menu_user{if $at=="user"}_selected{/if}.png" width="111" height="51" alt="brugere" /></a>
      </div>
      <div class="login">
        <span>{$today}</span>
        <span>&bull;</span>
        {if $user}
          <span>{$user.name}</span>
          <a class="button" href="{url type='user' row=$user}">Min side</a>
          <a class="button" href="login.php?action=logout">Log ud</a>
        {else}
          <a class="button" href="login.php">Log ind/Ny bruger</a>
        {/if}
      </div>
    </div>
    <div class="main">
    {if $help}
      <div id="helpbox" style="display:none;">
        <div class="minibar">
          <a class="button" href="javascript:helpClose()">Luk</a>
        </div>
        <div id="helpprogress" style="display:none;"><img src="layout/loading.gif" alt="indl&aelig;ser..." /></div>
        <div id="helpdisplay">&nbsp;</div>
      </div>
    {/if}
    {if $scripts}
      <noscript>
        {notice class="error"}
          Denne side fungerer ikke uden JavaScript.<br />
          JavaScript ser ud til at v&aelig;re sl&aring;et fra i din browser.
        {/notice}
      </noscript>
    {/if}