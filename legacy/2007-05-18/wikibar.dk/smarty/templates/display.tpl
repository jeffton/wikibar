{head title=$row.name at=$type}

{editbar type=$type id=$row.id}
<h1>{ $row.name } ({typename type=$type})</h1>
<div class="content">
  {if $info}
    <div class="info">
      {if $address}
        <h2>Adresse</h2>
        { $row.address_street }<br />
        { $row.address_postalcode } { $row.address_city } { $row.address_country }
      {/if}
      {if $row.music_starts_at neq null}
        <h2>Musikken starter</h2>
        { $row.music_starts_at }
      {/if}
      {if $prices}
        <h2>Priser</h2>
        <table>
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
      {/if}{* end prices *}
  
      {if $row.wardrobe neq null}
        <h2>Garderobe</h2>
        {if $row.wardrobe}
          {if $row.wardrobe_price neq null}
            Ja, { $row.wardrobe_price } kr.
          {else}
            Ja
          {/if}
        {else}
          Nej
        {/if}
      {/if}
      {if $urls}
        {if $row.url_website neq null}
           <h2>Website</h2>
           <a href="http://{ $row.url_website }">{ $row.url_website }</a>
        {/if}
        {if $row.url_myspace neq null}
          <h2>MySpace</h2>
          <a href="http://myspace.com/{ $row.url_myspace }">myspace.com/{ $row.url_myspace }</a>
        {/if}
      {/if}
    </div>
  {/if}{* end info *}

  {$row.text}

{*  {calendar type=$type id=$row.id} *}

</div>
{foot}