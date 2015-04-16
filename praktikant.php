<?php

// load dom library
include('simple_html_dom.php');

error_reporting(E_ALL);
ini_set("display_errors", 1);

// initialize wordpress
define( 'SHORTINIT', true );
require_once( '../wp-load.php' );

global $wpdb;
$sql = "SELECT id, post_content FROM asf2posts WHERE " .
" (`post_type` = 'post' OR `post_type` = 'page' OR `post_type` = 'revision') " .
//"AND `id` = '28132'" .
"LIMIT 100" .
"";
$entries = $wpdb->get_results( $sql);


foreach($entries as $entry) {

    // Create DOM from URL or file
  $entry_rewrite = $entry->post_content;
  $html = str_get_html($entry->post_content);

  // check if entity could be loaded
  if ($html) {

    // skip if images are rewritten
    if( strpos($html, "[caption") ) {
      continue;
    }

    $image_count = count($html->find('img'));
    $image_count = trim($image_count);

    // skip if there are no images
    if ($image_count <= 0) {
      continue;
    }

    // each image
    foreach($html->find('img') as $element) {

      // only for images with copyright notice
      if (isset ($element->title) && (strpos($element->title, "©") !== false) ) {

        // clean "align" classes
        $class_rewrite = str_replace("alignleft", "", $element->class);
        $class_rewrite = str_replace("alignnone", "", $class_rewrite);
        $class_rewrite = trim($class_rewrite);

        // recreate caption
        $caption = '[caption align="alignleft" width="' . $element->width . '" style="' . $element->style . '"]' .
          '<img class="'. $class_rewrite .'"  src="' . $element->src . '" alt="' . $element->alt . '" width="' . $element->width . '" height="' . $element->height . '" />' .
            $element->title . '[/caption]';

        // replace image notation with new notation
        $entry_rewrite = str_replace($element->outertext, $caption, $entry_rewrite);


        // connect to database
        global $wpdb;
        // update this entity
        $update = $wpdb->update(
          'asf2posts',
          array( 'post_content' => $entry_rewrite ),
          array( 'ID' => $entry->id)
        );
        //print_r($update);

      }
    } // - each image END

    // see if it worked
    $sql = "SELECT post_content FROM asf2posts WHERE `id` = $entry->id";
    $result = $wpdb->get_row( $sql);
    echo $result->post_content;
    echo "\n\r" . "\n\r";
    echo " ++++++++ ";
    echo "\n\r" . "\n\r";
  }
} // - entry END
