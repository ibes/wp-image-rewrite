<?php
// load dom library
include('simple_html_dom.php');

error_reporting(E_ALL);
ini_set("display_errors", 1);

// initialize wordpress
define( 'SHORTINIT', true );
require_once( '../wp-load.php' );

$praktikant = <<< EOT

 ,ggggggggggg,
dP"""88""""""Y8,                  ,dPYb,      I8         ,dPYb,                                I8
Yb,  88      `8b                  IP'`Yb      I8         IP'`Yb                                I8
 `"  88      ,8P                  I8  8I   88888888 gg   I8  8I                             88888888
     88aaaad8P"                   I8  8bgg,   I8    ""   I8  8bgg,                             I8
     88""""",gggggg,    ,gggg,gg  I8 dP" "8   I8    gg   I8 dP" "8    ,gggg,gg   ,ggg,,ggg,    I8
     88     dP""""8I   dP"  "Y8I  I8d8bggP"   I8    88   I8d8bggP"   dP"  "Y8I  ,8" "8P" "8,   I8
     88    ,8'    8I  i8'    ,8I  I8P' "Yb,  ,I8,   88   I8P' "Yb,  i8'    ,8I  I8   8I   8I  ,I8,
     88   ,dP     Y8,,d8,   ,d8b,,d8    `Yb,,d88b,_,88,_,d8    `Yb,,d8,   ,d8b,,dP   8I   Yb,,d88b,
     88   8P      `Y8P"Y8888P"`Y888P      Y88P""Y88P""Y888P      Y8P"Y8888P"`Y88P'   8I   `Y88P""Y8
EOT;
echo $praktikant;
echo "\n\r" . "\n\r";

global $wpdb;
// clean css class values (trim)
// trim spaces before classes
$sql = ("UPDATE " . $wpdb->prefix . "posts SET `post_content` = REPLACE(`post_content` , 'class=\"  ', 'class=\"')");
$wpdb->query($sql);
// trim spaces before classes
$sql = ("UPDATE " . $wpdb->prefix . "posts SET `post_content` = REPLACE(`post_content` , 'class=\" ', 'class=\"')");
$wpdb->query($sql);
// trim spaces after classes
$sql = ("UPDATE " . $wpdb->prefix . "posts SET `post_content` = REPLACE(`post_content` , ' \" title=\"', '\" title=\"')");
$wpdb->query($sql);

// select all posts from database
$sql = "SELECT id, post_content FROM " . $wpdb->prefix . "posts WHERE " .
" (`post_type` = 'post' OR `post_type` = 'page' OR `post_type` = 'revision') " .
//"AND `id` = '3894'" .
//"LIMIT 100" .
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
        $class_rewrite = str_replace("alignnone", "", $class_rewrite, $count_alignnone);
        $class_rewrite = str_replace("alignright", "", $class_rewrite, $count_alignright);
        $class_rewrite = trim($class_rewrite);

        if ( isset($count_alignright) && $count_alignright > 0 ) {
          $align = "alignright";
        } else if ( isset($count_alignnone) && $count_alignnone > 0 ) {
          $align = "alignnone";
        } else {
          $align = "alignleft";
        }

        $caption_class = "";
        if (isset ($element->class) &&
          ( (strpos($element->class, "size-full") !== false) || (strpos($element->class, "size-large") !== false) ) )  {
          $caption_class = ' class="caption-size-full" ';
        }

        // recreate caption
        $caption = '[caption align="' . $align . '" width="' . $element->width .'"' . $caption_class . ']' .
          '<img class="'. $class_rewrite .'" src="' . $element->src . '" alt="' . $element->alt . '" width="' . $element->width . '" height="' . $element->height . '" />' .
            $element->title . '[/caption]';

        echo $element;
        echo "\n\r";
        echo $caption;
        echo "\n\r";
        echo "\n\r";
        echo "----";
        echo "\n\r";

        // replace image notation with new notation
        $entry_rewrite = str_replace($element->outertext, $caption, $entry_rewrite);

        // update this entity
        $update = $wpdb->update(
          $wpdb->prefix . 'posts',
          array( 'post_content' => $entry_rewrite ),
          array( 'ID' => $entry->id)
        );

        $check_entity = $wpdb->get_row("SELECT id, post_content FROM " . $wpdb->prefix . "posts WHERE `ID` = " . $entry->id);
        echo "post_content: " . $check_entity->post_content;
        echo "\n\r";
        echo "\n\r";

      }
    } // - each image END
  }
} // - entry END
