<div class="edititem{ $edititem_style }">
  {if $edititem_title or $edititem_helptopic}
    <h2>{ $edititem_title }{if $edititem_helptopic}{help topic=$edititem_helptopic}{/if}</h2>
  {/if}
  { $edititem_content }
</div>