    </div>
    <div class="foot">
      <div class="login">
        {if $user}
          <span>{$user.name}</span>
          <a class="button" href="{url type='user' row=$user}">Min side</a>
          <a class="button" href="login.php?action=logout">Log ud</a>
        {else}
          <a class="button" href="login.php">Log ind/Ny bruger</a>
        {/if}
      </div>
      <div class="links">
        <a href="static.php?page=about">om wikibar</a>
      </div>
    </div>
  </body>
</html>