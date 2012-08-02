{combine_css path=$GVIDEO_PATH|@cat:"admin/template/style.css"}

{footer_script}{literal}
jQuery('label.color input').change(function() {
  $('label.color').removeClass('active');
  $('label.color input:checked').parent('label').addClass('active');
});
{/literal}{/footer_script}

<div class="titrePage">
	<h2>Embedded Videos</h2>
</div>

<form method="post" action="" class="properties">
<fieldset>
  <legend>{'Common configuration'|@translate}</legend>
  
  <ul>
    <li>
      <label>
        <span class="property">{'Video size'|@translate}</span>
        <input type="text" name="width" value="{$gvideo.width}" size="4"> &times;
        <input type="text" name="height" value="{$gvideo.height}" size="4"> px
      </label>
    </li>
    <li>
      <label>
        <span class="property">{'Autoplay'|@translate}</span>
        <label><input type="radio" name="autoplay" value="0" {if not $gvideo.autoplay}checked="checked"{/if}> {'No'|@translate}</label>
        <label><input type="radio" name="autoplay" value="1" {if $gvideo.autoplay}checked="checked"{/if}> {'Yes'|@translate}</label>
      </label>
    </li>
  </ul>
</fieldset>

<div class="left"><fieldset>
  <legend><img class="icon" src="{$GVIDEO_PATH}admin/template/icons/vimeo.png"> {'Vimeo'|@translate}</legend>
  
  <ul>
    <li>
      <span class="property">{'Color'|@translate}</span>
    {foreach from=$vimeo_colors item=color}
      <label class="color {$themeconf.name} {if $gvideo.vimeo.color == $color}active{/if}" style="background:#{$color};"><input type="radio" name="vimeo[color]" value="{$color}" {if $gvideo.vimeo.color == $color}checked="checked"{/if}></label>
    {/foreach}
    </li>
    <li>
      <label>
        <span class="property">{'Display'|@translate}</span>
        <label><input type="checkbox" name="vimeo[title]" value="1" {if $gvideo.vimeo.title}checked="checked"{/if}> {'Title'|@translate}</label>
        <label><input type="checkbox" name="vimeo[portrait]" value="1" {if $gvideo.vimeo.portrait}checked="checked"{/if}> {'Author portrait'|@translate}</label>
        <label><input type="checkbox" name="vimeo[byline]" value="1" {if $gvideo.vimeo.byline}checked="checked"{/if}> {'Author name'|@translate}</label>
      </label>
    </li>
  </ul>
</fieldset></div>

<div class="right"><fieldset>
  <legend><img class="icon" src="{$GVIDEO_PATH}admin/template/icons/dailymotion.png"> {'Dailymotion'|@translate}</legend>
  
  <ul>
    <li>
      <span class="property">{'Color'|@translate}</span>
    {foreach from=$dailymotion_colors item=color}
      <label class="color {$themeconf.name} {if $gvideo.dailymotion.color == $color}active{/if}" style="background:#{$color};"><input type="radio" name="dailymotion[color]" value="{$color}" {if $gvideo.dailymotion.color == $color}checked="checked"{/if}></label>
    {/foreach}
    </li>
    <li>
      <label>
        <span class="property">{'Display'|@translate}</span>
        <label><input type="checkbox" name="dailymotion[logo]" value="1" {if $gvideo.dailymotion.logo}checked="checked"{/if}> {'Logo'|@translate}</label>
        <label><input type="checkbox" name="dailymotion[title]" value="1" {if $gvideo.dailymotion.title}checked="checked"{/if}> {'Title'|@translate}</label>
      </label>
    </li>
  </ul>
</fieldset></div>

<div style="clear:right;"></div>

<p style="text-align:left;"><input type="submit" name="save_config" value="{'Save Settings'|@translate}"></p>

</form>