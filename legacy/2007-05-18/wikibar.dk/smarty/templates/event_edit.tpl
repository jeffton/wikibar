{head title="Redig&eacute;r event" at="calendar" scripts="ajax, event, help, tabs" onload="init()"}

<form action="save.php" method="post" id="editform">
  <input type="hidden" name="type" value="event" />
  <input type="hidden" name="id" value="{ $row.id }" />
  {editbar type="event" action="edit" class="abovetabs"}

  {notice class="error"}
    {$error}
  {/notice}

  {tabs group="eventtabs" labels="Event, Detaljer" selected=1}

  {tabpane group="eventtabs" index=1 selected=1}
    {edititem style="large" title="Type"}
      <label class="eventtype">
        <img src="layout/event-concert.png" width="58" height="69" 
          alt="Koncert" /><br />
        <input type="radio" class="radio" name="eventtype" value="concert" 
        {if !$row.eventtype or $row.eventtype eq 'concert'}checked="checked"{/if} />
      </label>
      <label class="eventtype">
        <img src="layout/event-party.png" width="58" height="69" 
          alt="Fest" /><br />
        <input type="radio" class="radio" name="eventtype" value="party" 
        {if $row.eventtype eq 'party'}checked="checked"{/if} />
      </label>
      <label class="eventtype">
        <img src="layout/event-releaseparty.png" width="64" height="69" 
          alt="Releaseparty" /><br />
        <input type="radio" class="radio" name="eventtype" value="releaseparty"
        {if $row.eventtype eq 'releaseparty'}checked="checked"{/if} />
      </label>
      <label class="eventtype">
        <img src="layout/event-release.png" width="59" height="69" 
          alt="Udgivelse" /><br />
        <input type="radio" class="radio" name="eventtype" value="release"
        {if $row.eventtype eq 'release'}checked="checked"{/if} />
      </label>
      <label class="eventtype">
        <img src="layout/event-festival.png" width="65" height="69" 
          alt="Festival" /><br />
        <input type="radio" class="radio" name="eventtype" value="festival"
        {if $row.eventtype eq 'festival'}checked="checked"{/if} />
      </label>
      {clear}
    {/edititem}
    {clear}
      
    {edititem title="Bands" style="large" helptopic="event_bands"}
      {chooser mode="multiple" values=$row.bands type="band" name="bands"}
    {/edititem}  
    
    {edititem title="Spillested" style="large" helptopic="event_venue"}
      {chooser mode="single" value=$row.venue type="venue" name="venue"}
    {/edititem}
    
    {edititem title="Dato" helptopic="event_date"}
      {datechooser name="date" value=$row.date text=$row.datetext}
    {/edititem}
    
    {edititem title="Tid" helptopic="event_time"}
      <input type="text" name="time" size="5" value="{ $row.time }" />
    {/edititem}
  
    {edititem title="Entr&eacute;"}
      <input type="text" size="4" name="price" value="{$row.price}" /> kr.
    {/edititem}
    
    {edititem title="Skal du med?" helptopic="event_guest"}
      {radios name="upforit" labels="Ja!, Nej/ikke angivet" values="1, " checked=$row.upforit}
    {/edititem}
    
    {clear}
  {/tabpane}
    
  {tabpane group="eventtabs" index=2}
    
    {edititem title="Slutdato" helptopic="event_date"}
      {datechooser name="enddate" value=$row.enddate text=$row.enddatetext}
    {/edititem}
    
    {urlchooser row=$row}
  
    {edititem title="Titel" helptopic="event_name" style="large"}
      <input type="text" name="name" value="{ $row.name }" size="50" />
    {/edititem}
    
    {edititem title="Note" style="large"}
      <textarea rows="4" cols="48" name="text">{$row.text}</textarea>
    {/edititem}
    
    {clear}
  {/tabpane}
    
{*  {tabpane group="eventtabs" index=3}
    {notice class="info"}
      En privat event kan kun ses af dens arrang&oslash;rer og inviterede.<br />
      V&aelig;r dog opm&aelig;rksom p&aring;, at spillesteder og bands altid 
      er synlige for alle.
    {/notice}
    
    {edititem}
      <h2><label><input type="checkbox" class="checkbox" name="private" />
      G&oslash;r denne event privat</label></h2>
    {/edititem}
    
    {edititem style="large" title="Medarrang&oslash;rer"}
      {chooser type="user" mode="multiple" name="organizers"}
    {/edititem}
    
    {edititem style="large" title="Inviterede"}
      {chooser type="user" mode="multiple" name="invitees"}
    {/edititem}
  
    {clear}
  {/tabpane} *}
  {clear}
  {editbar type="event" action="edit"}
</form>
{foot}