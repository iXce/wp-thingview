<?php
/**
 * @package ThingView
 * @version 0.1
 */
/*
Plugin Name: ThingView
Plugin URI: http://guillaume.segu.in/blog/
Description: This plugins handles the display of uploaded STLs using thingiview.js in a simple way
Author: Guillaume Seguin
Version: 0.1
Author URI: http://guillaume.segu.in/
*/

function thingview_upload_mimes($mimes) {
  $mimes['stl'] = 'application/sla';
  return $mimes;
}
add_filter('mime_types', 'thingview_upload_mimes');

?>
