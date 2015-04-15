<?php

include('simple_html_dom.php');

error_reporting(E_ALL);
ini_set("display_errors", 1);

//mail('mail@der-sebi.de', 'cronjob runs', 'cronjob runs');

// -------------------------

// initialize wordpress
define( 'SHORTINIT', true );
require_once( '../wp-load.php' );

function get_all_entries() {
  global $wpdb;
  $sql = "SELECT id, post_content FROM asf2posts WHERE " .
  " (`post_type` = 'post' OR `post_type` = 'page' OR `post_type` = 'revision') " .
  //"AND `id` = '13185'" .
 // "LIMIT 100" .
  "";
  $entries = $wpdb->get_results( $sql);
  return $entries;
}

$entries = get_all_entries();

// print_r ($entries);

foreach($entries as $entry) {
  // echo $entry->post_content;
  // echo "<br><hr><br>";
  // echo "<h1>" . $i . "</h1>";
  // echo "<br><hr><br>";
  // $i++;

    // Create DOM from URL or file
  $entry_original = $entry->post_content;
  $html = str_get_html($entry->post_content);

  if ($html) {

    $image_count = count($html->find('img'));
    $image_count = trim($image_count);

    if ($image_count > 0) {
      // each image
      foreach($html->find('img') as $element) {
        if (isset ($element->title) && (strpos($element->title, "©") !== false) ) {
          //echo $element . "\n\r";
          //echo $element->title . "\n\r";
          //echo ( strpos($element->title, "©") !== false) ? "Copyright" : "eigen";
          //echo "\n\r"  . "\n\r" ;

$caption = '[caption align="alignleft" width="' . $element->width . '" style="' . $element->style . '"]<img class="'. $element->class .'"  src="' . $element->src . '" alt="' . $element->alt . '" width="' . $element->width . '" height="' . $element->height . '" />' . $element->title . '[/caption]';


$entry_rewrite = str_replace($element->outertext, $caption, $entry_original);

echo "works";

  global $wpdb;
  $update = $wpdb->update(
    'asf2posts',
    array(
      'post_content' => $entry_rewrite
    ),
    array(
      'ID' => $entry->id
    )
  );
  print_r($update);

  $sql = "SELECT post_content FROM asf2posts WHERE `id` = $entry->id";
  $result = $wpdb->get_row( $sql);
  echo $result->post_content;




        }
      } // - each image END
    }
  }


} // - entry END




// function update_formdata_country() {
//   global $wpdb;

//   // get posts
//   $args = array( 'post_type' => 'job_listing', 'post_status'=> 'publish' );
//   $sql = "SELECT p.ID as id, m.meta_value as country FROM $wpdb->posts p, $wpdb->postmeta m WHERE p.post_type = 'job_listing' AND p.post_status = 'publish' AND m.meta_key = '_job_country' AND p.id = m.post_id";
//   $posts = $wpdb->get_results( $sql );

//   // get countries
//   $countries = get_all_countries();

//   // delete existing country records
//   $table = $wpdb->prefix . 'formdata_country';
//   $delete = $wpdb->query("TRUNCATE TABLE $table");

//   foreach ($posts as $post) {

//     $user_countries = explode(", ", $post->country);
//     foreach ($user_countries as $user_country) {

//       // if country is a valid country
//       if (isset($countries[$user_country])) {
//         // save country in stats
//         $result = $wpdb->insert(
//           $wpdb->prefix . 'formdata_country',
//           array(
//             'pid' => $post->id,
//             'data' => $countries[$post->country]->id
//           ),
//           array(
//             '%d',
//             '%d'
//           )
//         );

//       }
//       // countries which are not valid, will fail
//       else {
//         echo $user_country . ' failed' . "\n\r";
//         echo " on post " . $post->id  . "\n\r";
//       }
//     }
//   }
// }

// // update count of country
// function update_formdata() {
//   global $wpdb;
//   $sql = 'UPDATE ' . $wpdb->prefix . 'formdata f SET f.count_items = (SELECT COUNT(c.pid) FROM ' . $wpdb->prefix . 'formdata_country c WHERE (c.data = f.id) GROUP BY c.data) WHERE (f.field = "country")';
//   $wpdb->get_results( $sql);
// }

// function print_country_stats($statisticFile) {
//   global $wpdb;
//   $sql = "SELECT description, count_items FROM " . $wpdb->prefix . "formdata WHERE `field` = 'country' AND `count_items` > 0 ORDER BY count_items DESC";
//   $countries = $wpdb->get_results( $sql);
//   $count = 0;
//   foreach($countries as $country) {
//     $txt = $country->description . " - " . $country->count_items . '<br>' . "\n";
//     fwrite($statisticFile, $txt);
//     $count++;
//   }
//   $txt = "<br><strong>Sum of countries:</strong> " . $count . "\n";
//   fwrite($statisticFile, $txt);

//   return $count;
// }

// function print_member_stats($statisticFile) {
//   global $wpdb;

//   $sql = "SELECT COUNT(*) as count FROM $wpdb->posts WHERE post_type = 'job_listing' AND post_status = 'publish'";
//   $members = $wpdb->get_results( $sql );
//   $txt = "<br><br><strong>Sum of members:</strong> " . $members[0]->count . "\n";
//   fwrite($statisticFile, $txt);

//   return $members[0]->count;
// }

// $file = dirname(__FILE__) . "/hw-statistics.html";
// $statisticFile = fopen($file, "w") or die("Unable to open file!");
// $txt =  "<html><body>" . "\n" .
//         "<h1>Hostwriter Statistics</h1>\n" .
//         "date: " . date("D M j G:i:s T Y") . "<br><br>" . "\n" . "\n" .
//         "date: " . date('j.n.Y') . "<br><br>" . "\n" . "\n";
// fwrite($statisticFile, $txt);

// update_formdata_country();
// update_formdata();
// $countryCount = print_country_stats($statisticFile);
// $memberCount = print_member_stats($statisticFile);


// // == generate Image

// //Dynamically resize, crop, watermark and save images using Imagick
// function image_create($imagePathBackground,$imagePathNewImage,$fontPath,$width,$height,$members,$countries) {
//     try {
//         /*** the image file ***/
//         $image = $imagePathBackground;
//         /*** a new imagick object ***/
//         $im = new Imagick();
//         /*** ping the image ***/
//         $im->pingImage($image);
//         /*** read the image into the object ***/
//         $im->readImage($image);
//         /*** thumbnail the image ***/
//         $im->setImageFormat('jpg');
//         /*** Create Copyright Watermark ***/

//         /* Create a drawing object and set the font size */
//         $draw = new ImagickDraw();
//         /*** set the font ***/
//         $draw->setFont( $fontPath );

//         /*** add some transparency ***/
//         $draw->setFillColor( '#012152' );
//         $draw->setFillAlpha( 1 );
//         /*** set gravity to the center ***/
//         //$draw->setGravity( Imagick::GRAVITY_CENTER );
//         /*** set the font size ***/
//         $draw->setFontSize( 90 );
//         /*** overlay the text on the image ***/
//         $im->annotateImage($draw,325,250,0,$members);
//         $im->annotateImage($draw,730,250,0,$countries);

//         $draw->setFontSize( 24 );
//         $im->annotateImage($draw,345,300,0,'members');
//         $im->annotateImage($draw,725,300,0,'countries');

//         /*** Write the thumbnail to disk ***/
//         $im->writeImage(dirname(__FILE__) . $imagePathNewImage);
//         /*** Free resources associated with the Imagick object ***/
//         $im->destroy();
//     }
//     catch(Exception $e) { echo $e->getMessage(); }
// };

// /*** CODE FOR RUNTIME ***/
//   $imgPathBackground = dirname(__FILE__) . "/cron/background.jpg";
//   $fontPath = dirname(__FILE__) . "/cron/robotoslab-bold-webfont.ttf";
//   $imgPathNewImage = "/wp-content/uploads/2015/02/967hostwriters_slider.jpg";
//   $imgWidth = "1140";
//   $imgHeight = "500";
//   $members = $memberCount;
//   $countries = $countryCount;

//   image_create($imgPathBackground,$imgPathNewImage,$fontPath,$imgWidth,$imgHeight,$members,$countries);

//   $txt = '<img src="https://hostwriter.org/' . $imgPathNewImage . '" />' . "\n";
//   fwrite($statisticFile, $txt);
//   fclose($statisticFile);
// ?>