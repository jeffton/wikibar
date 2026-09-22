<div class="item{ $item_style }">
  {if $item_title or $item_helptopic}
    <h2>{ $item_title }{if $item_helptopic}{help topic=$item_helptopic}{/if}</h2>
  {/if}
  { $item_content }
</div>