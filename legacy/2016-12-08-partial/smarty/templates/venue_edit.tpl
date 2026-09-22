{head title="Redig&eacute;r spillested" scripts="edit" at="venue"}

<form action="save.php" method="post" id="editform">
  {editbar type="venue" id=$row.id action="edit"}
  {if $row.id }
    <h1>Redig&eacute;r spillested</h1>
  {else}
    <h1>Opret spillested</h1>
  {/if}
  <div class="content">
    {notice class="info"}
      Alle oplysninger undtagen spillestedets navn er valgfri. Indtast hvad du ved...
    {/notice}
    {notice class="error"}
      { $error }
    {/notice}
  
    <input type="hidden" name="type" value="venue" />
    <input type="hidden" name="id" value="{ $row.id }" />
    {item title="Navn"}
      <input type="text" size="24" name="name" value="{ $row.name }"/>
    {/item}
    {item title="Adresse"}
      <table>
        <tr>
          <td><label for="address_street">Vej og nummer</label></td>
          <td><input type="text" size="28" id="address_street" name="address_street" value="{ $row.address_street }" /></td>
        </tr>
        <tr>
          <td><label for="address_postalcode">Postnummer</label>/<label for="address_city">by</label></td>
          <td>
            <input type="text" size="4" id="address_postalcode" name="address_postalcode" value="{ $row.address_postalcode }" />
            <input type="text" size="20" id="address_city" name="address_city" value="{ $row.address_city }" />
          </td>
        </tr>
        <tr>
          <td><label for="address_country">Evt. land</label></td>
          <td><input type="text" size="28" id="address_country" name="address_country" value="{ $row.address_country }" /></td>
        </tr>
      </table>
    {/item}
    {item title="Priser"}
      <table>
        <tr>
          <td><label for="price_entry">Typisk entr&eacute;</label></td>
          <td><input type="text" size="3" class="amount" id="price_entry" name="price_entry" value="{ $row.price_entry }" /> kr.</td>
        </tr>
        <tr>
          <td><label for="price_draught">Fad&oslash;l</label></td>
          <td><input type="text" size="3" class="amount" id="price_draught" name="price_draught" value="{ $row.price_draught }" /> kr.</td>
        </tr>
        <tr>
          <td><label for="price_bottle">Flaske&oslash;l</label></td>
          <td><input type="text" size="3" class="amount" id="price_bottle" name="price_bottle" value="{ $row.price_bottle }" /> kr.</td>
        </tr>
        <tr>
          <td><label for="price_shot">Shot</label></td>
          <td><input type="text" size="3" class="amount" id="price_shot" name="price_shot" value="{ $row.price_shot }" /> kr.</td>
        </tr>
        <tr>
          <td><label for="price_drink">Typisk drink</label></td>
          <td><input type="text" size="3" class="amount" id="price_drink" name="price_drink" value="{ $row.price_shot }" /> kr.</td>
        </tr>
      </table>
    {/item}
    {item title="Garderobe"}
      {radios name="wardrobe" labels="Ja, Nej, Ikke angivet" values="1, 0, " checked=$row.wardrobe}
      <label>Evt. pris: <input type="text" class="amount" size="3" name="wardrobe_price" value="{ $row.wardrobe_price }" /> kr.</label><br />
    {/item}
    {item title="Musikken starter"}
      <input type="text" size="25" name="music_starts_at" value="{ $row.music_starts_at }" />
    {/item}
    
    {urlchooser row=$row}

    {item title="Beskrivelse" style="large"}
      {editor text=$row.text }
    {/item}
    {clear}
  </div>
  {editbar type="venue" id=$row.id action="edit"}
</form>

{foot}