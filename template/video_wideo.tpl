<object width="{$GVIDEO.width}" height="{$GVIDEO.height}" type="application/x-shockwave-flash" data="http://sa.kewego.com/swf/kp.swf" name="kplayer_{$GVIDEO.video_id}" id="kplayer_{$GVIDEO.video_id}">
  <param name="bgcolor" value="0x000000"/>
	<param name="allowfullscreen" value="true"/>
	<param name="allowscriptaccess" value="always"/>
	<param name="flashVars" value="playerKey=0df9b773a15b&configKey=&suffix=&sig={$GVIDEO.video_id}&autostart={$GVIDEO.autoplay}"/>
  <param name="movie" value="http://sa.kewego.com/swf/kp.swf"/>
  <param name="wmode" value="opaque"/>
  <param name="SeamlessTabbing" value="false"/>
  <video poster="http://api.kewego.com/video/getHTML5Thumbnail/?playerKey=0df9b773a15b&sig={$GVIDEO.video_id}" height="{$GVIDEO.width}" width="{$GVIDEO.height}" preload="none" controls="controls"></video>
  <script src="http://sa.kewego.com/embed/assets/kplayer-standalone.js"></script>
  <script defer="defer">kitd.html5loader("flash_kplayer_{$GVIDEO.video_id}","http://api.kewego.com/video/getHTML5Thumbnail/?playerKey=0df9b773a15b&sig={$GVIDEO.video_id}");</script>
</object>