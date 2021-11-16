{combine_css path=$GVIDEO_PATH|cat:'admin/template/style.css'}

{if $GVIDEO.type != 'embed'}
{footer_script}
jQuery("input[data-toggle]").change(function() {
  $block = $('#'+ $(this).data('toggle'));
  if ($block.is(':visible')) {
    $block.css('display', 'none');
  } else {
    $block.css('display', 'block');
  }
});

var border = jQuery("input[name='url']").css('border');

jQuery("input[name='url']").keyup(function() {
  if ($(this).val() != "{$GVIDEO.url}") {
    $(this).css('border', '1px solid #c00');
    $(".video_update").show();
  }
  else {
    $(this).css('border', border);
    $(".video_update").hide();
  }
});
{/footer_script}
{/if}


<h2>{$TITLE} &#8250; {'Edit photo'|translate} {$TABSHEET_TITLE}</h2>

<fieldset>
  <legend>{'Thumbnail'|translate}</legend>
  <table>
    <tr>
      <td id="albumThumbnail">
        <img src="{$TN_SRC}" alt="{'Thumbnail'|translate}" class="Thumbnail">
      </td>
      <td id="albumLinks" style="width:400px;vertical-align:top;">
        <ul style="padding-left:15px;margin:0;">
          <li style="margin:10px 0 20px 0;"><a href="{$RESET_THUMBNAIL}">{'Reset thumbnail (download from host)'|translate}</a></li>
        {if $U_ADD_FILM_FRAME}
          <li style="margin:10px 0 20px 0;"><a href="{$U_ADD_FILM_FRAME}">{'Add film effect'|translate}</a></li>
        {/if}
        
          <li>
            <form id="photo_update" method="post" action="{$F_ACTION}" enctype="multipart/form-data">
              {'Upload a new thumbnail'|translate}<br>
              <input type="file" size="20" name="photo_update">
              <input class="submit" type="submit" value="{'Send'|translate}" name="photo_update">
            </form>
          </li>
        </ul>
      </td>
    </tr>
  </table>
</fieldset>

<form action="{$F_ACTION}" method="post" id="catModify">
  <fieldset>
    <legend>{'Properties'|translate}</legend>

    <p>
      <b>{'Video URL'|translate}</b>
      <input type="text" name="url" value="{$GVIDEO.url}" style="width:400px;">
      <span class="video_update warning" style="display:none;"> {'Changing the url will reset video description, name and thumbnail'|translate}</span>
    </p>
    
    {if $GVIDEO.type != 'embed'}
    <p class="video_update">
      <b>{'Get video description'|translate}</b>
      <label><input type="radio" name="sync_description" value="1" {if $GVIDEO.sync_description}checked="checked"{/if}> {'Yes'|translate}</label>
      <label><input type="radio" name="sync_description" value="0" {if not $GVIDEO.sync_description}checked="checked"{/if}> {'No'|translate}</label>
    </p>
    
    <p class="video_update">
      <b>{'Get video tags'|translate}</b>
      <label><input type="radio" name="sync_tags" value="1" {if $GVIDEO.sync_tags}checked="checked"{/if}> {'Yes'|translate}</label>
      <label><input type="radio" name="sync_tags" value="0" {if not $GVIDEO.sync_tags}checked="checked"{/if}> {'No'|translate}</label>
    </p>
    
    <p>
      <b>{'Video size'|translate}</b>
      <label><input type="radio" name="size_common" value="true" {if $GVIDEO.size_common == 'true'}checked="checked"{/if} data-toggle="size"> {'Use common setting'|translate}</label>
      <label><input type="radio" name="size_common" value="false" {if $GVIDEO.size_common != 'true'}checked="checked"{/if} data-toggle="size"> {'Change'|translate}</label>
      
      <span style="display:{if $GVIDEO.size_common == 'true'}none{else}block{/if};" id="size">
        <input type="text" name="width" value="{$GVIDEO.width}" size="4"> &times;
        <input type="text" name="height" value="{$GVIDEO.height}" size="4"> px
      </span>
    </p>
    
    <p>
      <b>{'Autoplay'|translate}</b>
      <label><input type="radio" name="autoplay_common" value="true" {if $GVIDEO.autoplay_common == 'true'}checked="checked"{/if} data-toggle="autoplay"> {'Use common setting'|translate}</label>
      <label><input type="radio" name="autoplay_common" value="false" {if $GVIDEO.autoplay_common != 'true'}checked="checked"{/if} data-toggle="autoplay"> {'Change'|translate}</label>
      
      <span style="display:{if $GVIDEO.autoplay_common == 'true'}none{else}block{/if};" id="autoplay">
        <label><input type="radio" name="autoplay" value="0" {if $GVIDEO.autoplay == '0'}checked="checked"{/if}> {'No'|translate}</label>
        <label><input type="radio" name="autoplay" value="1" {if $GVIDEO.autoplay == '1'}checked="checked"{/if}> {'Yes'|translate}</label>
      </span>
    </p>
    {else}
    <p>
      <b>{'Embed code'|translate}</b><br>
      <textarea name="embed_code" style="width:600px;height:160px;">{$GVIDEO.embed}</textarea>
    </p>
    {/if}
    
    <p style="margin:0;">
      <input class="submit" type="submit" value="{'Save Settings'|translate}" name="save_properties">
    </p>
  </fieldset>
</form>