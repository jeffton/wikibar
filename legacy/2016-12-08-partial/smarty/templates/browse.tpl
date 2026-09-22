{head title=$title at=$type}

{editbar type=$type}
<h1>{ $title }</h1>
<div class="content">
  {if $pages}
    <p>
      V&aelig;lg begyndelsesbogstaver eller s&oslash;g:
    </p>
    {section name="letter" loop=$pageletters}
      <div class="pagebutton">
        <a class="button{if $smarty.section.letter.iteration eq $page} activebutton{/if}" href="{$type}s{if not $smarty.section.letter.first}/{$smarty.section.letter.iteration }{/if}">{$pageletters[letter][0]}{if $pageletters[letter][1]} <strong>&ndash;</strong> {$pageletters[letter][1]}{/if}</a>
      </div>
    {/section}
  {/if}
  
  <form action="browse.php" method="get" class="searchform" id="searchform">
    <div class="pagebutton">
      <input type="hidden" name="type" value="{ $type }" />
      <input type="text" name="search" value="{ $search }" />
      <a class="button" href="javascript:document.forms.searchform.submit()">S&oslash;g</a>
      <input type="submit" class="hidden" value="s&oslash;g" />
    </div>
  </form>
  
  {clear}
  
  {if count($results)}
    <div class="column">
      <ol>
        {section name="i" loop=$results}
          <li>{link type=$type row=$results[i]}</li>
          {if $smarty.section.i.iteration eq ($perpage/2)}
            </ol></div><div class="column"><ol start="{$perpage/2+1}">
          {/if}
        {/section}
      </ol>
    </div>
  {else}
    <p><em>(ingen resultater)</em></p>
  {/if}
</div>
{foot}