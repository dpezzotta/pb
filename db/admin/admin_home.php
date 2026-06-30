<?php ob_start();
session_start();
require(dirname(__FILE__) . '/admin_config.php');
require_once(dirname(__FILE__) . '/inc/SuperAdmin.php');
$admin_id = $_SESSION['admin_id'];
if (!$admin_id) {
    header("Location: admin_login.php");
}
show_header();
?>
<style>
  #date-filter {
  position: static;
  clear: both;
  z-index: 1;
  max-width: 980px;
  margin: 1.25rem auto 1.5rem;
  padding: 16px 18px 18px;
  background: #ffffff;
  border: 1px solid #d8e4f0;
  border-radius: 8px;
  box-shadow: 0 8px 24px rgba(11, 57, 99, 0.08);
  text-align: left;
  scroll-margin-top: 78px;
}
  
  h4{
    margin-bottom:5px;
    font-family:'ralewaymedium';
  }
  h5{
    text-decoration:underline;
    font-family:'ralewaymedium';
  }
  tr{
    background-color:#E6E6E6;
  }
  #date-filter .filter-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 0 0 10px;
    padding-bottom: 9px;
    border-bottom: 1px solid #e6eef6;
    color: #0b3b66;
    font-family: var(--bodyfont);
    font-size: 0.95rem;
    font-weight: 800;
  }
  #date-filter .filter-title span {
    color: #7a8da3;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
  }
  #date-filter .filter-controls {
    display: grid;
    gap: 13px;
  }
  #date-filter .filter-row {
    display: flex;
    justify-content: center;
    gap: 14px;
  }
  #date-filter .filter-row-primary {
    align-items: flex-end;
  }
  #date-filter .filter-row-secondary {
    align-items: flex-end;
    padding-top: 3px;
  }
  #date-filter .filter-field {
    display: block;
    margin: 0;
  }
  #date-filter .filter-field {
    display: flex;
    flex-direction: column;
    width: 176px;
    gap: 5px;
  }
  #date-filter label,
  #date-filter .filter-label {
    color: #314761;
    font-family: var(--bodyfont);
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    line-height: 1.2;
    text-transform: uppercase;
  }
  #date-filter select {
    width: 176px !important;
    height: 34px !important;
    padding: 0 28px 0 9px;
    border: 1px solid #c6d6e6 !important;
    border-radius: 6px;
    background: #f8fbfe;
    color: #102d49;
    font-family: "Segoe UI", system-ui, -apple-system, sans-serif !important;
    font-size: 0.82rem !important;
    box-shadow: inset 0 1px 1px rgba(0,0,0,0.03);
    outline: none;
  }
  #date-filter .filter-row-primary .filter-field,
  #date-filter .filter-row-primary select {
    width: 220px !important;
  }
  #date-filter .filter-row-secondary .filter-field,
  #date-filter .filter-row-secondary select {
    width: 190px !important;
  }
  #date-filter .filter-static {
    min-height: 34px;
    display: flex;
    align-items: center;
    padding: 0 10px;
    border: 1px solid #d8e4f0;
    border-radius: 6px;
    background: #f8fbfe;
    color: #102d49;
    font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
    font-size: 0.82rem;
    font-weight: 700;
  }
  #date-filter select:focus {
    border-color: #f26522 !important;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(242,101,34,0.16);
  }
  #date-filter br {
    display: none;
  }
  @media (max-width: 760px) {
    #date-filter .filter-row {
      flex-wrap: wrap;
      justify-content: flex-start;
    }
    #date-filter .filter-row-primary .filter-field,
    #date-filter .filter-row-primary select,
    #date-filter .filter-row-secondary .filter-field,
    #date-filter .filter-row-secondary select {
      width: 100% !important;
    }
    #date-filter .filter-field {
      flex: 1 1 100%;
    }
  }
  body{
    text-align:center;
    padding-top: 86px;
  }
  #director p{
    margin-bottom: 10px;
    color: #000000;
  }
  #director{
    display: none;
    line-height:120%;
  }
  #col1, #col2, #col3{
    width:305px;
    padding:0px;
    float:left;
    display:block;
    margin:10px 0px 10px 0px;
  }
  #col1{
    padding-right:10px;
  }
  #col2{
    border-left: 2px #e3e3e3 solid;
    border-right: 2px #e3e3e3 solid;
    padding:0px 10px;
  }
  #col3{
    padding-left:10px;
  }
  
  .dir-resources {
    text-align: left;
    margin: 1.5em 0 2.5em;
    font-size: 1.1em;
  }
  
  .dir-resources-col-wrapper {
    display: flex;
    justify-content: space-evenly;
  }
  
  h2,
  .dir-resources h4 {
  }
  
  h2 {
    font-size: 2.5em;
    margin-bottom: .5em;
    font-family: var(--company);
    color: var(--pb_orange);
    text-align: center;
  }
  
  .dir-resources h4 {
    margin-bottom: .75em;
    font-family: var(--bodyfont);
    font-weight: bold;
    color: #222;
  }
  
  .dir-resources h4:after {
    display: block;
    content: '';
    height: 5px;
    width: 28%;
    background: var(--pb_orange);
  }
  
  .dir-resources-col {
    width: 25%;
  }
  
  .dir-resources li {
    line-height: 1.2;
    margin-bottom: .625em;
  }
  
  .dir-resources img {
    width: 14px;
    height: 14px;
    margin-right: 7px;
  }
  
  .li-desc {
    font-size: .75em;
    font-style: italic;
  }
 

.sa-wrap {
  max-width: 1100px;
  margin: 1.5rem auto;
  padding: 0 1rem;
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
}
.sa-wrap h3.sa-title {
  font-size: 1.125rem;
  color: #F26522;
  margin: 0 0 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #F26522;
}
.sa-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.75rem;
}
@media (min-width: 600px) {
  .sa-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 900px) {
  .sa-grid { grid-template-columns: repeat(3, 1fr); }
}
.sa-box {
  background: #fff;
  border: 1px solid #E5E7EB;
  border-radius: 8px;
  padding: 0.875rem 1rem;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.sa-box h4 {
  font-size: 0.6875rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #9CA3AF;
  margin: 0 0 0.5rem;
}
.sa-box a {
  display: inline-block;
  font-size: 0.78125rem;
  padding: 0.2rem 0.45rem;
  margin: 0.125rem;
  background: #F9FAFB;
  border: 1px solid #E5E7EB;
  border-radius: 4px;
  color: #2563EB;
  text-decoration: none;
  white-space: nowrap;
  transition: all 0.2s ease;
}
.sa-box a:hover {
  background: #EFF6FF;
  border-color: #2563EB;
  color: #2563EB;
}
#pb-admin-nav {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 100;
}
#pb-admin-nav .pb-admin-nav-inner {
  max-width: 1010px;
  margin: 0 auto;
}
#pb-admin-nav .pb-admin-nav-left {
  text-align: left;
}
.pb-admin-subnav {
  position: fixed;
  top: 46px;
  left: 50%;
  z-index: 99;
  width: 1010px;
  margin: 0;
  transform: translateX(-50%);
  padding: 8px 0;
  line-height: 1.2;
  min-height: 0;
}
</style>
<a name="top"></a>
<?php
show_admin_menu();
?>
</table>
<?php
$now = date("m-d");
if ($now >= "05-15" && $now <= "08-15") :
?>
<div class="dir-resources">
  
  <h2>Director Stuff</h2>
 
  <div class="dir-resources-col-wrapper">
    <div class="dir-resources-col dir-resources-external">
    

      <h4>External Resources</h4>
      <ul>
        <li class="li-prezi"><a href="https://prezi.com/dashboard/next/#/folder/2e3e45cf1719486ea2b40358cef7948c/all" target="_blank">
          <img src="/db/admin/img/admin_home_icons/prezi.png">Prezi</a></li>
        <li class="li-quizizz"><a href="https://pb4.us/camptrivia" target="_blank">
          <img src="/db/admin/img/admin_home_icons/quizizz.png">Quizizz</a> | <a href="https://jeopardylabs.com/account/home/?order_flag=1&order_by=title" target="_blank">Jeopardy</a></li>
        <li class="li-quizizz"><a href="/db/admin/friday_trivia_admin.php" target="_blank">
          <img src="/db/admin/img/admin_home_icons/quizizz.png"><span style="color: red;">*NEW!</span> PB Trivia</a></li>
        <li class="li-slack"><a href="https://planetbravo.slack.com" target="_blank">
          <img src="/db/admin/img/admin_home_icons/slack.png">Slack</a></li>
        <li class="li-spotify"><a href="https://open.spotify.com" target="_blank">
          <img src="/db/admin/img/admin_home_icons/spotify.png">Spotify</a></li>
        <li class="li-classroom"><a href="https://lessonbook.org/" target="_blank">
          <img src="/db/admin/img/admin_home_icons/moodle.png">LessonBook</a></li>
        <li class="li-photos"><a href="https://photos.google.com/" target="_blank">
          <img src="/db/admin/img/admin_home_icons/photos.png">Google Photos</a></li>
		  
		  <li class="li-photos"><a target="_blank" href="http://www.planetbravo.com/blg">
		  <img src="/db/admin/img/admin_home_icons/photos.png">BravoBlog Uploader</a></li>
		  
		  
        <li class="li-timer"><a href="https://www.google.com/search?q=online+timer&rlz=1C5CHFA_enUS778US779&oq=online+timer&aqs=chrome..69i57j0l7.1243j0j9&sourceid=chrome&ie=UTF-8" target="_blank">
          <img src="/db/admin/img/admin_home_icons/timer.png" title="timer by Gregor Cresnar from the Noun Project">Online timer</a></li>
		   <li class="li-pb"><a href="AfternoonGameWheel.exe">
          <img src="/db/admin/img/admin_home_icons/pb.png">Game Wheel.exe</a></li>
        <li class="li-pb"><a href="SoundMachine.exe">
          <img src="/db/admin/img/admin_home_icons/pb.png">Sound Machine.exe</a></li>
        <li class="li-pb"><a href="https://www.planetbravo.com/braingames">
          <img src="/db/admin/img/admin_home_icons/pb.png"><span style="color: red;">*NEW!</span> Brain Games!</a></li>
        <li class="li-pb"><a href="https://www.planetbravo.com/surprise">
          <img src="/db/admin/img/admin_home_icons/pb.png"><span style="color: red;">*NEW!</span> Surprise!</a></li>
      </ul>
    </div>


    <div class="dir-resources-col dir-resources-duties">
 <h4>Training</h4>
      <ul>
        <li class="li-docs"><img src="/db/admin/img/admin_home_icons/pdf.png"> <a href="https://drive.google.com/file/d/1vR42y8WediPUjkdKVLIRxOOLguucb1VT/view?usp=sharing" target="_blank">Setup Week Roles</a></li>
		  <li class="li-docs"><img src="/db/admin/img/admin_home_icons/docs.png"> <a href="https://docs.google.com/document/d/1DGqmFsKNA_xFNAFTYEX9nh4lAZxiOarLIDWvo3RwZxA/edit?usp=sharing" target="_blank">Director Setup Checklist</a></li>
		 <!--    <li class="li-docs"><img src="/db/admin/img/admin_home_icons/docs.png"><a href="#" target="_blank">
          Staff Setup Roles</a></li>-->
      </ul>	
	<h4>Daily Duties</h4>
      <ul>

<li class="li-docs"><a href="https://docs.google.com/document/d/1ggsIElXTnUo5uHwI32v-9A527HWxiyxnGjO6-zYJTCE/edit#" target="_blank">
          <img src="/db/admin/img/admin_home_icons/docs.png">Ref Guide — Directors</a></li>

        <li class="li-docs"><a href="https://docs.google.com/document/d/15aXJrhLTIbioKQ9eXHTTZY1qeungJIF9eozT4e2Ut-U/edit?usp=sharing" target="_blank">
          <img src="/db/admin/img/admin_home_icons/docs.png">Director Daily Checklist</a></li>
		 
		  <li class="li-docs"> <a href="https://docs.google.com/document/d/1Z5D71byMFIDFC7MmRNIR42UYMJsg1wp3N_eDRv4M7dM/edit?tab=t.0" target="_blank"><img src="/db/admin/img/admin_home_icons/docs.png">Staff Daily Roles</li>
		  
		  <li class="li-docs"> <a href="https://docs.google.com/document/d/1C5Pbg963UtkGHRGgR2dUBN9tHFkHm3Pyb5beQmw8OkI/edit?usp=sharing" target="_blank"><img src="/db/admin/img/admin_home_icons/docs.png">Extended Care</li>
		  
		  
		  
		 
		  
		  <li class="li-pb"><a href="https://www.planetbravo.com/injury/" target="_blank">
          <img src="/db/admin/img/admin_home_icons/pb.png">Report Injuries</a></li>
		  <li class="li-pb"><a href="https://www.planetbravo.com/discipline/" target="_blank">
          <img src="/db/admin/img/admin_home_icons/pb.png"><span style="color: red;">*NEW!</span> Report Discipline</a></li>
		  <li class="li-pb"><a href="https://www.planetbravo.com/staff_issues/" target="_blank">
          <img src="/db/admin/img/admin_home_icons/pb.png"><span style="color: red;">*NEW!</span> Report Staff Issue</a></li>
		  
		   <!-- <li class="li-pb"><a href="https://docs.google.com/forms/d/e/1FAIpQLSePkawCxw-_k5QskWI30snMOX3STI2ldduZH-pnUHFTJ97liA/viewform?usp=sf_link" target="_blank">
          <img src="/db/admin/img/admin_home_icons/pb.png">Report COVID Case</a></li>-->
		  
<!--		  <li class="li-pb"><a href="https://docs.google.com/forms/d/e/1FAIpQLSfbXZfxPoc7yQ_ukoM9OO7dqhOSIJeGcqQYdm2Wey8bfmG0vA/viewform?usp=sf_link" target="_blank">
          <img src="/db/admin/img/admin_home_icons/pb.png">Report COVID Test Use</a></li>-->
		  		  		  		  
		   <li class="li-pb"><a href="https://docs.google.com/forms/d/e/1FAIpQLSeIVdFzoiq8i2KYEzaEKtJGwroQIksFELydoxG5J3xRbXGhMA/viewform?usp=sf_link" target="_blank">
          <img src="/db/admin/img/admin_home_icons/pb.png">Supply Request Form</a></li>
		  
		<li class="li-drive"><a href="https://docs.google.com/document/d/1PxpZD1hHFR0kyIaKt9S0xU7OLOCGgUTTJflbA7nMvYA/edit?tab=t.0" target="_blank">
          <img src="/db/admin/img/admin_home_icons/drive.png">Daily Schedule</a></li>
			  
		  <li class="li-drive"><a href="https://docs.google.com/document/d/18W1Y7YrHPLj4aFSLS60A5IvAED2o4sKWT6CUvUifZro/edit?usp=sharing" target="_blank">
          <img src="/db/admin/img/admin_home_icons/drive.png">Friday Schedule</a></li>
		  
	
       <!-- <li class="li-sites"><a href="http://pb4.us/ngt" target="_blank">
          <img src="/db/admin/img/admin_home_icons/sites.png">NGT Activities Site</a></li>-->
      </ul>
    </div>


    <div class="dir-resources-col dir-resources-internal">
      <h4>Internal Resources</h4>
      <ul>
     
		  <li class="li-drive"><a href="https://pb4.us/dirfolder" target="_blank">
          <img src="/db/admin/img/admin_home_icons/drive.png">Director Folder</a></li>
		  <li class="li-drive"><a href="https://www.pb4.us/staff26" target="_blank">
          <img src="/db/admin/img/admin_home_icons/drive.png">Staff Rosters</a></li>
		  <li class="li-drive"><a href="https://docs.google.com/spreadsheets/d/1S_sbIXKQDZcX2wtJ-bVebZpzEGO9xKs08imbxaZ_Akw/edit?pli=1&gid=578236029#gid=578236029" target="_blank">
          <img src="/db/admin/img/admin_home_icons/drive.png">CITs/Interns</a></li>
		  
		<!--  <li class="li-docs"><img src="/db/admin/img/admin_home_icons/slides.png"><a href="https://pb4.us/stemdirss" target="_blank">
          STEM Director Slides</a></li>-->
		  <li class="li-docs"><a href="https://docs.google.com/document/d/1bZBsHfdzPFHByg19NfBCZFpGoDQlHUp2tehL_FHel2k/edit?usp=sharing" target="_blank">
          <img src="/db/admin/img/admin_home_icons/docs.png">Director Handbook</a>
          <!--<br><span class="li-desc">via LessonBook</span></li>-->

		  <li class="li-drive"><a href="https://docs.google.com/document/d/1bruVvKp1CT2-sm2fRqzFB1mwNFrToBUfIkGijhCUDLs/edit?usp=sharing" target="_blank">
          <img src="/db/admin/img/admin_home_icons/docs.png"><span style="color: red;">*NEW!</span> Policies/Procedures</a></li>

		  
		  <li class="li-docs"><a href="https://docs.google.com/document/d/19RP83ham9KhMoElGpDjbh4wAsTUE0m2QPclfYp129dM/edit?usp=sharing" target="_blank">
          <img src="/db/admin/img/admin_home_icons/docs.png">Staff Handbook</a>
          <!--<br><span class="li-desc">via LessonBook</span></li>-->
		 
		  
		  
		  <li class="li-drive"><a href="https://docs.google.com/presentation/d/1MCUh0qSrF9Y8CQcRgCBzeGvtvlcVC3LVZjA-eSv5tzU/edit?slide=id.g3369d36e78c_1_0#slide=id.g3369d36e78c_1_0" target="_blank">
          <img src="/db/admin/img/admin_home_icons/slides.png">STEM Daily Slides</a></li>
		  
		  <!--<li class="li-drive"><a href="https://docs.google.com/presentation/d/191FKCRJttE-0-4qjX8xTu0bCEqrZ9rEb7E_zBD0meb0/edit?pli=1#slide=id.g2e1f4711ae3_0_583" target="_blank">
          <img src="/db/admin/img/admin_home_icons/drive.png">STEM Friday</a></li>-->
		  
		  
		  
       
		 <!-- <li class="li-docs"><a href="http://pb4.us/ocquickref" target="_blank">
          <img src="/db/admin/img/admin_home_icons/docs.png">PB@Home Counselors</a></li>
        <li class="li-pb"><a href="https://www.planetbravo.com/handbook/" target="_blank">
          <img src="/db/admin/img/admin_home_icons/pb.png">PB@Home Handbook</a></li>-->
        <!--<li class="li-docs"><a href="http://pb4.us/dirinfo" target="_blank">
          <img src="/db/admin/img/admin_home_icons/docs.png">Important Director Info</a>
          <br><span class="li-desc">Not updated for PB@Home</span></li> -->
         <li class="li-slides"><a href="http://pb4.us/unplugged" target="_blank">
          <img src="/db/admin/img/admin_home_icons/slides.png">Fun Challenge Slides</a></li>
        <li class="li-docs"><a href="http://pb4.us/ngt-doc" target="_blank">
          <img src="/db/admin/img/admin_home_icons/docs.png">NGT Activities Doc</a></li>
  <li class="li-docs"><a href="https://docs.google.com/document/d/1KND6Aik5TDyUfuGRJtxyxxVMUg_oy9dtAc7QHURwFPc/edit?usp=sharing" target="_blank">
          <img src="/db/admin/img/admin_home_icons/docs.png">Ice Breakers</a></li>
		  
		  <li class="li-docs"><a href="https://docs.google.com/document/d/1sQmetW2kp3zBbBs2sFHS6SKUg87iIz4dGNqreSEvPa0/edit?usp=sharing" target="_blank">
          <img src="/db/admin/img/admin_home_icons/docs.png">Outdoor Games</a></li>
		  <li class="li-docs"><a href="#" target="_blank">
          <img src="/db/admin/img/admin_home_icons/docs.png">Jamboree Doc</a></li>
        
       
      </ul>
    </div>
    
  </div>
</div>


<?php endif; ?>

<!-- OLD DIRECTOR RESOURCES BELOW -->
<!-- CSS set to display:none for #director at top of this page -->
<div id="director">
  <div id="col1">
    <h4>Director Dailies</h4> 
    <h5>Camper Checklists - Week of Aug 5, 2019</h5>
    <ul>  
    <li><a target="_blank" href="Checklist-BH-080519.pdf">Beverly Hills</a></li> 
    <li><a target="_blank" href="Checklist-IR-080519.pdf">Irvine</a></li>
    <li><a target="_blank" href="Checklist-MB-080519.pdf">Manhattan Beach</a></li> 
    <li><a target="_blank" href="Checklist-SM-080519.pdf">Santa Monica</a></li> 
    <br />
    <li><a target="_blank" href="Checklist-SAMPLE.pdf">SAMPLE (account works too)</a></li>
    <br />
    </ul>
    <h5>Upload Media</h5>
    <ul>
    <li><a target="_blank" href="https://photos.google.com/shared">Daily Photo Uploader</a>
    <li><a target="_blank" href="http://www.planetbravo.com/blg">BravoBlog Uploader</a>
    <li><a href="https://goo.gl/mYNEBO" target="_blank" style="font-size: 10px">(How to Upload Pics/Video)</a>
    </ul>
    <p>&nbsp;</p>
    <h5><span style="text-decoration: none">Weekly Staff Roles</span></h5> 
    <ul>
    <li><a target="_blank" href="https://docs.google.com/document/d/15z6ohVyhTQfMUxQoEUOnO69I-J73_cANuSg9BOIZtCs/edit">Google Doc</a><br /><br />
    </li>
    </ul>
  </div>
  
  <div id="col2">
    <h4>Director Duties</h4>
    <p><br />
    <a target="_blank" href="https://drive.google.com/file/d/0B8op95dudqjINW1ZZTZzbHhHMkpTY3BnaWFDTzNLZUdWbmow/view?usp=sharing ">Director Checklist</a></p><br>
    <p><a href="https://www.planetbravo.com/injury/" target="_blank">Report Injuries Here</a></p><br>
    <p><a href="https://www.planetbravo.com/discipline/" target="_blank">Report Discipline Issues Here</a></p><br>
    <p><a href="http://www.prezi.com" target="_blank">Prezi Account<br>
    Login with team@planetbravo.com</a></p><br>
    <p><a target="_blank" href="http://www.planetbravo.com/trivia/admin_trivia.php">Trivia Time</a></p><br>
    <p><a target="_blank" href="https://drive.google.com/folderview?id=0B8op95dudqjIaVQtUGpjb0t1SzQ&usp=sharing">Directors Folder (Drive)</a></p><br><br><br><br>
  </div>
  
  <div id="col3">
    <h4>Director Fun</h4>
    <p><br />
    <a href="https://docs.google.com/document/d/1K9x-WmT_bzE9GsWkB5PnoXOAa1zHiTOdsXf4uogb8J0/edit" target="_blank">Director Handbook!</a><br>
    <a target="_blank" href="http://www.planetbravo.com/campgames.html">Pick a Daily Game to Play!</a><br>
    <a href="AfternoonGameWheel.exe">Afternoon GameWheel (exe)</a><br>
    <a href="SoundMachine.exe">Sound Machine 2010 (exe)</a><br>
    <a href="http://www.online-stopwatch.com/countdown/" target="_blank">Countdown Timer</a><br>
    <p><br /><h3>Snack Sheets</h3>
    <a target="_blank" href="https://docs.google.com/spreadsheets/d/1rM5Gyvq7BQjU90fJQuUq_AVEPrgwn12kjq-oYCze14g/edit#gid=528607439">Berkeley</a><br>
    <a target="_blank" href="https://docs.google.com/spreadsheets/d/1rM5Gyvq7BQjU90fJQuUq_AVEPrgwn12kjq-oYCze14g/edit#gid=1804014560">Beverly Hills</a><br>
    <a target="_blank" href="https://docs.google.com/spreadsheets/d/1rM5Gyvq7BQjU90fJQuUq_AVEPrgwn12kjq-oYCze14g/edit#gid=596660113">Encino</a><br>
    <a target="_blank" href="https://docs.google.com/spreadsheets/d/1rM5Gyvq7BQjU90fJQuUq_AVEPrgwn12kjq-oYCze14g/edit#gid=2094889686">Irvine</a><br>
    <a target="_blank" href="https://docs.google.com/spreadsheets/d/1rM5Gyvq7BQjU90fJQuUq_AVEPrgwn12kjq-oYCze14g/edit#gid=549340493">Manhattan Beach</a><br>
    <a target="_blank" href="https://docs.google.com/spreadsheets/d/1rM5Gyvq7BQjU90fJQuUq_AVEPrgwn12kjq-oYCze14g/edit#gid=1294250018">Marin</a><br>
    <a target="_blank" href="https://docs.google.com/spreadsheets/d/1rM5Gyvq7BQjU90fJQuUq_AVEPrgwn12kjq-oYCze14g/edit#gid=0">Pasadena</a><br>
    <a target="_blank" href="https://docs.google.com/spreadsheets/d/1rM5Gyvq7BQjU90fJQuUq_AVEPrgwn12kjq-oYCze14g/edit#gid=483746548">Santa Monica</a><br>
    </p>
  </div>
</div>

<!-- END OLD DIRECTOR RESOURCES -->




<div id="date-filter">
<div class="filter-title">Dashboard Filters <span>updates automatically</span></div>
<div class="filter-controls">
<?php
$admin = new Admin();
$SuperAdmin = new SuperAdmin();
if ($_REQUEST["location"] == "") {
  $home_location = $SuperAdmin->get_home_location($admin_id);
  $_REQUEST["location"] = $home_location;
}
$admin_locations = $SuperAdmin->get_admin_locations($admin_id);
$camp_courses = $SuperAdmin->get_current_camp_courses();
// new admin course type filter
$admin_course_types = array(1=>"TECH", 2=>"STEM");
$admin_stem_tech = $admin->get_admin_stem_tech($admin_id);
$start_dates = $admin->get_start_dates();
$selected_date = isset($_REQUEST['date']) ? $_REQUEST['date'] : '';
if ($selected_date === '') {
  $selected_date = $admin->get_auto_admin_start_date($start_dates);
  $_REQUEST['date'] = $selected_date;
}
$selected_location = isset($_REQUEST['location']) ? $_REQUEST['location'] : 0;
$selected_course = isset($_REQUEST['course']) ? $_REQUEST['course'] : 0;
$selected_course_type = isset($_REQUEST['course_type']) ? $_REQUEST['course_type'] : 0;
$selected_enrollment = isset($_REQUEST['enrollment']) ? $_REQUEST['enrollment'] : '';
$enroll_options = array(
  '0' => 'Empty (0 enrolled)',
  '1' => '1 enrolled',
  '2' => '2 enrolled',
  '3' => '3 enrolled',
  'full' => 'Full',
);
asort($admin_locations);
asort($camp_courses);

?>
<div class="filter-row filter-row-primary">
  <label class="filter-field">Week:
    <select name="start_date" id="start_date" onchange="setDate();">
      <option value="0">-- All Dates --</option>
      <?php foreach ($start_dates as $start_date_row):
        $course_start_date = $start_date_row['course_start_date'];
        $course_start_date_format = $admin->format_date($course_start_date);
      ?>
      <option value="<?php echo htmlspecialchars($course_start_date); ?>"<?php if ($course_start_date == $selected_date) echo ' selected'; ?>><?php echo htmlspecialchars($course_start_date_format); ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label class="filter-field">Location:
    <select name="location" id="location" onchange="send_filter_form();">
      <option value="0">-- All Locations --</option>
      <?php foreach ($admin_locations as $location_id => $school_name): ?>
      <option value="<?php echo htmlspecialchars($location_id); ?>"<?php if ($location_id == $selected_location) echo ' selected'; ?>><?php echo htmlspecialchars($school_name); ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label class="filter-field">Course:
    <select name="course" id="course" onchange="send_filter_form();">
      <option value="0">-- All Courses --</option>
      <?php foreach ($camp_courses as $course_id => $course_name): ?>
      <option value="<?php echo htmlspecialchars($course_id); ?>"<?php if ($course_id == $selected_course) echo ' selected'; ?>><?php echo htmlspecialchars($course_name); ?></option>
      <?php endforeach; ?>
    </select>
  </label>
</div>
<div class="filter-row filter-row-secondary">
  <?php if (!$admin_stem_tech): ?>
  <label class="filter-field">Course Type:
    <select name="course_type" id="course_type" onchange="send_filter_form();">
      <option value="0">-- All Course Types --</option>
      <?php foreach ($admin_course_types as $id => $course_type): ?>
      <option value="<?php echo htmlspecialchars($id); ?>"<?php if ($id == $selected_course_type) echo ' selected'; ?>><?php echo htmlspecialchars($course_type); ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <?php else: ?>
  <div class="filter-field">
    <span class="filter-label">Course Type:</span>
    <input type="hidden" id="course_type" value="<?php echo (int)$admin_stem_tech; ?>">
    <span class="filter-static"><?php echo $admin_stem_tech == 1 ? 'STEM' : 'TECH'; ?></span>
  </div>
  <?php endif; ?>
  <label class="filter-field">Enrollment:
    <select name="enrollment" id="enrollment" onchange="send_filter_form();">
      <option value="">-- All --</option>
      <?php foreach ($enroll_options as $value => $label): ?>
      <option value="<?php echo htmlspecialchars($value); ?>"<?php if ((string)$selected_enrollment === (string)$value) echo ' selected'; ?>><?php echo htmlspecialchars($label); ?></option>
      <?php endforeach; ?>
    </select>
  </label>
</div>
</div>
</div>

<script type="text/javascript">
// Re-wrap filter functions to preserve enrollment state
var _enrollment = document.getElementById('enrollment') ? document.getElementById('enrollment').value : '';

function send_form() {
  document.getElementById('location_filter').action = "admin_home.php?date=<?=htmlspecialchars($_REQUEST['date'])?>&location="+document.getElementById("location").value+"&course="+document.getElementById("course").value+"&course_type="+document.getElementById("course_type").value+"&enrollment="+document.getElementById("enrollment").value+"#date-filter";
  window.location = document.getElementById('location_filter').action;
}

function send_form_course() {
  document.getElementById('course_filter').action = "admin_home.php?date=<?=htmlspecialchars($_REQUEST['date'])?>&location="+document.getElementById("location").value+"&course="+document.getElementById("course").value+"&course_type="+document.getElementById("course_type").value+"&enrollment="+document.getElementById("enrollment").value+"#date-filter";
  window.location = document.getElementById('course_filter').action;
}

function send_form_course_type() {
  document.getElementById('course_type_filter').action = "admin_home.php?date=<?=htmlspecialchars($_REQUEST['date'])?>&location="+document.getElementById("location").value+"&course="+document.getElementById("course").value+"&course_type="+document.getElementById("course_type").value+"&enrollment="+document.getElementById("enrollment").value+"#date-filter";
  window.location = document.getElementById('course_type_filter').action;
}

function send_filter_form() {
  var date = document.getElementById("start_date").value;
  var location = document.getElementById("location").value;
  var course = document.getElementById("course").value;
  var courseType = document.getElementById("course_type").value;
  var enrollment = document.getElementById("enrollment").value;
  window.location = "admin_home.php?date=" + encodeURIComponent(date) + "&location=" + encodeURIComponent(location) + "&course=" + encodeURIComponent(course) + "&course_type=" + encodeURIComponent(courseType) + "&enrollment=" + encodeURIComponent(enrollment) + "#date-filter";
}

function setDate() {
  var date = document.getElementById("start_date").value;
  document.cookie = "admin_date=" + date;
  send_filter_form();
}
</script>
<?php
// display list of locations with all courses at that location
$locations = $admin->view_locations();
$date = $_REQUEST['date'];
foreach ($locations as $location) {
    $location_id = $location['location_id'];
    $sumext_array[$location_id] = $admin->get_total_camp_report_by_location_ec_count($location_id,$date);
    if (!array_key_exists($location_id,$admin_locations)) continue;
    if (isset($_REQUEST["location"]) && $_REQUEST["location"]) if ($_REQUEST["location"] != $location_id) continue;
    $course_location_id = $courses[$i]['course_location_id'];
    $school_name = $location['school_name'];
    $active = $location['active'];
	if ($date) {
		$courses = $admin-> get_courses_by_location_id_and_start_date($location_id, $date);
	} else {
		$courses = $admin-> get_courses_by_location_id_for_adminhome($location_id);
}
foreach ($courses as $key => $value) {
  $course_location_id_value = $value['course_location_id'];
    if (isset($_REQUEST["course"]) && $_REQUEST["course"])
      if ($_REQUEST["course"] != $value["course_id"]) unset($courses[$key]);
}
	if ($active == 'y') {
    print <<< EOD
    <h2>$school_name</h2>
    <body style="line-height: 15px;">
    
EOD;

$pb_at_home_course = 0;
if ($location_id == 112) { $location_id_converted = 124; $pb_at_home_course = 1; }
if ($location_id == 111) { $location_id_converted = 126; $pb_at_home_course = 1; }
if ($location_id == 110) { $location_id_converted = 123; $pb_at_home_course = 1; }
if ($location_id == 109) { $location_id_converted = 125; $pb_at_home_course = 1; }
if ($location_id == 108) { $location_id_converted = 122; $pb_at_home_course = 1; }
if ($location_id == 107) { $location_id_converted = 121; $pb_at_home_course = 1; }
if ($location_id == 106) { $location_id_converted = 120; $pb_at_home_course = 1; }
if ($location_id == 105) { $location_id_converted = 119; $pb_at_home_course = 1; }
if ($location_id == 104) { $location_id_converted = 118; $pb_at_home_course = 1; }
if ($location_id == 103) { $location_id_converted = 117; $pb_at_home_course = 1; }

		print <<< EOD
	[<a class="blue" href="admin_view_daily_sign_in.php?date=$date&location_id=$location_id" target=_blank>Sign-in</a>]
	[<a class="blue" href="admin_select_nametags_html.php?date=$date&location_id=$location_id" target=_blank>Name Tags</a>]
	[<a class="blue" href="admin_select_nametag_awards.php?date=$date&location_id=$location_id" target=_blank>Award Tags</a>]
	[<a class="blue" href="admin_view_contact_sheet.php?date=$date&location_id=$location_id" target=_blank>Medical</a>]
	[<a target="_new" class="blue" href="admin_view_total_camp_report_locations.php?date=$date&location_id=$location_id">Camp Report</a>]
	[<a class="blue" href="admin_view_t_shirts.php?date=$date&location_id=$location_id" target=_blank>T-Shirts</a>]
	[<a class="blue" href="admin_count_t_shirt.php?date=$date&location=$location_id" target=_blank>T-Count</a>]
EOD;
	    if ($date)
		print <<< EOD
	[<a class="blue" href="admin_view_pizza_count.php?date=$date&location_id=$location_id" target=_blank>PizzaCt</a>]
	[<a class="blue" href="admin_view_pizza.php?date=$date&location_id=$location_id" target=_blank>Pizza</a>]
EOD;
		print <<< EOD
<br/>	
	[<a class="blue" href="admin_view_awards.php?date=$date&location_id=$location_id" target=_blank>Your Kids' Awards</a>]
	[<a class="blue" href="admin_view_no_awards.php?date=$date&location_id=$location_id" target=_blank>Your Kids Missing Awards</a>]
	[<a class="blue" href="admin_view_awards_by_cohort.php?date=$date&location_id=$location_id" target=_blank>Awards by Teacher</a>]
	[<a class="blue" href="admin_view_no_awards_by_cohort.php?date=$date&location_id=$location_id" target=_blank>No Awards by Teach</a>]
<br/>
	[<a class="blue" href="admin_view_prizes.php?date=$date&location_id=$location_id" target=_blank>Prizes</a>]
	[<a class="blue" href="admin_view_points.php?date=$date&location_id=$location_id" target=_blank>Points</a>]
	[<a>Pub</a>]
	[<a class="blue" href="admin_view_emails.php?date=$date&location_id=$location_id" target=_blank>Emails</a>]
	[<a class="blue" href="admin_view_emails_with_rewards.php?date=$date&location_id=$location_id" target=_blank>Emails With Awards</a>]
	[<a class="blue" href="admin_view_teachers_report.php?date=$date&location_id=$location_id" target=_blank>Teachers</a>]
	[<a class="blue" href="admin_count_new_students.php?date=$date&location=$location_id" target=_blank>New-Students-Count</a>]
		
EOD;

	print <<< EOD
	<ul><br>
EOD;
$sum = 0;
$sumtotal = 0;
$sumperc = 0;
$sumleft = 0;
$sumtotal_tech = 0;
$sumtotal_stem = 0;
$course = new Course();
$course_obj = $course;
$WaitList = new AdminWaitList();
$sumext = 0;

		if (
			 $location_id != 117 
		&& $location_id != 118
		&& $location_id != 119 
		&& $location_id != 120 
		&& $location_id != 121 
		&& $location_id != 122 
		&& $location_id != 123 
		&& $location_id != 124 
		&& $location_id != 125 
		&& $location_id != 126
		) {

foreach ($courses as $course) {
	if (!$admin_stem_tech) {
		$show_course_type = $_REQUEST['course_type'];
		if ($show_course_type) {
			if ($course["course_type"] == "j" && $show_course_type == 1) continue;
			if ($course["course_type"] == "s" && $show_course_type == 2) continue;
		}
	} else {
		if ($course["course_type"] == "j" && $admin_stem_tech == 2) continue;
		if ($course["course_type"] == "s" && $admin_stem_tech == 1) continue;
	}
  $course_location_id = $course['course_location_id'];
  $course_name = $course['course_name'];
	if (preg_match("/Grades/", $course_name)) $course_name = substr($course_name,12,strlen($course_name));
  $startdate = $course['course_start_date'];
  $max_enrollment = $course['max_enrollment'];
  $active_course = $course['active'];
  $start_grade = $course['start_grade'];
  $end_grade = $course['end_grade'];
  $currently_enrolled = $admin->get_enrolled($course_location_id);
  $online_count = $admin->get_enrolled_online($course_location_id);
  $enrollment_format = $course_obj->enrollment_format($currently_enrolled, $max_enrollment);
  $waitlist_count = $WaitList->find_count($course_location_id);

 // ---- NEW: Enrollment filter ----
$enroll_filter = $_REQUEST['enrollment'];
if ($enroll_filter !== '' && $enroll_filter !== null) {
    if ($course["course_id"] == 425) continue;
    if ($enroll_filter === 'full') {
        if ($currently_enrolled < $max_enrollment) continue;
    } else {
        if (intval($currently_enrolled) !== intval($enroll_filter)) continue;
    }
}
// ---- end enrollment filter ----

  if ($active_course == 'y') {
    $focus_count = 0;
    $focus_count = $WaitList->find_focus_count($course_location_id);
    if ($course["course_id"] != 425 && $course["course_id"] != 466 && $course["course_id"] != 5066) {
      $sum+= $currently_enrolled;
      $sumtotal+= $max_enrollment;
      if ($course["course_type"] == "s") $sumtotal_tech += $max_enrollment;
      if ($course["course_type"] == "j") $sumtotal_stem += $max_enrollment;
      if (!empty($sumtotal))
        $sumperc = round($sum/$sumtotal*100);
      else
        $sumperc = 0;
      $sumleft = $sumtotal-$sum;	  
    }
    $waitlist_contacted = $WaitList->waitlist_contacted($course_location_id);
    $late_cancelled = $WaitList->late_cancelled($course_location_id);
    $pb_course = $WaitList->pb_course($course_location_id);
    $teacher_nickname = $course_obj->get_course_teacher_nickname($course_location_id);
    if ($course["course_type"] == "j")
      $course_type = "<font color=green>STEM</font>";
    else if ($course["course_type"] == "s")
      $course_type = "<font color=blue>TECH</font>";
    $course_name_show = $course_name;
    if (substr(strtolower($course_name),0,5) == "stem ") $course_name_show = substr($course_name,5,strlen($course_name));
    if (substr(strtolower($course_name),0,5) == "tech ") $course_name_show = substr($course_name,5,strlen($course_name));
    print '<div align="center">
<table>
  <tr style="'.$enrollment_format.'">
    <td width="340">   <!--'.$course_location_id.'-->    '.$course_type.' '.$course_name_show.' - '.$teacher_nickname.'</td> 
    <td width="100">'.$startdate.'</td> 
    <td width="82"> [<a class="blue" href="admin_view_course_students.php?course_location_id='.$course_location_id.'">view all</a>]';
	
    if ($focus_count)
      echo '('.$focus_count.') ';
    else
      echo ' ';
    echo '</td><td width="20"></td> <td width="175">[<a class="blue" href="admin_view_course_list.php?course_location_id='.$course_location_id.'&course_start_date='.$date.'" target=_blank>Pza/Shrt</a>] [<a class="blue" href="admin_course_teachers.php?clid='.$course_location_id.'" target=_blank>Tch</a>] [<a class="blue" href="admin_view_points_course.php?date='.$date.'&course_location_id='.$course_location_id.'">Pts</a>]  [<a class="blue" href="admin_edit_course_details.php?course_location_id='.$course_location_id.'">Edit</a>]</td> 
 
    <td width="120">';
    if ($online_count) echo "<font color=green><b>($online_count)</b></font> ";
    $currently_enrolled -= $online_count;
    echo $currently_enrolled.' / '.$max_enrollment;
    if ($waitlist_count) echo '&nbsp;|&nbsp;<a href="/db/admin/admin_view_waitlist.php#'.$course_location_id.'">('.$waitlist_count.')</a>';
    if ($waitlist_contacted) echo ' <font color=blue><b>*</b></font>';
    if ($late_cancelled) echo ' <font color=blue><b>+</b></font>';
    if ($pb_course) echo ' <font color=blue><b>$</b></font>';
    echo '</td>
  </tr>
</table>
</div>
';
  $sumext = $sumext_array[$location_id];
	}
}
	echo "
	<h4>$sum / $sumtotal = $sumperc%</h4>$sumleft seats left ";
	echo "<br />$sumtotal_tech TECH seats";
	echo "<br />$sumtotal_stem STEM seats";
	if ($sumext) {
    echo "<br />$sumext EC campers";
	}
	$in_course_options = $admin->get_in_course_options($location_id);
	unset($results);
	foreach ($in_course_options as $value) $results[$value['course_start_date']]++;

	foreach ($results as $key => $value) {
		if ($date && $date != $key) continue;
		$course_capacity = $admin->get_location_capacity($location_id, $key);
		echo '<br/><a href="/db/admin/admin_view_course_students_by_location_chosen.php?date='.$key.'&location='.$location_id.'">';
		if ($value >= $course_capacity) echo '<font color=red>';
		echo date('F j: ',strtotime($key)).$value.' camper';
		if ($value > 1) echo 's';
		if ($value >= $course_capacity) echo '</font>';
		echo '</a>';
		echo "&nbsp;&nbsp;[ <a target='_new' href='/db/admin/admin_view_course_students_by_location_chosen.php?date=$key&location=$location_id'>view all</a> ]";		
		echo "&nbsp;&nbsp;[ <a target='_new' href='/db/admin/admin_view_course_students_by_location_chosen_no_pod.php?date=$key&location=$location_id'>no pod</a> ]";		
		echo "&nbsp;&nbsp;[ <a target='_new' href='/db/admin/admin_view_course_students_by_location_chosen_no_teacher.php?date=$key&location=$location_id'>no class</a> ]";		
		echo '&nbsp;&nbsp;|&nbsp;&nbsp;<a href="/db/admin/admin_view_course_students_by_location_chosen_summary.php?date='.$key.'&location='.$location_id.'">';
		if ($value >= $course_capacity) echo '<font color=red>';
		echo 'see course count';
		if ($value > 1) echo 's';
		if ($value >= $course_capacity) echo '</font>';
		echo '</a>';
		echo '&nbsp;&nbsp;|&nbsp;&nbsp;<a target="_new" href="/db/admin/admin_view_course_students_by_location_chosen_summary_capacity.php?date='.$key.'&location='.$location_id.'">';
		if ($value >= $course_capacity) echo '<font color=red>';
		echo "edit capacity: <b>$course_capacity</b>";
		if ($value >= $course_capacity) echo '</font>';
		echo '</a>';
	}
  echo "
	
	</ul><br />	<br />
";
		} else {
			$location_teachers = $admin->get_teacher_location_details($location_id);
			foreach ($location_teachers as $value) {
				$date_selected = trim($_GET["date"]);
				if (!$date_selected) $date_selected = '';
				if ($date_selected && $date_selected != $value['course_start_date']) continue;				
				echo '<center><table>  <tr style="'.$enrollment_format.'"><td width="100">  <a target="_new" href="/db/admin/admin_view_course_students.php?course_location_id='.$value['course_location_id'].'&teacher_id='.$value['teacher_id'].'">'.$value['nickname'].'</a> </td> ';
				echo '<td width="250">'.$value['course_name'].' </td> ';
				echo '<td width="150">'.$value['course_start_date'].' </td> ';
				echo '<td width="75"><a target="_new" href="/db/admin/admin_view_course_students.php?course_location_id='.$value['course_location_id'].'&teacher_id='.$value['teacher_id'].'">view all</a>     </td> ';
				echo '<td width="75"><a target="_new" href="/db/admin/admin_view_points_course_by_week.php?date='.$date.'&location_id='.$location_id.'&course_location_id='.$value['course_location_id'].'&teacher_id='.$value['teacher_id'].'">Points</a>   </td> ';
				echo '<td width="75"><a target="_new" href="/db/admin/admin_edit_course_details.php?course_location_id='.$value['course_location_id'].'">Edit</a> </td> ';
				$student_count = $admin->get_teacher_location_students($location_id, $value['course_location_id'], $value['teacher_id']);
				echo '<td width="75">'.$student_count.' students<br/></td></tr></table>';
			}
			echo "<br/><br/>";
		}
	}
}

?>

<a name="sa"></a>
<hr />

<?php
if (
  $_SESSION["admin_id"] == 58    // Kaal
  || $_SESSION["admin_id"] == 2  // Danny
  || $_SESSION["admin_id"] == 242 // Chris
) {
?>
<div class="sa-wrap">
  <h3 class="sa-title">Super Administrator</h3>
  <div class="sa-grid">
  <div class="sa-box">
      <h4>Enrollment Watch</h4>
      <a target="_blank" href="admin_tally.php?camp=61&course_current=SORT_DESC">Courses Tallies</a>
	  <a target="_blank" href="location_courses.php?location=0">Courses by Location</a>
	  <a target="_blank" href="extended_care_data.php">Extended Care by Course</a>  
	  <a target="_blank" href="admin_view_course_cancellations.php">Cancellation Excuses</a>
	  <a target="_blank" href="admin_view_shopping_cart.php">Active Shopping Carts</a>
      <a target="_blank" href="admin_view_shopping_carts.php">Active Coupon Carts</a>
	<!--  <a target="_blank" href="receipt_email_preview.php">Receipt Email Preview</a>
	  <a target="_blank" href="receipt_email_live_preview.php">Live Receipt Preview</a> -->
	  <a target="_blank" href="admin_test_card_watch.php">Test Card Watch</a>
	  <a target="_blank" href="admin_student_change_watch.php">Student Change Watch</a>
	  <a target="_blank" href="duplicate_student_merges.php">Duplicate Student Merges</a>
	  <a target="_blank" href="report_registrations.php">Registration Report</a>
	  <a target="_blank" href="scholarship_review.php">Scholarship Review</a>

         <a target="_blank" href="shirt_orders.php">Shirt Orders</a>
         <a target="_blank" href="estimated_enrollments.php">Estimated Enrollments</a>
    </div>

    <div class="sa-box">
      <h4>Marketing Tools</h4>
	  <a target="_blank" href="admin_update_mailinglist.php">Update Mailing List</a>
	  <a target="_blank" href="admin_view_all_accounts.php">Compare ALL YTD</a>
	  <a target="_blank" href="admin_view_new_but_not_enrolled.php">New Accounts, No Enroll</a>
	  <a target="_blank" href="admin_view_potential_parents_db.php">Enrolled before, Not now</a>
	  <a target="_blank" href="admin_report_single_week_new_campers.php">Single Week New Campers</a>
	  <a target="_blank" href="students_done_for_summer.php?date=<?=$date?>">Students Done for Summer</a>
	  <a target="_blank" href="students_done_for_summer.php?date=<?=$date?>&location_id=<?=$location_id?>">Done for Summer by Location</a>
	  <a target="_blank" href="/db/admin/admin_view_publicity_all.php?date=<?=$date?>">How they heard about us</a>
	  <a target="_blank" href="report_referral_codes.php">Referral Codes Used</a>
	  <a target="_blank" href="admin_media_gallery_setup.php">Media Gallery Setup</a>
    </div>

    <div class="sa-box">
      <h4>Class Stats</h4>
         <a target="_blank" href="admin_home_history.php">Historical Admin Home</a>
      <a target="_blank" href="course_year_stats.php">Course Year Stats</a>
      <a target="_blank" href="course_teacher_emails.php">Course Teacher Emails</a>

      <a target="_blank" href="admin_equipment_totals.php">Equipment Needs</a>
	  <a target="_blank" href="admin_view_teachers_report_all.php?date=<?=$date?>">All Locations Teachers</a>

	  <a target="_blank" href="admin_moodle_enrollments.php">Moodle Teacher Sync</a>


	  <a target="_blank" href="admin_view_lone_students.php">Old/Young/Lone Students</a>
	  <a target="_blank" href="5th_camp_students.php">5th Camp Students</a>
      <a target="_blank" href="end_of_the_line_students.php">End of the Line Students</a>

	  <a target="_blank" href="partner_code_receivables.php">Partner Code Receivables</a>
	  <a target="_blank" href="bulk_add.php">Bulk Add Coupon Vouchers</a>
    </div>

	  <div class="sa-box">
      <h4>Weekly Reports</h4>
      <a target="_blank" href="admin_view_daily_sign_in_all.php?date=<?=$date?>" target="_blank">Daily Sign-In Sheet</a>
      <a target="_blank" href="admin_view_total_camp_report_all.php?date=<?=$date?>" target="_blank">Total Camp Report</a>
      <a target="_blank" href="admin_view_t_shirts_all.php?date=<?=$date?>" target="_blank">Camp T-Shirts</a>
      <a target="_blank" href="admin_view_awards_all.php?date=<?=$date?>" target="_blank">Awards</a>
      <a target="_blank" href="award_email_template.php">Award Email Template</a>
      <a target="_blank" href="admin_view_emails_with_rewards.php?date=<?=$date?>">Emails With Awards</a>
      </div>

    <div class="sa-box">
      <h4>Camp Communications</h4>
        <a href="admin_view_details_csv.php?date=<?=$date?>">Detailed Roster CSV</a>
		<a href="admin_bravome_usernames.php?date=<?=$date?>">BravoMe Usernames</a>
		<a href="admin_view_google_csv.php?date=<?=$date?>">Google Roster CSV</a>
        <a target="_blank" href="admin_view_receive_email.php" target="_blank">Receive Email</a>
      <a target="_blank" href="admin_view_receive_brochure.php" target="_blank">Receive Brochure</a>   
    </div>

	  <div class="sa-box">
      <h4>Staff Issues</h4>
      <a target="_blank" href="/staff_issues/">Submit Staff Issue</a>
	  <a target="_blank" href="admin_teacher_retention.php">Teacher Retention</a>
      <a target="_blank" href="admin_staff_issues.php">View Staff Issue Reports</a>
      <a target="_blank" href="teacher_yb.php">Submit Yearbook Memory</a>
      <a target="_blank" href="admin_yearbook.php">View Yearbook Memories</a>
    </div>

	  <div class="sa-box">
      <h4>WEEKLY TASKS FOR CAMP</h4>
        <a target="_blank" href="welcome_send.php">Welcome Packets</a>         
  <a target="_blank" href="care_profile_updates.php">Care.com Profile Updates</a>
  <a target="_blank" href="wednesday_mailers.php">Wednesday Mailers</a>
  <a target="_blank" href="final_details_mailers.php">Final Details Mailers</a>
  <a target="_blank" href="rotating_sites_schedule.php">Brain Games & Surprise Schedule</a>
  <a target="_blank" href="friday_trivia_admin.php">PB Trivia</a>
      <a target="_blank" href="tlp_permissions.php">PB Jamboree Tech Permissions</a>
	  <a target="_blank" href="bravohack_accounts.php">BravoHack Accounts</a>

    </div>


<?php } ?>
    <?php
if (
  $_SESSION["admin_id"] == 58    // Kaal
  || $_SESSION["admin_id"] == 2  // Danny
) {
?>
	  <div class="sa-box">
      <h4>Admin Management</h4>
      <a target="_blank" href="super_admin_list.php">List Admins</a>
      <a target="_blank" href="super_admin_new.php">New Admin</a>
      <a target="_blank" href="super_admin_locations.php">Assign Admin Locations</a>
      <a target="_blank" href="super_admin_logins.php">View Admin Logins</a>
      <a target="_blank" href="super_admin_stem_tech.php">Edit STEM/TECH Access</a>
    </div>
	<?php } ?>   
</div><!-- /.sa-grid -->
</div><!-- /.sa-wrap -->
<a href="#top">TOP</a>

<?php  
show_footer();
ob_end_flush();
?>
