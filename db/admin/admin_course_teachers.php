<?php ob_start();
session_start();
require(dirname(__FILE__) . '/admin_config.php');
require_once(dirname(__FILE__) . '/inc/MoodleEnrollments.php');
$admin_id = $_SESSION['admin_id'];
if (!$admin_id) {
    header("Location: admin_login.php");
}
$course_location_id = $_GET['clid'];
// echo "<pre>";
// print_r($_REQUEST);
// echo "</pre>";
$connection = connect_database();
if ($_POST['update']=='Update Course Teachers') {
	$query = "delete from `teacher_course_location` where course_location_id = $course_location_id";
// 	echo $query;
	$result = mysqli_query($connection,$query);
	foreach ($_REQUEST as $key => $teacher_id) {
		if (substr($key,0,8) == 'teacher_') {
			$query = "insert into`teacher_course_location` (`teacher_id`,`course_location_id`) values ($teacher_id,$course_location_id)";
// 	echo $query;
			$result = mysqli_query($connection,$query);
		}
	}
	$query = "update award set file_pdf_path = NULL where course_location_id = $course_location_id";
	mysqli_query($connection,$query);
	try {
		$moodle_sync = pb_moodle_reconcile_all_current();
		$_SESSION["message"] = "<p><b>Moodle reconcile:</b> ".$moodle_sync['inserted']." added, ".$moodle_sync['skipped']." already existed, ".$moodle_sync['deleted']." stale removed.</p>";
	} catch (Exception $e) {
		$_SESSION["message"] = "<p><b>Moodle reconcile warning:</b> ".htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')."</p>";
	}
}
$admin = new Admin();
$course = $admin->get_course_by_course_location_id($course_location_id);
$teacher = $admin->get_teacher_by_course_location_id($course_location_id);

$query = "SELECT teacher.teacher_id, first_name, last_name, nickname FROM teacher, teacher_course_location WHERE course_location_id = '$course_location_id' AND teacher.teacher_id = teacher_course_location.teacher_id order by teacher_id asc";
$result = mysqli_query($connection,$query);
while($row = mysqli_fetch_assoc($result)) $course_teachers[] = $row;

$location = $admin->get_location_by_course_location_id($course_location_id);
$location_id = $location['location_id'];
$school_name = $location["school_name"];
$max_enrollment = $course['max_enrollment'];
$currently_enrolled = $admin->get_enrolled($course_location_id);
$course_name = $course['course_name'];
$course_foc = $course['course_foc'];
$course_start_date = $course['course_start_date'];
$course_end_date = $course['course_end_date'];
$order_by = $_GET['order_by'];
show_header_waitlist();
show_admin_menu();
?>
<h3>
<a class="blue" href="admin_home.php">Admin Home</a> :: 
<a class="blue" href="admin_view_courses.php">Course Teachers</a>
<?php
$WaitList = new AdminWaitList();
$waitlist_count = $WaitList->find_count($course_location_id);
print "$course_name</h3><h4> ($course_start_date - $course_end_date) - <a href='https://www.planetbravo.com/db/admin/admin_view_waitlist.php#$course_location_id'>WAITLIST</a>";
if ($waitlist_count) echo " ($waitlist_count)";
?><h4>
<?php
echo $school_name." <a style='cursor:pointer' target='_new' onclick='show_school_events(\"$location_id\",\"$course_start_date\",\"$school_name\")' title='$school_name'><font color=red>$course_start_date</font></a> <a style='cursor:pointer' target='_new' onclick='show_admin_edit_course_details(\"$course_location_id\",\"$school_name\")' title='$school_name'>($currently_enrolled/$max_enrollment)</a>";
// echo $school_name." (".$currently_enrolled."/".$max_enrollment.")";
?>
<br/><br/>
<?php
if (!empty($course_teachers))
if (count($course_teachers)>1) {
	foreach($course_teachers as $value) {
?>
<h4>Teacher: <?php print '<a target="_blank" href="/db/admin/admin_view_teacher_details.php?teacher_id='.$value['teacher_id'].'">'.$value['first_name'].' '.$value['last_name'].'</a> ('.$value['nickname'].')'; ?></h4><p>
<?php }
} else { ?>
<h4>Teacher: <?php print '<a target="_blank" href="/db/admin/admin_view_teacher_details.php?teacher_id='.$teacher['teacher_id'].'">'.$teacher['first_name'].' '.$teacher['last_name'].'</a> ('.$teacher['nickname'].')'; ?></h4><p>
<?php } ?>
<?php
if ($_SESSION["message"]) {
  echo $_SESSION["message"];
  unset($_SESSION["message"]);
}
$query = "SELECT email, nickname, first_name, last_name, teacher_id FROM `teacher` where email <> ''";
$result = mysqli_query($connection,$query);
while ($row = mysqli_fetch_assoc($result)) $teachers[] = $row;
$query = "SELECT teacher.teacher_id, first_name, last_name, nickname FROM teacher, teacher_course_location WHERE course_location_id = '$course_location_id' AND teacher.teacher_id = teacher_course_location.teacher_id";
$result = mysqli_query($connection,$query);
while ($row = mysqli_fetch_assoc($result)) {
	$course_teachers[] = $row;
	$teacher_ids[] = $row["teacher_id"];
}

// echo "<pre>";
// print_r($teacher_ids);
// echo "</pre>";

?>
<form method='post' action='admin_course_teachers.php?clid=<?php echo $course_location_id; ?>'>
<table class="course">
<?php
foreach($teachers as $value) {
	$checked = '';
	foreach($teacher_ids as $teacher_id) if ($teacher_id == $value['teacher_id']) $checked = ' checked';
?>
<tr>
    <td><?php echo $value['teacher_id']; ?></td>
    <td><?php echo $value['first_name'].' '.$value['last_name'].' ('.$value['nickname'].')'; ?></td>
    <td><?php echo $value['email']; ?></td>
    <td><center><input type='checkbox' name='teacher_<?php echo $value['teacher_id']; ?>' value='<?php echo $value['teacher_id']; ?>'<?php echo $checked; ?>></center></td>
</tr>
<?php 
} 
?>
<tr>
    <td colspan='4'>&nbsp;</td>
</tr>
<tr>
    <td colspan='3'>&nbsp;</td>
    <td><input type='submit' name='update' value='Update Course Teachers'></td>
</tr>
</table>
</form>
<br/><br/>
<center>
[ <a href="/db/admin/admin_view_course_students.php?course_location_id=<?php echo $course_location_id; ?>">Course Students</a> ]
</center>
<?php
show_footer();
ob_end_flush();
?>
