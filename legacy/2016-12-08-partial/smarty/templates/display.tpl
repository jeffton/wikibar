{head title=$row.name at=$type scripts=$scripts onload=$onload onunload=$onunload}

{editbar type=$type id=$row.id}
<h1>{ $row.name } ({typename type=$type}{if $type eq 'band' and $row.country}, {$row.country}{/if})</h1>
<div class="content">

  {div class="description"}
    {$row.text}
  {/div}
  
  {if $address and $type eq 'venue'}
    {item title="Adresse"}
      {div}{ $row.address_street }{/div}
      {div}{ $row.address_postalcode } { $row.address_city } { $row.address_country }{/div}
      {if !$row.address_country}
        <a class="button" href="http://findvej.dk/{$row.address_street},{$row.address_postalcode} {$row.address_city}?text={$row.name}">Kort</a>
        <a class="button" href="http://www.rejseplanen.dk/bin/query.exe/mn?ZADR=1&Z={$row.address_street}, {$row.address_postalcode} {$row.address_city}">Rejseplan
        <img src="layout/rejseplanen.png" alt="Rejseplanen.dk" style="vertical-align:-2px" width="21" height="14" /></a>
      {/if}
    {/item}
  {/if}
  {item title="Musikken starter"}
    { $row.music_starts_at }
  {/item}
  {if $prices}
    {item title="Priser"}
      <table class="pricelist">
        {if $row.price_entry neq null}
          <tr>
            <th>Typisk entr&eacute;</th>
            <td class="amount">{ $row.price_entry } kr.</td>
          </tr>
        {/if}
        {if $row.price_draught neq null}
          <tr>
            <th>Fad&oslash;l</th>
            <td class="amount">{ $row.price_draught } kr.</td>
          </tr>
        {/if}
        {if $row.price_bottle neq null}
          <tr>
            <th>Flaske&oslash;l</th>
            <td class="amount">{ $row.price_bottle } kr.</td>
          </tr>
        {/if}
        {if $row.price_shot neq null}
          <tr>
            <th>Shot</th>
            <td class="amount">{ $row.price_shot } kr.</td>
          </tr>
        {/if}
        {if $row.price_drink neq null}
          <tr>
            <th>Typisk drink</th>
            <td class="amount">{ $row.price_drink } kr.</td>
          </tr>
        {/if}
      </table>
    {/item}
  {/if}{* end prices *}

  {if $row.wardrobe neq null}
    {item title="Garderobe"}
      {if $row.wardrobe}
        {if $row.wardrobe_price neq null}
          Ja, { $row.wardrobe_price } kr.
        {else}
          Ja
        {/if}
      {else}
        Nej
      {/if}
    {/item}
  {/if}
  
  {if $urls}
  
    {item}
      {if $row.url_website neq null}
        <h2>Website</h2>
        <a href="http://{ $row.url_website }">{ $row.url_website }</a>
      {/if}
      {if $row.url_myspace neq null}
        <h2>MySpace</h2>
        <a href="http://myspace.com/{ $row.url_myspace }">myspace.com/{ $row.url_myspace }</a>
      {/if}
      {if $row.url_facebook neq null}
        <h2>Facebook</h2>
        <a href="http://facebook.com/{ $row.url_facebook }">facebook.com/{ $row.url_facebook }</a>
      {/if}
    {/item}
  {/if}

  {clear}
    
  {column}
    {item style="large" title="Tidligere events"}
      {calendar type=$type id=$row.id page=-1 template="small" perpage=6}
    {/item}
  {/column}
  {column side="right"}
    {item style="large" title="Kommende events"}
      {calendar type=$type id=$row.id page=1 template="small" perpage=6}
    {/item}
  {/column}
      
  {clear}
</div>
{foot}
