<?php
//FOR NEW YEARS
// change year variable each year below - about line 17 and line 18 big each year
// change gallery.php references to year this was
require_once('../config.php');
require_once(dirname(__FILE__) . '/inc/media_gallery_dynamic.php');
$connection = connect_database();
$location = isset($_GET['camp_location']) ? $_GET['camp_location'] : '';
$monthy = isset($_GET['month']) ? $_GET['month'] : '';
$dayy = isset($_GET['day']) ? $_GET['day'] : '';
$year = pb_media_gallery_year_from_request($connection);
$location = mysqli_escape_string($connection,$location);
$monthy = pb_media_gallery_month_name(mysqli_escape_string($connection,$monthy));
$dayy = mysqli_escape_string($connection,$dayy);
$data = pb_media_gallery_find_row($connection, $year, $location, $monthy, $dayy);
$num = $data ? 1 : 0;
if (!$data) {
  header("location: media.php?year=".$year."&camp_location=".str_replace(" ","+",$location));
  exit;
}
$location = $data["location"];
$location = str_replace("stem","",$location);
$location_full = $data["location_full"];
if (!$location_full) {
    $location_alias = pb_media_gallery_alias_for_code($location);
    if ($location_alias) {
        $location_full = $location_alias['location_full'];
    }
}
$location_display = str_replace('-TTC', '', $location_full);
$camp_year = $data["camp_year"];
$camp_month = $data["camp_month"];
$camp_day = $data["camp_day"];
$full_week_name = $data["full_week_name"];
$week_num = $data["week_num"];
$monday_video_file = $data["monday_video_file"];
$tuesday_video_file = $data["tuesday_video_file"];
$wednesday_video_file = $data["wednesday_video_file"];
$thursday_video_file = $data["thursday_video_file"];
$friday_video_file = $data["friday_video_file"];
$album_username = $data["album_username"];
$monday_photo_alb = $data["monday_photo_alb"];
$tuesday_photo_alb = $data["tuesday_photo_alb"];
$wednesday_photo_alb = $data["wednesday_photo_alb"];
$thursday_photo_alb = $data["thursday_photo_alb"];
$friday_photo_alb = $data["friday_photo_alb"];
$monthumb = '../blogload/uploads/'.$monday_video_file.'.jpg';
$tuethumb = '../blogload/uploads/'.$tuesday_video_file.'.jpg';
$wedthumb = '../blogload/uploads/'.$wednesday_video_file.'.jpg';
$thuthumb = '../blogload/uploads/'.$thursday_video_file.'.jpg';
$frithumb = '../blogload/uploads/'.$friday_video_file.'.jpg';
$video_prefixes = pb_media_gallery_video_prefixes($location_full, $location);
$video_filenames = array();
foreach ($video_prefixes as $video_prefix) {
    $video_filenames[] = $video_prefix . $camp_month . $camp_day . $camp_year . ".mp4";
}
$escaped_video_filenames = array();
foreach ($video_filenames as $video_filename) {
    $escaped_video_filenames[] = "'" . mysqli_real_escape_string($connection, $video_filename) . "'";
}
$query2 = "SELECT * FROM media_keeper WHERE media_keeper.video_file_name_mp4 IN (" . implode(",", $escaped_video_filenames) . ") ORDER BY FIELD(media_keeper.video_file_name_mp4, " . implode(",", $escaped_video_filenames) . ") LIMIT 1";
$result2=mysqli_query($connection,$query2);
$num2=mysqli_num_rows($result2);
$data2 = mysqli_fetch_assoc($result2);
$full_title_of_video = $data2 ? $data2["full_title_of_video"] : "";
$bravoblog_video_filename = $num2 ? $data2["video_file_name_mp4"] : $video_filenames[0];
$bravoblog_video_prefix = pb_media_gallery_primary_video_prefix($location_full, $location);
$second_video_filename = $bravoblog_video_prefix . $monthy . $dayy . $camp_year . "b.mp4";
$second_video_path = $_SERVER['DOCUMENT_ROOT'] . "/blogload/uploads/" . $second_video_filename;
$has_second_video = file_exists($second_video_path);
$dates = pb_media_gallery_dates_for_location($connection, $year, $location_full);



mysqli_close($connection);
if (file_exists($monthumb)) {
} else {
    $monthumb = 'https://www.planetbravo.com/img/monday.png';
}
if (file_exists($tuethumb)) {
} else {
    $tuethumb = 'https://www.planetbravo.com/img/tuesday.png';
}
if (file_exists($wedthumb)) {
} else {
    $wedthumb = 'https://www.planetbravo.com/img/wednesday.png';
}
if (file_exists($thuthumb)) {
} else {
    $thuthumb = 'https://www.planetbravo.com/img/thursday.png';
}
if (file_exists($frithumb)) {
} else {
    $frithumb = 'https://www.planetbravo.com/img/friday.png';
}
if ($location == "bh") {
    $bhactivelink = 'class="active"';
}
if ($location == "en") {
    $enactivelink = 'class="active"';
}
if ($location == "la") {
    $laactivelink = 'class="active"';
}
if ($location == "mb") {
    $mbactivelink = 'class="active"';
}
if ($location == "pas") {
    $pasactivelink = 'class="active"';
}
if ($location == "rpv") {
    $rpvactivelink = 'class="active"';
}
if ($location == "rs") {
    $rsactivelink = 'class="active"';
}
if ($location == "mv") {
    $smactivelink = 'class="active"';
}


ob_start();
session_start();

if ($_REQUEST["camp_location"])
  $camp_location = $_REQUEST["camp_location"];
else
  $camp_location = 0;
if ($_REQUEST["month"])
  $month = $_REQUEST["month"];
else
  $month = 0;
if ($_REQUEST["day"])
  $day = $_REQUEST["day"];
else
  $day = 0;
if (!$_REQUEST["year"] && (!$_REQUEST["camp_location"] || !$_REQUEST["month"] || !$_REQUEST["day"])) {
 $location = "media.php?camp_location=".$camp_location."&month=".$month."&day=".$day;
 header ("location: $location");
 exit;
} else {

//   if (strftime("%m") <= "07") {
//     if (strftime("%m") == "07" && strftime("%d") < "15")
//       $year = strftime("%Y");
//     elseif (strftime("%m") == "07" && strftime("%d") >= "15")
//       $year = strftime("%Y");
//     else
//       $year = strftime("%Y");
//   } else {
//     $year = strftime("%Y");
//   }



require_once(dirname(__FILE__) . '/../config.php');
require_once(dirname(__FILE__) . '/../db/admin/inc/MySQL.php');
require_once(dirname(__FILE__) . '/../db/admin/inc/rwd.php');
foreach ($dates as $key => $value)
  foreach ($value as $value_1) {
    $all_dates[] = $key."~".$value_1;
  }
$all_dates_id = 0;
    if ($_REQUEST["month"] == "January") { $month = "01"; }
elseif ($_REQUEST["month"] == "February") { $month = "02"; }
elseif ($_REQUEST["month"] == "March") { $month = "03"; }
elseif ($_REQUEST["month"] == "April") { $month = "04"; }
elseif ($_REQUEST["month"] == "May") { $month = "05"; }
elseif ($_REQUEST["month"] == "June") { $month = "06"; }
elseif ($_REQUEST["month"] == "July") { $month = "07"; }
elseif ($_REQUEST["month"] == "August") { $month = "08"; }
elseif ($_REQUEST["month"] == "September") { $month = "09"; }
elseif ($_REQUEST["month"] == "October") { $month = "10"; }
elseif ($_REQUEST["month"] == "November") { $month = "11"; }
elseif ($_REQUEST["month"] == "December") { $month = "12"; }
$current_date = $month."~".$day;
if (!empty($all_dates)) $all_dates = array_unique($all_dates);
foreach ($all_dates as $key => $value) {
  if ($value == $current_date) $all_dates_id = $key;
}
$x = explode("~",$all_dates[$all_dates_id-1]);
$location_prev_month = $x[0];
$location_prev_day = $x[1];
    if ($location_prev_month == "01") { $location_prev_month = "January"; }
elseif ($location_prev_month == "02") { $location_prev_month = "February"; }
elseif ($location_prev_month == "03") { $location_prev_month = "March"; }
elseif ($location_prev_month == "04") { $location_prev_month = "April"; }
elseif ($location_prev_month == "05") { $location_prev_month = "May"; }
elseif ($location_prev_month == "06") { $location_prev_month = "June"; }
elseif ($location_prev_month == "07") { $location_prev_month = "July"; }
elseif ($location_prev_month == "08") { $location_prev_month = "August"; }
elseif ($location_prev_month == "09") { $location_prev_month = "September"; }
elseif ($location_prev_month == "10") { $location_prev_month = "October"; }
elseif ($location_prev_month == "11") { $location_prev_month = "November"; }
elseif ($location_prev_month == "12") { $location_prev_month = "December"; }
  $x = explode("~",$all_dates[$all_dates_id+1]);
  $location_next_month = $x[0];
  $location_next_day = $x[1];
    if ($location_next_month == "01") { $location_next_month = "January"; }
elseif ($location_next_month == "02") { $location_next_month = "February"; }
elseif ($location_next_month == "03") { $location_next_month = "March"; }
elseif ($location_next_month == "04") { $location_next_month = "April"; }
elseif ($location_next_month == "05") { $location_next_month = "May"; }
elseif ($location_next_month == "06") { $location_next_month = "June"; }
elseif ($location_next_month == "07") { $location_next_month = "July"; }
elseif ($location_next_month == "08") { $location_next_month = "August"; }
elseif ($location_next_month == "09") { $location_next_month = "September"; }
elseif ($location_next_month == "10") { $location_next_month = "October"; }
elseif ($location_next_month == "11") { $location_next_month = "November"; }
elseif ($location_next_month == "12") { $location_next_month = "December"; }
  if ($location_prev_month && $location_prev_day)
    $location_prev = "gallery.php?year=".$year."&camp_location=".$camp_location."&month=".$location_prev_month."&day=".$location_prev_day;
  else
    $location_prev = 0;
  if ($location_next_month && $location_next_day)
    $location_next = "gallery.php?year=".$year."&camp_location=".$camp_location."&month=".$location_next_month."&day=".$location_next_day;
  else
    $location_next = 0;
	$current_month = $month;
	if ($current_month == "01") { $current_month = "January"; }
elseif ($current_month == "02") { $current_month = "February"; }
elseif ($current_month == "03") { $current_month = "March"; }
elseif ($current_month == "04") { $current_month = "April"; }
elseif ($current_month == "05") { $current_month = "May"; }
elseif ($current_month == "06") { $current_month = "June"; }
elseif ($current_month == "07") { $current_month = "July"; }
elseif ($current_month == "08") { $current_month = "August"; }
elseif ($current_month == "09") { $current_month = "September"; }
elseif ($current_month == "10") { $current_month = "October"; }
elseif ($current_month == "11") { $current_month = "November"; }
elseif ($current_month == "12") { $current_month = "December"; }

?>
<!doctype html>
<html class="no-js" lang="en">
    <head>
   
<?php
print <<< EOD

   <meta property="fb:app_id" content="140649427418" />
   <meta property='og:type' content='video.movie' />
   <meta property='og:url' content='https://www.planetbravo.com/camps/gallery.php?year=$camp_year&camp_location=$location_full&month=$camp_month&day=$camp_day' />
   <meta property='og:title' content='BravoBlog - $camp_month $camp_day, $camp_year' />
   <meta property='og:description' content='by PlanetBravo $location_full' />
   <meta property='og:image' content='https://www.planetbravo.com/img/bravoblog-poster.png' />
   <meta property='og:video:url' content='https://www.planetbravo.com/blogload/uploads/$bravoblog_video_filename' />
   
   
	<meta property='og:video:height' content='360' />
    <meta property='og:video:width' content='640' />
	<meta property='og:video:type' content='video/mp4' />
   


EOD;
?>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/header1lite.php'; ?>
<link href="/css/stylesheets/videojs.css" rel="stylesheet">
<link href="/css/vendor/nanogallery/themes/light/nanogallery_light.min.css" rel="stylesheet"> 
<link rel="stylesheet" type="text/css" href="/css/gallerystuff.css">  

<style>
.video-frame {
  position: relative;
  width: 100%;
  max-width: 960px;
  margin: 0 auto 2rem;
  aspect-ratio: 16 / 9;
  background-color: black;
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4);
  overflow: hidden; /* this is important! */
}

.video-frame .video-js,
.video-frame .video-js video {
  position: absolute;
  top: 0;
  left: 0;
  width: 100% !important;
  height: 100% !important;
  object-fit: contain !important; /* THIS is the critical line */
}


	.video-wrap {
    margin-bottom: 3rem;
    position: relative;
    z-index: 1;
  }

  .video-js {
    width: 100% !important;
    max-width: 1000px;
    height: auto !important;
    margin: 0 auto;
    position: relative;
  }

  .video-js.single-video {
    max-width: 1200px;
  }



  /* Center the play button properly */

	.video-js .vjs-big-play-button {
  position: absolute !important;
  top: 60% !important;
  left: 57% !important;
  transform: translate(-50%, -50%) !important;
  width: 4rem !important;
  height: 4rem !important;
  font-size: 2rem !important;
  line-height: 4rem !important;
  border: none !important;
  background-color: rgba(0, 0, 0, 0.5) !important;
  border-radius: 50% !important;
  padding: 0 !important;
  z-index: 10 !important;
}
	/* Override when it's just one video */
.video-js.single-video .vjs-big-play-button {
  top: 55% !important;
  left: 54% !important;
}

</style>


		
		
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/header2.php'; ?>
<div class="show-for-large-up">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/subnav-camps-top.php'; ?>
</div>
<div class="hero gallery-hero">
    <div class="row">
        <div class="small-12 columns text-center">
            <h1 class="responsive-headline">STEM Camps</h1>
            <h2 class="responsive-headline"><?php echo $_REQUEST["camp_location"]; ?> Media</h2>
            <ul class="media-date-list">
                <li>
                <?php if ($location_prev) { ?>
		  <a href="<?php echo $location_prev; ?>"><i class="fa fa-chevron-left"></i> PREV</a>
                <?php } else { echo "&nbsp;"; } ?>
                </li>
                <li><h3><?php
		  echo $_REQUEST["month"]." ";
		  echo $_REQUEST["day"].", $year";
                ?></h3>
                </li>
                <li>
                <?php if ($location_next) { ?>
		  <a href="<?php echo $location_next; ?>">NEXT <i class="fa fa-chevron-right"></i></a>
                <?php } else { echo "&nbsp;"; } ?>
                </li>
            </ul>
            <a href="<?php echo str_replace("gallery","media",$_SERVER["REQUEST_URI"])."&go=yes"; ?>">Choose a different date</a> <!--| <a href="https://www.planetbravo.com/camps/gallery-stem.php?camp_location=<?php echo $location_display  ?>&month=<?php echo $current_month  ?>&day=<?php echo $day; ?>">View STEM Media</a>-->
       
       <br /><br />
            
            
            <select onchange="window.location='https://www.planetbravo.com/camps/gallery.php?year=<?php echo $year; ?>&camp_location=' + this.value + '&month=<?php echo $current_month  ?>&day=<?php echo $day; ?>';" >
  <option disabled selected value>VIEW ANOTHER LOCATION</option>
  <!--<option value="Berkeley">Berkeley</option>-->
  <!--<option value="Beverly Hills">Beverly Hills</option>-->
  <option value="Beverly Hills-TTC">Beverly Hills</option>
  <option value="Burbank-TTC">Burbank</option>
  <option value="Pasadena/Eagle Rock-TTC">Eagle Rock/Pasadena</option>
				<option value="Encino-TTC">Encino</option>
  <option value="Irvine-TTC">Irvine</option>
  <option value="Manhattan Beach-TTC">Manhattan Beach</option>
  <!--<option value="Marin">Marin</option>-->
  
  <option value="Mar Vista/Santa Monica-TTC">Mar Vista/Santa Monica</option>
  <option value="Westchester-TTC">Westchester</option>
				
</select>
        <div class="small-12 large-11 large-centered columns">
<!-- <div id="nanoGallery"></div> -->
    <div align="center">
     <a class="orangebutton" id="pdf" href="<?php echo $album_username; ?>">See Today's Camp Photos</a>
    </div>

  </div>
       
        </div>

</div>
    </div>

<?php if ($num2) { ?>
<section class="video-wrap">
  <div class="row">
	  
    <?php if ($has_second_video): ?>
      <div class="small-12 large-6 columns">
		  <div class="video-frame">
        <video src="/blogload/uploads/<?php echo $bravoblog_video_filename; ?>"
               class="video-js vjs-default-skin"
               controls preload="metadata"
               poster="/img/bravoblog-poster.png"
               data-setup='{}'>
          <p class="vjs-no-js">To view this video please enable JavaScript...</p>
			  </video></div>
      </div>
      <div class="small-12 large-6 columns">
		  <div class="video-frame">
        <video src="/blogload/uploads/<?php echo $second_video_filename; ?>"
               class="video-js vjs-default-skin"
               controls preload="metadata"
               poster="/img/bravoblog-poster.png"
               data-setup='{}'>
          <p class="vjs-no-js">To view this video please enable JavaScript...</p>
			  </video> </div>
      </div>
    <?php else: ?>
      <div class="row">
        <div class="small-12 large-10 large-centered columns">
			<div class="video-frame">
          <video src="/blogload/uploads/<?php echo $bravoblog_video_filename; ?>"
                 id="bravoblog"
                 class="video-js vjs-default-skin single-video"
                 controls preload="metadata"
                 poster="/img/bravoblog-poster.png"
                 data-setup='{"example_option":true}'>
            <p class="vjs-no-js">To view this video please enable JavaScript...</p>
          </video></div>
        </div>
      </div>
      <script>
        var myvid = document.getElementById('bravoblog');
        var myvids = [
          "/blogload/uploads/<?php echo $bravoblog_video_filename; ?>"
        ];
        var activeVideo = 0;
        myvid.addEventListener('ended', function(e) {
          activeVideo = (++activeVideo) % myvids.length;
          myvid.src = myvids[activeVideo];
          myvid.autoplay = false;
          myvid.load();
        });
      </script>
    <?php endif; ?>
  </div>
</section>
<?php } ?>




<section>

 <!--  <div class="small-12 large-11 large-centered columns"> -->
<!-- <div id="nanoGallery"></div> -->
    <!-- <div align="center">
     <a class="orangebutton" id="pdf" href="<?php echo $album_username; ?>">See Today's Camp Photos</a>
    </div>

  </div> -->
</section>
<section>
    <div class="row">
        <div class="small-12 medium-6 medium-centered text-center columns social-footer">
            <h3>Stay connected with PlanetBravo!</h3>
                      <a target="_blank" href="https://www.facebook.com/planetbravo" title="PlanetBravo on Facebook"><i class="fa fa-facebook-square fa-3x"></i></a>
                      <a target="_blank" href="https://twitter.com/planetbravo" title="PlanetBravo on Twitter"><i class="fa fa-twitter-square fa-3x"></i></a>
        </div>
    </div>
</section>
<div class="row">
    <div class="small-12 hide-for-large-up columns">
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/subnav-camps.php'; ?>
    </div>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footerTEST.php'; ?>
<script src="//vjs.zencdn.net/4.12/video.js"></script>  
<script src="/js/vendor/jquery.nanogallery.min.js"></script> 
<script>



    $(document).ready(function () {
        jQuery("#nanoGallery").nanoGallery({
            kind: 'picasa',
            theme: 'light',
            touchAutoOpenDelay: -1,
            thumbnailWidth: 'auto',
            thumbnailHeight: 250,
            thumbnailLabel: {
                position: 'overImageOnMiddle',
                display: false,
                displayDescription: true,
                titleMaxLength: 20,
                hideIcons: true,
                align: 'center',
                itemsCount: 'description'            
            },
            colorScheme: 'none',
			locationHash:false,
            thumbnailGutterWidth : 0,
            thumbnailGutterHeight : 0,
            thumbnailHoverEffect: 'imageScaleIn80',
			// Dynamic Album Config
			userID: '<?php Print($album_username); ?>',
            album: '<?php Print($monday_photo_alb); ?>',
        });
    });
	
</script>
    </body>
</html>
<?php
}
?>
