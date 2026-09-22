<div class="editbar{if $class} {$class}{/if}">
  {if $backurl}
    <a class="button" href="{ $backurl }">Tilbage</a>
  {/if}
  {if $editurl}
    <a class="button" href="{ $editurl }">Redig&eacute;r {typename type=$type}</a>
  {/if}
{*  {if $historyurl}
    <a class="button" href="{ $historyurl }">Historik</a>
  {/if}*}
  {if $createurl}
    <a class="button" href="{ $createurl }">Opret {typename type=$type}</a>
  {/if}
  {if $savebutton}
    <a class="button" href="javascript:document.forms.editform.submit()">Gem</a><input type="submit" value="Gem" class="hidden" />
  {/if}
</div>
