<div class="main">
  <h1>Kalender, side {$page}</h1>

  {section name="e" loop=$events}
    <div class="event" style="background-image:url('eventsmall-{$events[e].eventtype}.png');">
      <div class="minibar">
        <a class="button" href="vcal.php?id={$events[e].id}">Gem</a>
      </div>
      {if $events[e].status}
        <img src="eventstatus_{$events[e].status}.png" alt="{$events[e].status}" width="86" height="40" align="right"/>
      {/if}
      <h2>
        {$events[e].date}
        {if $events[e].enddate}
          <small>til {$events[e].enddate}</small>
        {/if}
      </h2>
      {if $events[e].name}
        <h3>{$events[e].name}</h3>
      {/if}
      
      {if count($events[e].bands)}
        <div>
          {section name="b" loop=$events[e].bands}
            {$events[e].bands[b].name}{if $events[e].bands[b].country} ({$events[e].bands[b].country}){/if}
            {if !$smarty.section.b.last} - {/if}
          {/section}
        </div>
      {/if}
  
      {$events[e].time}
      {if $events[e].venue.name}
        @ {$events[e].venue.name}
      {/if}
      {if $events[e].price}
        {if $events[e].time or $events[e].venue.name}
        -
        {/if}
        {$events[e].price} kr.
      {/if}
    </div>
  {sectionelse}
    <em>Kalenderen er tom</em>
  {/section}
  
</div>
<div class="bottom">
  {if $previousevents}
    {if $page > 2}
      <a class="button" href="index.php">&lt;&lt; I dag</a>
    {/if}
    <a class="button" href="index.php?page={$page-1}">&lt; Tidligere events ({$previousevents})</a>
  {/if}
  {if $nextevents}
    <a class="button" href="index.php?page={$page+1}">Senere events ({$nextevents}) &gt;</a>
  {/if}
</div>

