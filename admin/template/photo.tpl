{combine_css path=$GVIDEO_PATH|@cat:"admin/template/style.css"}

{footer_script}{literal}
jQuery("input[data-toggle]").change(function() {
  $block = $('#'+ $(this).data('toggle'));
  if ($block.is(':visible')) {
    $block.css('display', 'none');
  } else {
    $block.css('display', 'block');
  }
});
{/literal}

var border = jQuery("input[name='url']").css('border');

jQuery("input[name='url']").keyup(function() {ldelim}
  if ($(this).val() != "{$GVIDEO.url}") {ldelim}
    $(this).css('border', '1px solid #c00');
    $("#change_warning").show();
  } else {ldelim}
    $(this).css('border', border);
    $("#change_warning").hide();
  }
});
{/footer_script}

<h2>{$TITLE} &#8250; {'Edit photo'|@translate} {$TABSHEET_TITLE}</h2>

<fieldset>
  <legend>{'Thumbnail'|@translate}</legend>
  <table>
    <tr>
      <td id="albumThumbnail">
        <img src="{$TN_SRC}" alt="{'Thumbnail'|@translate}" class="Thumbnail">
      </td>
      <td id="albumLinks" style="width:400px;vertical-align:top;">
        <ul style="padding-left:15px;margin:0;">
        {if $U_ADD_FILM_FRAME}
          <li style="margin:10px 0 20px 0;"><a href="{$U_ADD_FILM_FRAME}">{'Add film effect'|@translate}</a></li>
        {/if}
        
          <li>
            <form id="photo_update" method="post" action="{$F_ACTION}" enctype="multipart/form-data">
              {'Upload a new thumbnail'|@translate}<br>
              <input type="file" size="20" name="photo_update">
              <input class="submit" type="submit" value="{'Send'|@translate}" name="photo_update">
            </form>
          </li>
        </ul>
      </td>
    </tr>
  </table>
</fieldset>

<form action="{$F_ACTION}" method="post" id="catModify">
  <fieldset>
    <legend>{'Properties'|@translate}</legend>

    <p>
      <b>{'Video URL'|@translate}</b>
      <input type="text" name="url" value="{$GVIDEO.url}" style="width:400px;">
      <span id="change_warning" style="display:none;">{'Changing the url will reset video description, name and thumbnail'|@translate}</span>
    </p>

    <p>
      <b>{'Video size'|@translate}</b>
      <label><input type="radio" name="size_common" value="true" {if $GVIDEO.size_common == 'true'}checked="checked"{/if} data-toggle="size"> {'Use common setting'|@translate}</label>
      <label><input type="radio" name="size_common" value="false" {if $GVIDEO.size_common != 'true'}checked="checked"{/if} data-toggle="size"> {'Change'|@translate}</label>
      
      <span style="display:{if $GVIDEO.size_common == 'true'}none{else}block{/if};" id="size">
        <input type="text" name="width" value="{$GVIDEO.width}" size="4"> &times;
        <input type="text" name="height" value="{$GVIDEO.height}" size="4"> px
      </span>
    </p>
    
    <p>
      <b>{'Autoplay'|@translate}</b>
      <label><input type="radio" name="autoplay_common" value="true" {if $GVIDEO.autoplay_common == 'true'}checked="checked"{/if} data-toggle="autoplay"> {'Use common setting'|@translate}</label>
      <label><input type="radio" name="autoplay_common" value="false" {if $GVIDEO.autoplay_common != 'true'}checked="checked"{/if} data-toggle="autoplay"> {'Change'|@translate}</label>
      
      <span style="display:{if $GVIDEO.autoplay_common == 'true'}none{else}block{/if};" id="autoplay">
        <label><input type="radio" name="autoplay" value="0" {if $GVIDEO.autoplay == '0'}checked="checked"{/if}> {'No'|@translate}</label>
        <label><input type="radio" name="autoplay" value="1" {if $GVIDEO.autoplay == '1'}checked="checked"{/if}> {'Yes'|@translate}</label>
      </span>
    </p>

    <p style="margin:0;">
      <input class="submit" type="submit" value="{'Save Settings'|@translate}" name="save_properties">
    </p>
  </fieldset>
</form>