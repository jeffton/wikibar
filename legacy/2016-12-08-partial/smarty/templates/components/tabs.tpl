<ul class="tabs">
  {section name="t" loop=$tabs_labels}
    <li id="{$tabs_group}{$smarty.section.t.iteration}"class="{if $tabs_selected != $smarty.section.t.iteration}in{/if}active">
      <a href="javascript:tabSelect('{$tabs_group}', {$smarty.section.t.iteration}, {$tabs_count})">
        { $tabs_labels[t] }</a></li>
  {/section}
</ul>