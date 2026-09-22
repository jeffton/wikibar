<input type="hidden" name="{$datechooser_name}" id="{$datechooser_name}value" 
    value="{ $datechooser_value }" />
<input type="text" id="{$datechooser_name}text" name="{$datechooser_name}text" 
  onkeyup="showDate('{$datechooser_name}')" 
  onchange="showDate('{$datechooser_name}')" 
  value="{ $datechooser_text }"
  autocomplete="off" />
<div id="{$datechooser_name}display" class="datedisplay"></div>
