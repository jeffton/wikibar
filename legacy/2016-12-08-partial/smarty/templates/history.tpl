{head title=$title at=$type}

{editbar type=$type action='history' id=$row.id}
<h1>{$title} ({typename type=$type})</h1>

<div class="content">
  {if count($history)}
    <h2>Senest anvendte versioner</h2>
    <table class="history">
      <tr><th>Version</th><th>Handling</th><th>Bruger</th><th>V&aelig;lg</th></tr>
      {section name="h" loop=$history}
        <tr>
          <td>{$history[h].version}</td>
          <td>{$history[h].action}</td>
          <td>{link type='user' row=$history[h].user}</td>
          <td><input type="radio" class="radio" name="version" value="{$history[h].version}"/>
          </td>
        </tr>
      {/section}
    </table>
    <a class="button" href="javascript:alert(document.getElementsByName('version')[0].value)">Vis valgt version</a>
    
    
    {* UP NEXT lav det som en form! *}
    
    
    
    {*<a class="button">Gendan version</a>*}
  {else}
    {notice class="info"}Der er ingen historik for den valgte side{/notice}
  {/if}
</div>

{editbar type=$type action='history' id=$row.id}
{foot}