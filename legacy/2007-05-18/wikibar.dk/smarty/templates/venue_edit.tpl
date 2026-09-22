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
    {edititem title="Navn"}
      <input type="text" size="24" name="name" value="{ $row.name }"/>
    {/edititem}
    {edititem title="Adresse"}
      <table>
        <tr>
          <td>Vej og nummer</td>
          <td><input type="text" size="28" name="address_street" value="{ $row.address_street }" /></td>
        </tr>
        <tr>
          <td>Postnummer/by</td>
          <td>
            <input type="text" size="4" name="address_postalcode" value="{ $row.address_postalcode }" />
            <input type="text" size="20" name="address_city" value="{ $row.address_city }" />
          </td>
        </tr>
        <tr>
          <td>Evt. land</td>
          <td><input type="text" size="28" name="address_country" value="{ $row.address_country }" /></td>
        </tr>
      </table>
    {/edititem}
    {edititem title="Priser"}
      <table>
        <tr>
          <td>Typisk entr&eacute;</td>
          <td><input type="text" size="3" class="amount" name="price_entry" value="{ $row.price_entry }" /> kr.</td>
        </tr>
        <tr>
          <td>Fad&oslash;l</td>
          <td><input type="text" size="3" class="amount" name="price_draught" value="{ $row.price_draught }" /> kr.</td>
        </tr>
        <tr>
          <td>Flaske&oslash;l</td>
          <td><input type="text" size="3" class="amount" name="price_bottle" value="{ $row.price_bottle }" /> kr.</td>
        </tr>
        <tr>
          <td>Shot</td>
          <td><input type="text" size="3" class="amount" name="price_shot" value="{ $row.price_shot }" /> kr.</td>
        </tr>
        <tr>
          <td>Typisk drink</td>
          <td><input type="text" size="3" class="amount" name="price_drink" value="{ $row.price_shot }" /> kr.</td>
        </tr>
      </table>
    {/edititem}
    {edititem title="Garderobe"}
      {radios name="wardrobe" labels="Ja, Nej, Ikke angivet" values="1, 0, " checked=$row.wardrobe}
      Evt. pris: <input type="text" class="amount" size="3" name="wardrobe_price" value="{ $row.wardrobe_price }" /> kr.<br />
    {/edititem}
    {edititem title="Musikken starter"}
      <input type="text" size="25" name="music_starts_at" value="{ $row.music_starts_at }" />
    {/edititem}
    
    {urlchooser row=$row}

    {edititem title="Beskrivelse" style="large"}
      {editor text=$row.text }
    {/edititem}
    {clear}
  </div>
  {editbar type="venue" id=$row.id action="edit"}
</form>

{foot}