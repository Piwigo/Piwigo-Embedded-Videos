{combine_css path=$GVIDEO_PATH|@cat:"admin/template/style.css"}
{include file='include/colorbox.inc.tpl'}
{include file='include/add_album.inc.tpl'}

{footer_script}{literal}
jQuery("input[data-toggle]").change(function() {
  $('#'+ $(this).data('toggle')).toggle();
});
jQuery(".showInfo").tipTip({
  delay: 0,
  fadeIn: 200,
  fadeOut: 200,
  maxWidth: '300px',
  defaultPosition: 'right'
});
{/literal}{/footer_script}

<div class="titrePage">
	<h2>Embedded Videos</h2>
</div>

<form method="post" action="" class="properties">
<fieldset>
  <legend>{'Properties'|@translate}</legend>
  
  <ul>
    <li>
      <label>
        <span class="property">{'Album'|@translate}</span>
        <select style="width:400px" name="category" id="albumSelect" size="1">
          {html_options options=$category_parent_options selected=$POST.category}
        </select>
      </label>
      {'... or '|@translate}<a href="#" class="addAlbumOpen" title="{'create a new album'|@translate}">{'create a new album'|@translate}</a>
    </li>
    <li>
      <label>
        <span class="property">{'Video URL'|@translate}</span>
        <input type="text" name="url" value="{$POST.url}" style="width:400px;">
      </label>
    </li>
  {if $gd_available}
    <li>
      <span class="property">{'Thumbnail'|@translate}</span>
      <label><input type="checkbox" name="add_film_frame" value="true"> {'Add film effect'|@translate} </label>
      <a class="showInfo" title="<img src='{$GVIDEO_PATH}admin/template/example-frame.jpg'>">i</a>
    </li>
  {/if}
  </ul>  
</fieldset>

<fieldset>
  <legend>{'Configuration'|@translate}</legend>
  
  <ul>
    <li>
      <span class="property">{'Video size'|@translate}</span>
      <label><input type="radio" name="size_common" value="true" {if $POST.size_common != 'false'}checked="checked"{/if} data-toggle="size"> {'Use common setting'|@translate}</label>
      <label><input type="radio" name="size_common" value="false" {if $POST.size_common == 'false'}checked="checked"{/if} data-toggle="size"> {'Change'|@translate}</label>
    </li>
    <li {if $POST.size_common != 'false'}style="display:none;"{/if} id="size">
      <span class="property">&nbsp;</span>
      <input type="text" name="width" value="{$POST.width}" size="4"> &times;
      <input type="text" name="height" value="{$POST.height}" size="4"> px
    </li>
    <li>
      <span class="property">{'Autoplay'|@translate}</span>
      <label><input type="radio" name="autoplay_common" value="true" {if $POST.autoplay_common != 'false'}checked="checked"{/if} data-toggle="autoplay"> {'Use common setting'|@translate}</label>
      <label><input type="radio" name="autoplay_common" value="false" {if $POST.autoplay_common == 'false'}checked="checked"{/if} data-toggle="autoplay"> {'Change'|@translate}</label>
    </li>
    <li {if $POST.autoplay_common != 'false'}style="display:none;"{/if} id="autoplay">
      <span class="property">&nbsp;</span>
      <label><input type="radio" name="autoplay" value="0" {if $POST.autoplay == '0'}checked="checked"{/if}> {'No'|@translate}</label>
      <label><input type="radio" name="autoplay" value="1" {if $POST.autoplay == '1'}checked="checked"{/if}> {'Yes'|@translate}</label>
    </li>
    <li>
      <span class="property">{'Get video description'|@translate}</span>
      <label><input type="radio" name="sync_description" value="1" {if $gvideo.sync_description}checked="checked"{/if}> {'Yes'|@translate}</label>
      <label><input type="radio" name="sync_description" value="0" {if not $gvideo.sync_description}checked="checked"{/if}> {'No'|@translate}</label>
    </li>
    <li>
      <span class="property">{'Get video tags'|@translate}</span>
      <label><input type="radio" name="sync_tags" value="1" {if $gvideo.sync_tags}checked="checked"{/if}> {'Yes'|@translate}</label>
      <label><input type="radio" name="sync_tags" value="0" {if not $gvideo.sync_tags}checked="checked"{/if}> {'No'|@translate}</label>
    </li>
  </ul>  
</fieldset>


<p style="text-align:left;"><input type="submit" name="add_video" value="{'Add'|@translate}"></p>

<fieldset>
  <legend>{'Supported services'|@translate}</legend>
  
  <ul class="services">
    <li>
      <img class="icon" src="{$GVIDEO_PATH}admin/template/icons/youtube.png">
      <a href="http://www.youtube.com" target="_blank">YouTube</a>
    </li>
    <li>
      <img class="icon" src="{$GVIDEO_PATH}admin/template/icons/vimeo.png">
      <a href="http://www.vimeo.com" target="_blank">Vimeo</a>
    </li>
    <li>
      <img class="icon" src="{$GVIDEO_PATH}admin/template/icons/dailymotion.png">
      <a href="http://www.dailymotion.com" target="_blank">Dailymotion</a>
    </li>
    <li>
      <img class="icon" src="{$GVIDEO_PATH}admin/template/icons/wat.png">
      <a href="http://www.wat.tv" target="_blank">Wat</a>
    </li>
    <li>
      <img class="icon" src="{$GVIDEO_PATH}admin/template/icons/wideo.png">
      <a href="http://www.wideo.fr" target="_blank">Wideo</a>
    </li>
  </ul>
</fieldset>

</form>