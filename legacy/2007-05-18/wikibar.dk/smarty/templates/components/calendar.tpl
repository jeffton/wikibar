{if count($events)}
  <div class="calendar">
    {section name="e" loop=$events}
      {if !$events[e].samedate}
        <h2>
          {$events[e].date}
          {if $events[e].enddate}
            <span class="item"><small>til {$events[e].enddate}</small></span>
          {/if}
        </h2>
      {/if}
      <div class="event" style="background-image: url('layout/event-{$events[e].eventtype}.png')">
        <div class="eventcontent">
        
          <div class="minibar">
            <a class="button" href="{url type='event' row=$events[e] action='edit'}">Redig&eacute;r event</a>
            {if !$events[e].upforit}
              <a class="button" href="guest.php?action=add&event={$events[e].id}">Jeg er p&aring;</a>
            {else}
              <a class="button" href="guest.php?action=remove&event={$events[e].id}">Fjern mig</a>
            {/if}
          </div>
          
          <div class="eventdetails">          
  
            {if $events[e].name}
              <h3>
                {$events[e].name}
              </h3>
            {/if}
            {if count($events[e].bands)}
              <div class="bands">
                {section name="b" loop=$events[e].bands}
                  {link type="band" row=$events[e].bands[b]}{if !$smarty.section.b.last} &bull; {/if}
                {/section}
              </div>
            {/if}
            
            {$events[e].time}
            
            {if $events[e].venue.id}
              <span class="at">@</span>{link type="venue" row=$events[e].venue}
            {/if}
            
            {if $events[e].price neq null}
              {if $events[e].venue.id or $events[e].time} &ndash; {/if}
              {$events[e].price} kr.
            {/if}
            
            {if count($events[e].guests)}
              <div class="guests">
                {section name="u" loop=$events[e].guests}
                  {link type="user" row=$events[e].guests[u]}{if !$smarty.section.u.last}, {else} er p&aring;<br />{/if}
                {/section}
              </div>
            {/if}
            
          </div>
  
          {if $events[e].text or $events[e].url_website or $events[e].url_myspace}
            <div class="eventnotes">
              {$events[e].text}
              {if $events[e].url_website or $events[e].url_myspace}
                <div class="links">
                  {if $events[e].url_website}
                    <a href="http://{$events[e].url_website}">{$events[e].url_website}</a>
                  {/if}
                  {if $events[e].url_myspace}
                    {if $events[e].url_website}
                      &bull;
                    {/if}
                    <a href="http://myspace.com/{$events[e].url_myspace}">myspace.com/{$events[e].url_myspace}</a>
                  {/if}
                </div>
              {/if}
            </div>
          {/if}
          
          {clear}
          
        </div>
      </div>
    {/section}

  </div>
{else}
  <em>Kalenderen er tom</em>
{/if}