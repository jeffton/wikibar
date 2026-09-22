{head title=$title at=$type}

{editbar type=$type}
<h1>{ $title }</h1>
<div class="content">
  {if $pages}
    <p>
      V&aelig;lg begyndelsesbogstaver eller s&oslash;g:
    </p>
    {section name="letter" loop=$pageletters}
      <a class="button" href="browse.php?type={$type}&page={$smarty.section.letter.iteration }">{$pageletters[letter][0]}{if $pageletters[letter][1]} <strong>&ndash;</strong> {$pageletters[letter][1]}{/if}</a>
    {/section}
  {/if}
  
  <form action="browse.php" method="get" class="searchform" id="searchform">
    <input type="hidden" name="type" value="{ $type }" />
    <input type="text" name="search" value="{ $search }" />
    <a class="button" href="javascript:document.forms.searchform.submit()">S&oslash;g</a>
    <input type="submit" class="hidden" value="s&oslash;g" />
  </form>
  
  
  {if count($results)}
    <ul>
      {section name="i" loop=$results}
        <li>{link type=$type row=$results[i]}</li>
      {/section}
    </ul>
  {else}
    <p><em>(ingen resultater)</em></p>
  {/if}
</div>
{foot}