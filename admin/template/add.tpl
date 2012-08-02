{combine_css path=$GVIDEO_PATH|@cat:"admin/template/style.css"}

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
        <select name="category" size="1">
          {html_options options=$category_parent_options selected=$POST.category}
        </select>
      </label>
    </li>
    <li>
      <label>
        <span class="property">{'Video URL'|@translate}</span>
        <input type="text" name="url" value="{$POST.url}" size="70">
      </label>
    </li>
    <li>
      <span class="property">{'Name'|@translate}</span>
      <label><input type="radio" name="name_server" value="true" {if $POST.name_server != 'false'}checked="checked"{/if} data-toggle="name"> {'From the video'|@translate}</label>
      <label><input type="radio" name="name_server" value="false" {if $POST.name_server == 'false'}checked="checked"{/if} data-toggle="name"> {'Change'|@translate}</label>
    </li>
    <li {if $POST.name_server != 'false'}style="display:none;"{/if} id="name">
      <span class="property">&nbsp;</span>
      <input type="text" name="name" value="{$POST.name}" size="70">
    </li>
    <li>
      <span class="property">{'Author'|@translate}</span>
      <label><input type="radio" name="author_server" value="true" {if $POST.author_server != 'false'}checked="checked"{/if} data-toggle="author"> {'From the video'|@translate}</label>
      <label><input type="radio" name="author_server" value="false" {if $POST.author_server == 'false'}checked="checked"{/if} data-toggle="author"> {'Change'|@translate}</label>
    </li>
    <li {if $POST.author_server != 'false'}style="display:none;"{/if} id="author">
      <span class="property">&nbsp;</span>
      <input type="text" name="author" value="{$POST.author}" size="20">
    </li>
    <li>
      <span class="property">{'Description'|@translate}</span>
      <label><input type="radio" name="description_server" value="true" {if $POST.description_server != 'false'}checked="checked"{/if} data-toggle="description"> {'From the video'|@translate}</label>
      <label><input type="radio" name="description_server" value="false" {if $POST.description_server == 'false'}checked="checked"{/if} data-toggle="description"> {'Change'|@translate}</label>
    </li>
    <li {if $POST.description_server != 'false'}style="display:none;"{/if} id="description">
      <span class="property">&nbsp;</span>
      <textarea name="description" rows="5" cols="50">{$POST.description}</textarea>
    </li>
    <li>
      <span class="property">{'Thumbnail'|@translate}</span>
      <label><input type="radio" name="thumbnail_server" value="true" checked="checked" data-toggle="thumbnail_src"> {'From the video'|@translate}</label>
      <label><input type="radio" name="thumbnail_server" value="false" data-toggle="thumbnail_src"> {'Change'|@translate}</label>
    </li>
    <li id="thumbnail_src" style="display:none;">
      <span class="property">&nbsp;</span>
      <input type="file" name="thumbnail_src"><br>
      <span class="property">&nbsp;</span>
      {'Maximum file size: %sB.'|@translate|@sprintf:$upload_max_filesize_shorthand} {'Allowed file types: %s.'|@translate|@sprintf:'jpg, png, gif'}
      <input type="hidden" name="MAX_FILE_SIZE" value="{$upload_max_filesize}">
    </li>
    <li>
      <span class="property">&nbsp;</span>
      <label><input type="checkbox" name="add_film_frame" value="true"> {'Add film effect'|@translate} </label>
      <a class="showInfo" title="<img src='{$GVIDEO_PATH}admin/template/example-frame.jpg'>">i</a>
    </li>
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
  </ul>  
</fieldset>


<p style="text-align:left;"><input type="submit" name="add_video" value="{'Add'|@translate}"></p>

<fieldset>
  <legend>{'Supported services'|@translate}</legend>
  
  <ul>
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
    <li>
      <img class="icon" src="{$GVIDEO_PATH}admin/template/icons/videobb.png">
      <a href="http://www.videobb.com" target="_blank">videobb</a>
    </li>
  </ul>
</fieldset>

</form>