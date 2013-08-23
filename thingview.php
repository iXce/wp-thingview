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

require('thingiview.js/php/convert.php');

function thingview_upload_mimes($mimes) {
  $mimes['stl'] = 'application/sla';
  return $mimes;
}
add_filter('mime_types', 'thingview_upload_mimes');

function thingview_handle_upload($data, $task) {
  if ($task != 'upload')
    return $data;

  $filename = $data['file'];
  $json_filename = "$filename.json";
  $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

  if ($data['type'] != 'application/sla' || $ext != 'stl')
    return $data;

  set_time_limit(3000); // Increase time limit
  $contents = file_get_contents($filename);
  $contents = preg_replace('/$\s+.*/', '', $contents);

  if (stripos($contents, 'solid') === FALSE) {
    $handle = fopen($filename, 'rb');
    if (!$handle) {
      trigger_error("Failed to open file $filename");
    }
    $result = parse_stl_binary($handle);
  } else {
    $result = parse_stl_string($contents);
  }
  
  file_put_contents($json_filename, json_encode($result));
  return $data;
}
add_filter('wp_handle_upload', 'thingview_handle_upload', 10, 2);

function thingview_delete_file($filename) {
  $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

  if ($ext != 'stl')
    return $file;

  $json_filename = "$filename.json";

  if (file_exists($json_filename))
    unlink($json_filename);

  return $filename;
}
add_filter('wp_delete_file', 'thingview_delete_file');

function thingview_script() {
  ?>
  <script type="text/javascript">
  jQuery(document).ready( function($) {
    $( 'li.attachment > div.type-application.subtype-sla:parent' ).live( 'click', function( event ) {
      $( ".link-to > [value='none']").attr( "selected", true ); // selected none in select field
      $( ".link-to-custom" ).val( '' ); // clear input field for target of link
      $( '.media-sidebar div.setting' ).remove(); // remove link field
      $( '.attachment-display-settings' ).attr( "hidden", true );
    });
  } );
  </script>
  <?php
}
add_action('print_media_templates', 'thingview_script');

function thingview_send_to_editor($html, $id, $attachment) {
  $filename = get_attached_file($attachment['id'], true);
  $ext = pathinfo($filename, PATHINFO_EXTENSION);

  if ($ext == 'stl') {
    $html = sprintf('[thing id=%d width="500px" height="360px" class="aligncenter"]', $attachment['id']);
  }

  return $html;
}
add_filter('media_send_to_editor', 'thingview_send_to_editor', 10, 3);

function thingview_thing_shortcode($attributes) {
  $default_attributes = array('id' => 0,
                              'width' => '500px',
                              'height' => '360px',
                              'class' => '',
                              'color' => '#86E4FF',
                              'background' => 'inherit');
  $attributes = shortcode_atts($default_attributes, $attributes, 'thing');
  $file_url = wp_get_attachment_url($attributes['id']);
  if ($file_url === false)
    return "Missing attachment STL file";

  wp_enqueue_script('jquery');
  wp_print_scripts('jquery');
  $js_dir = plugins_url("thingiview.js/javascripts/", __FILE__);
  wp_enqueue_script("Three.js", $js_dir . "three.min.js");
  wp_enqueue_script("thingiview.js", $js_dir . "thingiview.js",
                    array("Three.js"));

  return '
  <script>
    jQuery(document).ready(function() {
      thingiurlbase = "' . $js_dir . '";
      thingiview' . $attributes['id'] . ' = new Thingiview("thing-' . $attributes['id'] . '");
      thingiview' . $attributes['id'] . '.setObjectColor("' . $attributes['color']. '");
      thingiview' . $attributes['id'] . '.setBackgroundColor("' . $attributes['background'] . '");
      thingiview' . $attributes['id'] . '.initScene();
      thingiview' . $attributes['id'] . '.loadJSON("' . $file_url . '.json");
    })
  </script>
  <div id="thing-' . $attributes['id'] .
  '" class="' . $attributes['class'] .
  '" style="width:' . $attributes['width'] .
         '; height:' . $attributes['height'].'"></div>';
}
add_shortcode( 'thing', 'thingview_thing_shortcode' );

?>
