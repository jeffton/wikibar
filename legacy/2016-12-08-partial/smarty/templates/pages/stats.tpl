{head title="Statistik"}

<h1>Statistik</h1>

<div class="content">

  {column}
  {if count($popbands)}
    {item title="Top 5 bands"}
      <table>
        <tr><th>Band</th><th>Set</th></tr>
        {section name="b" loop=$popbands}
          <tr>
            <td>{link type='band' row=$popbands[b]}</td>
            <td class="amount">{$popbands[b].num} gange</td>
          </tr>
        {/section}
      </table>
    {/item}
  {/if}
  
  {if count($popvenues)}
    {item title="Top 5 spillesteder"}
      <table>
        <tr><th>Spillested</th><th>Bes&oslash;gt</th></tr>
        {section name="v" loop=$popvenues}
          <tr>
            <td>{link type='venue' row=$popvenues[v]}</td>
            <td class="amount">{$popvenues[v].num} gange</td>
          </tr>
        {/section}
      </table>
    {/item}
  {/if}
  
  {if count($partyusers)}
    {item title="Top 5 brugere"}
      <table>
        <tr><th>Bruger</th><th>Med til</th></tr>
        {section name="u" loop=$partyusers}
          <tr>
            <td>{link type='user' row=$partyusers[u]}</td>
            <td class="amount">{$partyusers[u].num} events</td>
          </tr>
        {/section}
      </table>    
    {/item}
  {/if}
  
  {if count($friends)}
    {item title="Dine venner"}
      <table>
        <tr><th>Bruger</th><th>F&aelig;lles</th></tr>
        {section name="f" loop=$friends}
          <tr>
            <td>{link row=$friends[f] type='user'}</td>
            <td class="amount">{$friends[f].num} events</td>
          </tr>
        {/section}
      </table>
    {/item}
  {/if}

  
  {/column}
  
  {column side="right"}

  {if count($popevents_num)}
    {item style="large" title="St&oslash;rste events"}
      Med 
      {strip}
        {section name="num" loop=$popevents_num}
          {if $smarty.section.num.last and !$smarty.section.num.first} og {else}
          {if !$smarty.section.num.first}, {/if}{/if}
          {$popevents_num[num]}
        {/section}
      {/strip}
      bruger(e).
      {calendar events=$popevents template="small"}
    {/item}
  {/if}
    
  {item title="Antal"}
    <table class="pricelist">
      <tr>
        <th>Events</th>
        <td class="amount">{$counts.event}</td>
      </tr>
      <tr>
        <th>Bands</th>
        <td class="amount">{$counts.band}</td>
      </tr>
      <tr>
        <th>Spillesteder</th>
        <td class="amount">{$counts.venue}</td>
      </tr>
      <tr>
        <th>Brugere</th>
        <td class="amount">{$counts.user}</td>
      </tr>
    </table>
  {/item}
  
  {item title="Senest oprettet"}
    <table class="pricelist">
      <tr>
        <th>Band</th>
        <td class="amount">{link type='band' row=$latestband}</td>
      </tr>
      <tr>
        <th>Spillested</th>
        <td class="amount">{link type='venue' row=$latestvenue}</td>
      </tr>
      <tr>
        <th>Bruger</th>
        <td class="amount">{link type='user' row=$latestuser}</td>
      </tr>
    </table>
  {/item}
  
  {/column}

</div>

{foot}
