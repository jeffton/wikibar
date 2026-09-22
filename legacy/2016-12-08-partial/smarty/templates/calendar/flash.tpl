{strip}
  {section name="e" loop=$events}
    {if !$smarty.section.e.first}
    <br />
    {/if}
<a href="{url type='event' id=$events[e].id}">
    <b>
       {$events[e].date}
        {if $events[e].enddate}
          &nbsp;til {$events[e].enddate}
        {/if}
      </b>
      <br />
      {if $events[e].name}
        <i>{$events[e].name}</i>
        <br />
      {/if}
      
      {if count($events[e].bands)}
          {section name="b" loop=$events[e].bands}
            {$events[e].bands[b].name}
            {if !$smarty.section.b.last} - {/if}
          {/section}
        <br />
      {/if}
  
      {$events[e].time}
      {if $events[e].venue.name}
        &nbsp;@ {$events[e].venue.name}
      {/if}
      {if $events[e].price}
        {if $events[e].time or $events[e].venue.name}
        &nbsp;-&nbsp;
        {/if}
        {$events[e].price} kr.
      {/if}

      {if $events[e].time or $events[e].price or $events[e].venue.name}
        <br />
      {/if}
</a>
  {sectionelse}
    <i>Kalenderen er tom</i>
  {/section}
{/strip}