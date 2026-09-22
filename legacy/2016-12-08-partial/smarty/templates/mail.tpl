{head title=$title}

{if $error}
  <h1>Noget gik galt...</h1>
  <div class="content">
    Tjek at du har fået hele linket med fra mailen og prøv igen.
  </div>
{else}
  {if $action eq "confirm"} 
    <h1>Tak!</h1>
    <div class="content">
      <p>Din mail-adresse er nu bekræftet.</p>
      <ul>
        <li><a href="{url type='event'}">Forsiden</a></li>
        <li><a href="{url type='user' id=$userId}">Din side</a></li>
      </ul>
    </div>  
  {/if}
{/if}

{foot}