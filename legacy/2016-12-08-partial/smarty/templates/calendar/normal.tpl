<h1>Kalender{if $page > 1} (side {$page}){/if}{if $page < 0} (arkiv, side {$zero-$page}){/if}</h1>
<div class="content">
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
        {if $events[e].id == $highlight}
          <a id="e"></a>
        {/if}
        <div class="event{if $events[e].id == $highlight} eventhighlight{/if}" style="background-image: url('layout/event-{$events[e].eventtype}.png')">
          <div class="eventcontent">
          
            <div class="minibar">
              <a class="button" href="{url type='event' row=$events[e] action='edit'}">Redig&eacute;r event</a>
              {if !$events[e].upforit}
                <a class="button" href="guest.php?action=add&amp;event={$events[e].id}">{if $page>0}Jeg er p&aring;{else}Jeg var p&aring;{/if}</a>
              {else}
                <a class="button" href="guest.php?action=remove&amp;event={$events[e].id}">Fjern mig</a>
              {/if}
            </div>
            
            {if $events[e].status}
              <img src="layout/eventstatus_{$events[e].status}.png" class="eventstatus" alt="{$events[e].status}" width="86" height="40" />
            {/if}
            
            <div class="eventdetails">
            
              {if $events[e].name}
                <h3>
                  {$events[e].name}
                </h3>
              {/if}
              {if count($events[e].bands)}
                <div class="eventbands">
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
                <div class="eventguests">
                  {section name="u" loop=$events[e].guests}
                    {link type="user" row=$events[e].guests[u]}
                    {if !$smarty.section.u.last} &bull; {else} {if $page>0}er{else}var{/if} p&aring;<br />{/if}
                  {/section}
                </div>
              {/if}
              
            </div>
    
            {if $events[e].text or $events[e].url_website or $events[e].url_myspace or $events[e].url_facebook}
              <div class="eventnotes">
                {$events[e].text}
                {if $events[e].url_website or $events[e].url_myspace or $events[e].url_facebook}
                  <div class="eventlinks">
                    {if $events[e].url_website}
                      <a href="http://{$events[e].url_website}">
                        <img src="layout/linkicon-website.png" width="18" height="18" alt="Website" title="Website" />
                      </a>
                    {/if}
                    {if $events[e].url_myspace}
                      <a href="http://myspace.com/{$events[e].url_myspace}">
                        <img src="layout/linkicon-myspace.png" width="17" height="19" alt="MySpace" title="MySpace" />
                      </a>
                    {/if}
                    {if $events[e].url_facebook}
                      <a href="http://facebook.com/{$events[e].url_facebook}">
                        <img src="layout/linkicon-facebook.png" width="18" height="18" alt="Facebook" title="Facebook" />
                      </a>
                    {/if}
                  </div>
                {/if}
              </div>
            {/if}
          </div>
        </div>
      {/section}
  
    </div>
    
   
    <div class="editbar">
    {strip}
      {section name="p" loop=$archivePageNumbers}
        {assign var='pageDisplay' value=$zero-$archivePageNumbers[p]}
        {assign var='pageValue' value=$archivePageNumbers[p]}
        <a class="button{if $pageValue eq $page} activebutton{/if}"
            href="{url type='calendar' id=$pageValue}">
          {if $pageValue < $page}&lt; {/if}
          Arkiv {$pageDisplay}
          {if $pageValue > $page} &gt;{/if}
        </a>
        {if !$smarty.section.p.last}&nbsp;{/if}
      {/section}   
    {/strip}
    &bull;
    
    {strip}
      {section name="p" loop=$pageNumbers}
        <a class="button{if $pageNumbers[p] eq $page} activebutton{/if}"
            href="{url type='calendar' id=$pageNumbers[p]}">
          {if $pageNumbers[p] < $page}&lt; {/if}
          Side {$pageNumbers[p]}
          {if $pageNumbers[p] > $page} &gt;{/if}
        </a>
        &nbsp;
      {/section}
    {/strip}
    </div>
    
  {else}
    <em>Kalenderen er tom</em>
  {/if}
</div>
