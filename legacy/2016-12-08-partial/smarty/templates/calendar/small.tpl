{if count($events)}
  {section name="e" loop=$events}
    <div class="smallevent{if $smarty.section.e.last} lastsmallevent{/if}" style="background-image: url('layout/eventsmall-{$events[e].eventtype}.png')">
      <div class="minibar">
        <a class="button" href="{url type='event' id=$events[e].id}">Vis</a>
      </div>
      <h3>
        {$events[e].date}
      </h3>
      {if $events[e].name}
        <h4>{$events[e].name}</h4>
      {/if}
      
      {if count($events[e].bands)}
        {if $type == "band"}
          <strong>+</strong>
        {/if}
        {section name="b" loop=$events[e].bands}
          {$events[e].bands[b].name}{if $events[e].bands[b].country} ({$events[e].bands[b].country}){/if}
          {if !$smarty.section.b.last} &bull; {/if}
        {/section}
        <br />
      {/if}
                
      {if $events[e].venue.name}
        <span class="at">@</span>{$events[e].venue.name}
      {/if}
    </div>
  {/section}
{/if}
