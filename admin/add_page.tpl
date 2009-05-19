{include file='include/autosize.inc.tpl'}
{known_script id="jquery.dimensions" src=$ROOT_URL|@cat:"template-common/lib/plugins/jquery.dimensions.packed.js"}
{known_script id="jquery.cluetip" src=$ROOT_URL|@cat:"template-common/lib/plugins/jquery.cluetip.packed.js"}

<script type="text/javascript">
jQuery().ready(function(){ldelim}
  jQuery('.cluetip').cluetip({ldelim}
    width: 400,
    splitTitle: '|'
  });
});
</script>

<div class="titrePage">
<h2>{'py_title'|@translate}</h2>
</div>

<form name="add_page" method="post" action="{$pywaie_F_ACTION}" class="properties"  ENCTYPE="multipart/form-data">
<fieldset>
	<legend>{'py_addvideo'|@translate}</legend>
	<table>
		<tr>
			<td></td>
			<td>{'py_filename'|@translate} *</td>
			<td>&nbsp;
			<input type="text" size="55" maxlength="50" value="{$PYWAIE_ADD_NAME}" name="pywaie_add_name"/></td>
		</tr>
		<tr>
			<td><img class="cluetip" src="{$ICON_INFOBULLE}" alt="" title="{'py_url'|@translate}|{'pybulle_reference'|@translate}"></td>
			<td>{'py_url'|@translate} *</td>
			<td>&nbsp;
			<input type="text" size="55" value="{$PYWAIE_ADD_URL}" name="pywaie_add_url" /></td>
		</tr>
		<tr>
			<td><img class="cluetip" src="{$ICON_INFOBULLE}" alt="" title="{'py_parent'|@translate}|{'pybulle_categorie'|@translate}"></td>
			<td>{'py_parent'|@translate} *</td>
			<td>&nbsp;
      {html_options name="parent" options=$category_option_parent selected=$category_option_parent_selected}
      </td>
		</tr>

		<tr>
			<td colspan="3"><br><hr></td>
		</tr>

		<tr>
			<td><img class="cluetip" src="{$ICON_INFOBULLE}" alt="" title="{'py_thumb'|@translate}|{'pybulle_miniature'|@translate}"></td>
			<td>{'py_thumb'|@translate}</td>
  	</tr>

  	<tr>
	  		<td></td>
			<td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name="thumbnail" value="no_thumb" {$no_thumb_CHECKED}><i>&nbsp;&nbsp;{'py_no_thumb'|@translate}</i></td>
		</tr>
		<tr>
			<td></td>
			<td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name="thumbnail" value="thumb_from_server" {$thumb_from_server_CHECKED}>&nbsp;&nbsp;<i>{'py_thumb_from_server'|@translate}</i></td>
		</tr>
		<tr>
			<td></td>
			<td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name="thumbnail" id="thumb_from_user" value="thumb_from_user" {$thumb_from_user_CHECKED}>&nbsp;&nbsp;<i>{'py_thumb_from_user'|@translate}&nbsp;&nbsp;</i>
			<input name="picture" type="file" value="" size="20" onmousedown="document.getElementById('thumb_from_user').checked = true;"/></td>
		</tr>
		<tr>
			<td><br></td>
			<td colspan="2"><br>
				<table style="text-align:left;">
					<tr>
						<td colspan="3"><input type="checkbox" name="add_band" value="on" id="add_band" {$ADD_BAND}>&nbsp;&nbsp;{'py_add_band'|@translate}</td>
					</tr>
					<tr>
						<td><input type="checkbox" name="thumb_resize" value="on" id="thumb_resize" {$THUMB_RESIZE}>&nbsp;&nbsp;{'py_thumb_resize'|@translate} : &nbsp;</td>
						<td><input type="text" size="3" maxlength="3" value="{$DEFAULT_THUMB_W}" name="thumb_width" onmousedown="document.getElementById('thumb_resize').checked = true;"/>&nbsp;</td>
						<td><i>{'maximum width'|@translate}</i></td>
					</tr>
					<tr>
						<td></td>
						<td><input type="text" size="3" maxlength="3" value="{$DEFAULT_THUMB_H}" name="thumb_hight" onmousedown="document.getElementById('thumb_resize').checked = true;"/>&nbsp;</td>
						<td><i>{'maximum height'|@translate}</i></td>
					</tr>
				</table>
			</td>
		</tr>
	  	<tr>
			<td colspan="3"><br><hr></td>
		</tr>

		<tr>
			<td></td>
			<td>{'py_width'|@translate}</td>
			<td>&nbsp;
			<input type="text" size="3" maxlength="3" value="{$PYWAIE_ADD_W}" name="pywaie_add_w" /></td>
		</tr>
		<tr>
			<td></td>
			<td>{'py_height'|@translate}</td>
			<td>&nbsp;
			<input type="text" size="3" maxlength="3" value="{$PYWAIE_ADD_H}" name="pywaie_add_h" /></td>
		</tr>
		<tr>
			<td><img class="cluetip" src="{$ICON_INFOBULLE}" alt="" title="{'py_start'|@translate}|{'pybulle_start'|@translate}"></td>
			<td>{'py_start'|@translate}</td>
			<td>&nbsp;
			<input type="text" size="3" maxlength="4" value="{$PYWAIE_ADD_START}" name="pywaie_add_start" /></td>
		</tr>

		<tr>
			<td colspan="3"><br><hr></td>
		</tr>
		<tr>
			<td></td>
		      <td>{'Name'|@translate}</td>
        		<td>&nbsp;
			<input type="text" size="30" name="name" value="{$NAME}" /></td>
      	</tr>
		<tr>
			<td></td>
		      <td>{'Author'|@translate}</td>
        		<td>&nbsp;
			<input type="text" size="30" name="author" value="{$AUTHOR}" /></td>
      	</tr>

		<tr>
        		<td></td>
			<td>{'Description'|@translate}</td>
        		<td>&nbsp;
			<textarea rows="4" cols="42" name="description">{$DESCRIPTION}</textarea></td>
      	</tr>


	</table>
	<br />
	<div style="text-align:center;">
		<input class="submit" type="submit" value="{'Submit'|@translate}" name="submit_add" /></div>
<div style="text-align:right;">* {'py_*'|@translate}</div>
	</fieldset>
</form>
<br />