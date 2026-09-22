{if $chooser_mode eq "multiple"}
  <ul id="{$chooser_id}">
{/if}
  {section name="v" loop=$chooser_values}
    {if $chooser_mode eq "multiple"}
      <li id="{$chooser_id}{$smarty.section.v.iteration}">
    {/if}
      <input type="text" id="{$chooser_id}Field{$smarty.section.v.iteration}" name="{$chooser_id}field[]"
      value="{$chooser_values[v].name}" onkeyup="chooserSearch('{$chooser_id}', {$smarty.section.v.iteration})" 
      size="30" autocomplete="off" /><!-- autocomplete is non-standard but very useful, so there. -->
      <a class="button" href="javascript:chooserSearch('{$chooser_id}', {$smarty.section.v.iteration}, true)">S&oslash;g</a>
      {if $chooser_mode eq "multiple"}
        <a class="button" href="javascript:chooserRemove('{$chooser_id}', {$smarty.section.v.iteration})">Fjern valg</a>
      {/if}
      <span id="{$chooser_id}Progress{$smarty.section.v.iteration}" style="visibility:hidden"><img src="layout/loading.gif" alt="..." /></span>
      <div id="{$chooser_id}Results{$smarty.section.v.iteration}">
        {chooser_results search=$chooser_values[v].name name=$chooser_name index=$smarty.section.v.iteration type=$chooser_type selected=$chooser_selected[v]}
      </div>
    {if $chooser_mode eq "multiple"}
      </li>
    {/if}
  {/section}
{if $chooser_mode eq "multiple"}
  </ul>
  <a class="button" href="javascript:chooserAdd('{$chooser_id}')">Flere {typename type=$chooser_type plur=1}</a>
{/if}
