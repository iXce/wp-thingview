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
  $ext = pathinfo($filename, PATHINFO_EXTENSION);

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
  $ext = pathinfo($filename, PATHINFO_EXTENSION);

  if ($ext != 'stl')
    return $file;

  $json_filename = "$filename.json";

  if (file_exists($json_filename))
    unlink($json_filename);

  return $filename;
}
add_filter('wp_delete_file', 'thingview_delete_file');

?>
