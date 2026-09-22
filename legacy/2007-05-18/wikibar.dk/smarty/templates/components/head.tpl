<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
  <head>
    <title>{if $title}{ $title } p&aring; {/if}wikibar.dk</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="layout/style.css?v={$scriptversion}" />
    <link rel="icon" type="image/png" href="layout/favicon.png" />
    {section name="script" loop=$scripts}
      <script type="text/javascript" src="scripts/{ $scripts[script] }.js?v={$scriptversion}"></script>
    {/section}
  </head>
  <body{if $onload} onload="{$onload}"{/if}>
    <div class="menu">
      <div class="menucontent">
        <a href="index.php"><img src="layout/menu_calendar{if $at=="calendar"}_selected{/if}.png" width="122" height="51" alt="kalender" /></a>
        <a href="browse.php?type=venue"><img src="layout/menu_venue{if $at=="venue"}_selected{/if}.png" width="161" height="51" alt="spillesteder" /></a>
        <a href="browse.php?type=band"><img src="layout/menu_band{if $at=="band"}_selected{/if}.png" width="85" height="51" alt="bands" /></a>
        <a href="browse.php?type=user"><img src="layout/menu_user{if $at=="user"}_selected{/if}.png" width="111" height="51" alt="brugere" /></a>
      </div>
    </div>
    <div class="main">
    {if $help}
      <div id="helpbox" style="display:none;">
        <div class="minibar">
          <a class="button" href="javascript:helpClose()">Luk</a>
        </div>
        <div id="helpprogress" style="display:none;"><img src="layout/loading.gif" /></div>
        <div id="helpdisplay">&nbsp;</div>
      </div>
    {/if}
    {if $script}
      <noscript>
        {notice class="error"}
          Denne side fungerer ikke uden JavaScript.<br />
          JavaScript ser ud til at v&aelig;re sl&aring;et fra i din browser.
        {/notice}
      </noscript>
    {/if}