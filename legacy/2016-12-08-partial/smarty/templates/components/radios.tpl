{section name="radio" loop=$radio_labels}
  <label><input type="radio" class="radio" name="{$radio_name}" value="{$radio_values[radio]}" {if $radio_checked==$radio_values[radio]}checked="checked" {/if}/>
  {$radio_labels[radio]}</label><br />
{/section}
