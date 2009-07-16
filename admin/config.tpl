<div class="titrePage">
<h2>{'py_title'|@translate}</h2>
</div>

<form action="{$pywaie_F_ACTION}" method="post" name="cat_options" id="cat_options">
	<table width="100%">
		<tr>
			<td width="50%"><fieldset>
			<legend>Google Video</legend>
			<table>
				<tr>
					<td>{'py_width'|@translate}</td>
					<td>&nbsp;
					<input type="text" size="3" maxlength="3" value="{$PYWAIE_GVIDEO_W}" name="pywaie_gvideo_w" /></td>
				</tr>
				<tr>
					<td>{'py_height'|@translate}</td>
					<td>&nbsp;
					<input type="text" size="3" maxlength="3" value="{$PYWAIE_GVIDEO_H}" name="pywaie_gvideo_h" /></td>
				</tr>
				<tr>
					<td>{'language'|@translate}</td>
					<td>&nbsp;
					<input type="text" size="3" maxlength="2" value="{$PYWAIE_GVIDEO_HL}" name="pywaie_gvideo_hl" /></td>
				</tr>
				<tr>
					<td>{'py_autostart'|@translate}</td>
					<td>&nbsp;
					<input type="radio" value="true" name="pywaie_gvideo_autoplay" {$PYWAIE_GVIDEO_AUTOPLAY_TRUE} /> 
					On
					<input type="radio" value="" name="pywaie_gvideo_autoplay" {$PYWAIE_GVIDEO_AUTOPLAY_FALSE} /> 
					Off </td>
				</tr>
			</table>
  	</fieldset> </td>
    
			<td><fieldset>
			<legend>YouTube</legend>
			<table>
				<tr>
					<td>{'py_width'|@translate}</td>
					<td>&nbsp;
					<input type="text" size="3" maxlength="3" value="{$PYWAIE_YTUBE_W}" name="pywaie_ytube_w" /></td>
				</tr>
				<tr>
					<td>{'py_height'|@translate}</td>
					<td>&nbsp;
					<input type="text" size="3" maxlength="3" value="{$PYWAIE_YTUBE_H}" name="pywaie_ytube_h" /></td>
				</tr>
				<tr>
					<td>{'py_autostart'|@translate}</td>
					<td>&nbsp;
					<input type="radio" value="1" name="pywaie_ytube_autoplay" {$PYWAIE_YTUBE_AUTOPLAY_TRUE} /> 
					On
					<input type="radio" value="0" name="pywaie_ytube_autoplay" {$PYWAIE_YTUBE_AUTOPLAY_FALSE} /> 
					Off </td>
				</tr>
			</table>
			</fieldset> </td>
		</tr>
    
		<tr>
			<td><fieldset>
			<legend>Dailymotion</legend>
			<table>
				<tr>
					<td>{'py_width'|@translate}</td>
					<td>&nbsp;
					<input type="text" size="3" maxlength="3" value="{$PYWAIE_DM_W}" name="pywaie_dm_w" /></td>
				</tr>
				<tr>
					<td>{'py_height'|@translate}</td>
					<td>&nbsp;
					<input type="text" size="3" maxlength="3" value="{$PYWAIE_DM_H}" name="pywaie_dm_h" /></td>
				</tr>
				<tr>
					<td>{'py_autostart'|@translate}</td>
					<td>&nbsp;
					<input type="radio" value="1" name="pywaie_dm_autoplay" {$PYWAIE_DM_AUTOPLAY_TRUE} /> 
					On
					<input type="radio" value="0" name="pywaie_dm_autoplay" {$PYWAIE_DM_AUTOPLAY_FALSE} /> 
					Off </td>
				</tr>
			</table>
			</fieldset> </td>
      
      <td><fieldset>
			<legend>Wideo</legend>
			<table>
				<tr>
					<td>{'py_width'|@translate}</td>
					<td>&nbsp;
					<input type="text" size="3" maxlength="3" value="{$PYWAIE_WIDEO_W}" name="pywaie_wideo_w" /></td>
				</tr>
				<tr>
					<td>{'py_height'|@translate}</td>
					<td>&nbsp;
					<input type="text" size="3" maxlength="3" value="{$PYWAIE_WIDEO_H}" name="pywaie_wideo_h" /></td>
				</tr>
				<tr>
					<td>{'py_autostart'|@translate}</td>
					<td>&nbsp;
					<input type="radio" value="true" name="pywaie_wideo_autoplay" {$PYWAIE_WIDEO_AUTOPLAY_TRUE} /> 
					On
					<input type="radio" value="false" name="pywaie_wideo_autoplay" {$PYWAIE_WIDEO_AUTOPLAY_FALSE} /> 
					Off </td>
				</tr>
			</table>
			</fieldset> </td>
		</tr>

		<tr>
			<td><fieldset>
			<legend>Vimeo</legend>
			<table>
				<tr>
					<td>{'py_width'|@translate}</td>
					<td>&nbsp;
					<input type="text" size="3" maxlength="3" value="{$PYWAIE_VIMEO_W}" name="pywaie_vimeo_w" /></td>
				</tr>
				<tr>
					<td>{'py_height'|@translate}</td>
					<td>&nbsp;
					<input type="text" size="3" maxlength="3" value="{$PYWAIE_VIMEO_H}" name="pywaie_vimeo_h" /></td>
				</tr>
				<tr>
					<td>{'py_autostart'|@translate}</td>
					<td>&nbsp;
					<input type="radio" value="1" name="pywaie_vimeo_autoplay" {$PYWAIE_VIMEO_AUTOPLAY_TRUE} /> 
					On
					<input type="radio" value="0" name="pywaie_vimeo_autoplay" {$PYWAIE_VIMEO_AUTOPLAY_FALSE} /> 
					Off </td>
				</tr>
			</table>
			</fieldset> </td>    
	</table>
	<div align="center">
		<input class="submit" type="submit" value="{'Submit'|@translate}" name="submit" /></div>
</form>
<br>