{section name="i" loop="$results"}
  <label><input type="radio" class="radio" name="{$results_name}" value="id:{$results[i].id}" {if ((!$results_selected) and ($smarty.section.i.iteration == 1)) or ($results_selected == $results[i].id)}checked="checked"{/if} /> {$results[i].name}</label><br />
{/section}
{if $results_createallowed}
  <label><input type="radio" class="radio" name="{$results_name}" value="name:{$results_search}"
  {if (!$results) or ($results_selected == "create")}checked="checked"{/if} /> <strong>Opret {typename type=$results_type}:</strong> { $results_search }</label>
{/if}