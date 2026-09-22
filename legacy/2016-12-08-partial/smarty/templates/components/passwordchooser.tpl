{item title="Password" helptopic=$passwordchooser_helptopic}
  {if $passwordchooser_edit}
    Hvis du vil skifte password:
  {/if}
  <table>
    <tr>
      <td><label for="password">Indtast</label></td>
      <td>
        <input type="password" id="password" size="20" name="password" value="{ $passwordchooser_password }"/>
      </td>
    </tr>
    <tr>
      <td><label for="passwordrepeat">Gentag</label></td>
      <td>
        <input type="password" id="passwordrepeat" size="20" name="passwordrepeat" value="{ $passwordchooser_passwordrepeat }"/>
      </td>
    </tr>
  </table>
{/item}