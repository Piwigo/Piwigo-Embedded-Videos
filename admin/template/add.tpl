{combine_css path=$GVIDEO_PATH|cat:'admin/template/style.css'}

{combine_script id='common' load='footer' path='admin/themes/default/js/common.js'}

{include file='include/colorbox.inc.tpl'}
{include file='include/add_album.inc.tpl'}

{combine_script id='LocalStorageCache' load='footer' path='admin/themes/default/js/LocalStorageCache.js'}

{combine_script id='jquery.selectize' load='footer' path='themes/default/js/plugins/selectize.min.js'}
{combine_css id='jquery.selectize' path="themes/default/js/plugins/selectize.{$themeconf.colorscheme}.css"}


{footer_script}
{* <!-- CATEGORIES --> *}
var categoriesCache = new CategoriesCache({
  serverKey: '{$CACHE_KEYS.categories}',
  serverId: '{$CACHE_KEYS._hash}',
  rootUrl: '{$ROOT_URL}'
});

categoriesCache.selectize(jQuery('[data-selectize=categories]'));

jQuery('[data-add-album]').pwgAddAlbum();

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
jQuery(".showProvidersInfo").click(function() {
  $(".providersInfo").toggle();
});
jQuery(".radio input").on('change', function() {
  if (jQuery(this).is(':checked')) {
    var mode = jQuery(this).val();
    
    jQuery('.'+mode+'-hide').hide();
    jQuery('.'+mode+'-show').show();
  }
}).trigger('change');
{/footer_script}


<div class="titrePage">
	<h2>Embedded Videos</h2>
</div>

<form method="post" action="" class="properties" enctype="multipart/form-data">
  <div class="radio">
    <input type="radio" name="mode" value="provider" id="mode_provider" {if $POST.mode!="embed"}checked{/if}><label for="mode_provider">{'Add video from hosting platform'|translate}</label><!--
    --><input type="radio" name="mode" value="embed" id="mode_embed" {if $POST.mode=="embed"}checked{/if}><label for="mode_embed">{'Add video from embed code'|translate}</label>
  </div>
  
  <div class="warnings custom-warn embed-show provider-hide">
    <ul><li>{'Do not use this form for videos provided by Youtube, Dailymotion, Vimeo, Wat or Wideo.'|translate}</li></ul>
  </div>

<fieldset>
  <legend>{'Properties'|translate}</legend>
  
  <ul>
    <li>
      <span class="property">{'Album'|translate}</span>
      <select data-selectize="categories" data-value="{$POST.category}" data-default="first" name="category"></select>
      {'... or '|translate} <a href="#" data-add-album="category" title="{'create a new album'|@translate}">{'create a new album'|translate}</a>
    </li>
    <li class="embed-show provider-hide">
      <span class="property">{'Title'|translate}</span>
      <input type="text" name="title" value="{$POST.title}" style="width:400px;">
    </li>
    <li>
      <span class="property">{'Video URL'|translate} <small class="embed-show provider-hide">({'optional'|translate})</small></span>
      <input type="text" name="url" value="{$POST.url}" style="width:400px;">
    </li>
    <li class="embed-show provider-hide">
      <span class="property">{'Embed code'|translate}</span>
      <textarea name="embed_code" style="width:600px;height:160px;">{$POST.embed_code}</textarea>
    </li>
    <li class="embed-show {if $gd_available}provider-show{else}provider-hide{/if}">
      <span class="property">{'Thumbnail'|translate} <small class="embed-show provider-hide">({'optional'|translate})</small></span>
      <input type="file" size="20" name="thumbnail_file" class="embed-show provider-hide">
    {if $gd_available}
      <label><input type="checkbox" name="add_film_frame" value="true" {if $POST.add_film_frame}checked="checked"{/if}> {'Add film effect'|translate} </label>
      <a class="icon-info-circled-1 showInfo" title="<img src='{$GVIDEO_PATH}admin/template/example-frame.jpg'>"></a>
    {/if}
    </li>
  </ul>  
</fieldset>

<fieldset class="embed-hide provider-show">
  <legend>{'Configuration'|translate}</legend>
  
  <ul>
    <li>
      <span class="property">{'Video size'|translate}</span>
      <label><input type="radio" name="size_common" value="true" {if $POST.size_common != 'false'}checked="checked"{/if} data-toggle="size"> {'Use common setting'|translate}</label>
      <label><input type="radio" name="size_common" value="false" {if $POST.size_common == 'false'}checked="checked"{/if} data-toggle="size"> {'Change'|translate}</label>
    </li>
    <li {if $POST.size_common != 'false'}style="display:none;"{/if} id="size">
      <span class="property">&nbsp;</span>
      <input type="text" name="width" value="{$POST.width}" size="4"> &times;
      <input type="text" name="height" value="{$POST.height}" size="4"> px
    </li>
    <li>
      <span class="property">{'Autoplay'|translate}</span>
      <label><input type="radio" name="autoplay_common" value="true" {if $POST.autoplay_common != 'false'}checked="checked"{/if} data-toggle="autoplay"> {'Use common setting'|translate}</label>
      <label><input type="radio" name="autoplay_common" value="false" {if $POST.autoplay_common == 'false'}checked="checked"{/if} data-toggle="autoplay"> {'Change'|translate}</label>
    </li>
    <li {if $POST.autoplay_common != 'false'}style="display:none;"{/if} id="autoplay">
      <span class="property">&nbsp;</span>
      <label><input type="radio" name="autoplay" value="0" {if $POST.autoplay == '0'}checked="checked"{/if}> {'No'|translate}</label>
      <label><input type="radio" name="autoplay" value="1" {if $POST.autoplay == '1'}checked="checked"{/if}> {'Yes'|translate}</label>
    </li>
    <li>
      <span class="property">{'Get video description'|translate}</span>
      <label><input type="radio" name="sync_description" value="1" {if $gvideo.sync_description}checked="checked"{/if}> {'Yes'|translate}</label>
      <label><input type="radio" name="sync_description" value="0" {if not $gvideo.sync_description}checked="checked"{/if}> {'No'|translate}</label>
    </li>
    <li>
      <span class="property">{'Get video tags'|translate}</span>
      <label><input type="radio" name="sync_tags" value="1" {if $gvideo.sync_tags}checked="checked"{/if}> {'Yes'|translate}</label>
      <label><input type="radio" name="sync_tags" value="0" {if not $gvideo.sync_tags}checked="checked"{/if}> {'No'|translate}</label>
    </li>
  </ul>  
</fieldset>


<p style="text-align:left;">
  <input type="submit" name="add_video" value="{'Add'|translate}">
</p>

<fieldset style="margin-top:40px;" class="embed-hide provider-show">
  <legend>{'Supported services'|translate}</legend>
  
  <ul class="services">
    <li>
      <img class="icon" src="{$GVIDEO_PATH}admin/template/icons/youtube.png">
      <a href="http://www.youtube.com" target="_blank">YouTube</a>
      <span class="providersInfo">{'Videos can be unlisted but not private.'|translate}<br></span>
    </li>
    <li>
      <img class="icon" src="{$GVIDEO_PATH}admin/template/icons/vimeo.png">
      <a href="http://www.vimeo.com" target="_blank">Vimeo</a>
      <span class="providersInfo">{'Videos can be unlisted and private if the gallery website is within the authorized domains (PRO).'|translate}<br></span>
    </li>
    <li>
      <img class="icon" src="{$GVIDEO_PATH}admin/template/icons/dailymotion.png">
      <a href="http://www.dailymotion.com" target="_blank">Dailymotion</a>
      <span class="providersInfo">{'Videos can be private if you use the private permalink.'|translate}<br></span>
    </li>
    <li>
      <img class="icon" src="{$GVIDEO_PATH}admin/template/icons/wat.png">
      <a href="http://www.wat.tv" target="_blank">Wat</a>
      <span class="providersInfo" style="font-style:italic;">{'No privacy option.'|translate}<br></span>
    </li>
    <li>
      <img class="icon" src="{$GVIDEO_PATH}admin/template/icons/wideo.png">
      <a href="http://www.wideo.fr" target="_blank">Wideo</a>
      <span class="providersInfo" style="font-style:italic;">{'No privacy option.'|translate}<br></span>
    </li>
    <li>
      <a class="showProvidersInfo">{'Show privacy details'|translate}</a>
    </li>
  </ul>
</fieldset>

</form>